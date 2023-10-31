<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SuperAdmin\CustomController;
use App\Mail\SendMail;
use App\Models\Appointment;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Doctor;
use App\Models\DoctorSubscription;
use App\Models\Faviroute;
use App\Models\Hospital;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Treatments;
use App\Models\WorkingHour;
use Carbon\Carbon;
use App\Models\HospitalGallery;
use App\Models\Lab;
use App\Models\LabSettle;
use App\Models\LabWorkHours;
use App\Models\Language;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\MedicineChild;
use App\Models\NotificationTemplate;
use App\Models\Offer;
use App\Models\Pathology;
use App\Models\PathologyCategory;
use App\Models\Pharmacy;
use App\Models\PharmacySettle;
use App\Models\PharmacyWorkingHour;
use App\Models\Prescription;
use App\Models\PurchaseMedicine;
use App\Models\Radiology;
use App\Models\RadiologyCategory;
use App\Models\Report;
use App\Models\User;
use App\Models\UserAddress;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;

class WebsiteController extends Controller
{
    public function index()
    {
        if (env('DB_DATABASE') == "") {
            return view("first_page");
        }
        $banners = Banner::get();
        $doctors = Doctor::with('category:id,name')->where([['status', 1], ['is_filled', 1], ['subscription_status', 1]])->get()->take(8);

        foreach ($doctors as $doctor) {
            $doctor['is_fav'] = $this->checkFavourite($doctor['id']);
            
        }

        $treatments = Treatments::whereStatus(1)->paginate(6);
        $setting = Setting::first();
        $currency = $setting->currency_symbol;
        $reviews = Review::get();
        $blogs = Blog::get();
        return view('website.home', compact('banners', 'doctors', 'treatments', 'setting', 'reviews', 'blogs', 'currency'));
    }



    
    public function sign_up(Request $request)
    {
        $request->validate(
            [
                'name' => 'bail|required',
                'email' => 'bail|required|email|unique:users',
                'dob' => 'bail|required|date_format:Y-m-d',
                'gender' => 'bail|required',
                'phone' => 'bail|required|numeric',
                'password' => 'bail|required|min:6'
            ],
            [
                'dob.before' => 'Date is invalid.',
            ]
        );
        $data = $request->all();
        $verification = Setting::first()->verification;
        $verify = $verification == 1 ? 0 : 1;
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($request->password),
            'verify' => $verify,
            'phone' => $data['phone'],
            'phone_code' => $data['phone_code'],
            'image' => 'defaultUser.png',
            'status' => 1,
            'dob' => $data['dob'],
            'gender' => $data['gender']
        ]);
        Auth::loginUsingId($user->id);

        if ($user->verify) {
            return redirect('/');
        } else {
            Session::put('verified_user', $user);
            return redirect('send_otp');
        }
    }

    // Doctor Register
    public function doctorRegister(Request $request)
    {
        $request->validate([
            'doc_name' => 'bail|required|unique:doctor,name',
            'doc_email' => 'bail|required|email|unique:users,email',
            'doc_dob' => 'bail|required',
            'doc_gender' => 'bail|required',
            'doc_phone' => 'bail|required|numeric',
            'doc_password' => 'bail|required|min:6'
        ]);
        $data['name'] = $request->doc_name;
        $data['email'] = $request->doc_email;
        $data['phone'] = $request->doc_phone;
        $data['phone_code'] = $request->phone_code;
        $data['dob'] = $request->doc_dob;
        $data['gender'] = $request->doc_gender;
        $data['password'] = $request->doc_password;
        $user = (new CustomController)->doctorRegister($data);
        Auth::loginUsingId($user->id);
        if ($user->verify) {
            return redirect('/doctor_home');
        } else {
            Session::put('verified_user', $user);
            return redirect('doctor/send_otp/' . $user->id);
        }
    }

    public function sendOtp()
    {
        $user = Session::get('verified_user');
        $user = (new CustomController)->sendOtp($user);
        Session::put('verified_user', $user);
        $status = '';
        $setting = Setting::first();
        if ($setting->using_msg == 1 && $setting->using_mail == 1)
            $status = 'verification code sent in email and phone';

        if ($status == '') {
            if ($setting->using_msg == 1 || $setting->using_mail == 1) {
                if ($setting->using_msg == 1)
                    $status = 'verification code sent into phone';
                if ($setting->using_mail == 1)
                    $status = 'verification code sent into email';
            }
        }
        return view('website.send_otp', compact('user', 'status'));
    }

    public function verify_user(Request $request)
    {
        $data = $request->all();
        $otp = $data['digit_1'] . $data['digit_2'] . $data['digit_3'] . $data['digit_4'];
        $user = Session::get('verified_user');
        if ($user) {
            if ($user->otp == $otp) {
                $user->verify = 1;
                $user->save();
                if (Auth::loginUsingId($user->id)) {
                    session()->forget('verified_user');
                    if (auth()->user()->hasRole('doctor'))
                        return redirect('doctor_home');
                    return redirect('/');
                }
            } else
                return redirect()->back()->withErrors(__('otp does not match'));
        } else
            return redirect()->back()->withErrors(__('Oops...user not found..!!'));
    }

    public function doctor(Request $request)
    {
        $setting = Setting::first();
        $currency = $setting->currency_symbol;
        $categories = Category::whereStatus(1)->get();
        $treatments = Treatments::whereStatus(1)->get();
        $doctor = Doctor::with(['treatment', 'category', 'expertise'])->whereStatus(1)->where('is_filled', 1)->whereSubscriptionStatus(1);
        $data = $request->all();
        $getSet = 0;
        if (isset($data['doc_lat']) && isset($data['doc_lang']) && $data['doc_lang'] != '' && $data['doc_lat'] != '') {
            $radius = $setting->radius;
            $hospital = Hospital::whereStatus(1)->GetByDistance($data['doc_lat'], $data['doc_lang'], $radius)->get(['id']);
            $doctor->whereIn('hospital_id', $hospital);
            $doctors = $doctor->get()->values()->all();
            foreach ($doctors as $doctor) {
                $doctor['is_fav'] = $this->checkFavourite($doctor['id']);
                $doctor->hospital = (new CustomController)->getHospital($doctor['id']);
            }
        } elseif (isset($data['search_doctor']) && $data['search_doctor'] != '') {
            $doctor->where('name', 'LIKE', '%' . $data['search_doctor'] . "%");
        } elseif (isset($data['gender_type']) && $data['gender_type'] != '') {
            $doctor->where('gender', $data['gender_type']);
        } elseif (isset($data['category'])) {
            $doctor->whereIn('category_id', $data['category']);
        }
        if (isset($data['treatment_id'])) {
            $doctor->where('treatment_id', $data['treatment_id']);
        } elseif (isset($data['sort_by']) && $data['sort_by'] != '') {
            $reqData = $request->all();
            if ($reqData['sort_by'] == 'rating') {
                $doctor = $doctor->get();
                $getSet = 1;
                $doctors = $doctor->sortByDesc('rate');
            } elseif ($reqData['sort_by'] == 'latest') {
                $doctor = $doctor->get();
                $getSet = 1;
                $doctors = $doctor->sortByDesc('id');
            } elseif ($reqData['sort_by'] == 'popular') {
                $doctor = $doctor->where('is_popular', 1);
            }
        }
        if (isset($data['from'])) {
            if (!$getSet) {
                $doctors = $doctor->get()->values()->all();
            }

            foreach ($doctors as $doctor) {
                $doctor['is_fav'] = $this->checkFavourite($doctor['id']);
                $doctor->hospital = (new CustomController)->getHospital($doctor['id']);
            }
            $view = view('website.display_doctors', compact('doctors', 'currency', 'categories'))->render();
            return response()->json(['html' => $view, 'count' => count($doctors), 'meta' => $doctors, 'success' => true]);
        }
        $doctors = $doctor->paginate(2);
        $doctores = $doctors;
        $doctors = $doctors->toArray();
        foreach ($doctors['data'] as &$doctor) {
            $doctor['is_fav'] = $this->checkFavourite($doctor['id']);
            $doctor['hospital'] = (new CustomController)->getHospital($doctor['id']);
        }
        if ($request->ajax()) {
            $view = view('website.display_doctors', compact('doctors', 'currency', 'categories'))->render();
            return response()->json(['html' => $view, 'count' => count($doctors), 'meta' => $doctors, 'success' => true]);
        }
       // return view('website.especialistas', compact('doctors', 'currency', 'categories', 'treatments'));
        return view('website.find_doctor', compact('doctors', 'currency', 'categories', 'treatments','doctores'));
    }

    public function addBookmark($doctor_id)
    {
        if (auth()->check()) {
            $user_id = auth()->user()->id;
            $faviroute = Faviroute::where([['user_id', $user_id], ['doctor_id', $doctor_id]])->first();
            if (!$faviroute) {
                $data = [];
                $data['user_id'] = $user_id;
                $data['doctor_id'] = $doctor_id;
                Faviroute::create($data);
                return response(['success' => true, 'msg' => __('Agregado a favoritos..!!')]);
            } else {
                $faviroute->delete();
                return response(['success' => true, 'msg' => __('Removido de favoritos..!!')]);
            }
        } else {
            return response(['success' => false, 'msg' => __('Login Required.')]);
        }
    }

    public function distance($lat1, $lang1, $lat2, $lang2)
    {
        $lat1 = $lat1;
        $lon1 = $lang1;
        $lat2 = $lat2;
        $lon2 = $lang2;
        $unit = 'K';
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            $distance = 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);
            if ($unit == "K") {
                $distance = $miles * 1.609344;
            } else if ($unit == "N") {
                $distance = $miles * 0.8684;
            } else {
                $distance = $miles;
            }
        }
        return $distance;
    }

    public function doctor_profile(Request $request, $id, $name)
    {
        $setting = Setting::first();
        $doctor = Doctor::with(['category', 'expertise'])->find($id);
        $doctor->user = User::where('id', $doctor->user_id)->first();
        $doctor['is_fav'] = $this->checkFavourite($doctor['id']);
        $doctor->hospital = (new CustomController)->getHospital($id);
        $doctor->treatment = (new CustomController)->getTreatment($id);
        $doctor->expertise = (new CustomController)->getExpertise($id);
        $rating_one_pr = $this->calculateRate(1, $id);
        $rating_two_pr = $this->calculateRate(2, $id);
        $rating_three_pr = $this->calculateRate(3, $id);
        $rating_four_pr = $this->calculateRate(4, $id);
        $rating_five_pr = $this->calculateRate(5, $id);
        foreach ($doctor->hospital as $hospital) {
            $hospital->hospital_gallery = HospitalGallery::where('hospital_id', $hospital->id)->get();
        }
        $doctor->workHour = WorkingHour::where('doctor_id', $id)->get();
        $currency = Setting::first()->currency_symbol;
        $reviews = Review::where('doctor_id', $id)->with('user')->get();
        $today_timeslots = (new CustomController)->timeSlot($id, Carbon::today(env('timezone'))->format('Y-m-d'));
        $tomorrow_timeslots = (new CustomController)->timeSlot($id, Carbon::tomorrow()->format('Y-m-d'));
        $today_date[] = Carbon::now(env('timezone'))->format('M d, Y');
        $today_date[] =  WorkingHour::where([['doctor_id', $id], ['day_index', Carbon::now(env('timezone'))->format('l')]])->first()->period_list;
        $today = WorkingHour::where([['doctor_id', $id], ['day_index', Carbon::now(env('timezone'))->format('l')]])->first()->status;
        $currently_open = 0;
        foreach (json_decode($today_date[1]) as $tDate) {
            if (Carbon::now(env('timezone'))->between(Carbon::parse($tDate->start_time), Carbon::parse($tDate->end_time))) {
                $currently_open = 1;
            }
        }
        if ($request->ajax()) {
            return response(['success' => true, 'data' => $doctor]);
        }
        return view('website.doctor_detail', compact('today', 'doctor', 'currency', 'reviews', 'today_timeslots', 'today_date', 'tomorrow_timeslots', 'rating_one_pr', 'rating_two_pr', 'rating_three_pr', 'rating_four_pr', 'rating_five_pr'));
    }

    public function test_report(Request $request)
    {
        $request->validate([
            'patient_name' => 'bail|required',
            'age' => 'bail|required|numeric',
            'phone_no' => 'bail|required|numeric|digits_between:6,12',
            'gender' => 'bail|required',
            'pathology_category_id' => 'required_without:radiology_category_id',
            'radiology_category_id' => 'required_without:pathology_category_id',
            'pathology_id' => "required_with:pathology_category_id",
            'radiology_id' => "required_with:radiology_category_id",
            'date' => 'bail|required',
            'time' => 'bail|required',
            'doctor_id' => 'bail|required_if:prescription_required,1',
            'prescription' => 'bail|required_if:prescription_required,1'
        ]);
        $data = $request->all();
        $lab = Lab::find($request->lab_id);
        $data['report_id'] =  '#' . rand(100000, 999999);
        if (isset($data['prescription'])) {
            $test = explode('.', $data['prescription']);
            $ext = end($test);
            $name = uniqid() . '.' . $data['prescription']->getClientOriginalExtension();;
            $location = public_path() . '/report_prescription/upload';
            $data['prescription']->move($location, $name);
            $data['prescription'] = $name;
        }
        $data['user_id'] = auth()->user()->id;
        if (isset($data['pathology_id'])) {
            $data['pathology_id'] = implode(',', $data['pathology_id']);
        }
        if (isset($data['radiology_id'])) {
            $data['radiology_id'] = implode(',', $data['radiology_id']);
        }
        $data = array_filter($data, function ($a) {
            return $a !== "";
        });
        $report = Report::create($data);
        $settle = array();

        $com = $lab->commission * $request->amount;
        $admin_commission = $com / 100;
        $lab_commission = $request->amount - $admin_commission;

        $settle['lab_id'] = $lab->id;
        $settle['report_id'] = $report->id;
        $settle['admin_amount'] = $admin_commission;
        $settle['lab_amount'] = $lab_commission;
        $settle['payment'] = $report->payment_status == 1 ? 1 : 0;
        $settle['lab_status'] = 0;
        LabSettle::create($settle);
        return response(['success' => true]);
    }

    public function booking($id, $name)
    {
        $doctor = Doctor::with(['category', 'expertise'])->find($id);

        $doctor->user = User::where('id', $doctor->user_id)->first();

        $doctor['is_fav'] = $this->checkFavourite($doctor['id']);
        $doctor->hospital = (new CustomController)->getHospital($id);
        $doctor->treatment = (new CustomController)->getTreatment($id);
        $doctor->expertise = (new CustomController)->getExpertise($id);

        $reviews = Review::where('doctor_id', $id)->with('user')->get();

        $patient_addressess = UserAddress::where('user_id', auth()->user()->id)->get();
        $today_timeslots = (new CustomController)->timeSlot($id, Carbon::today(env('timezone'))->format('Y-m-d'));
        $doctor->hospital = (new CustomController)->getHospital($id);
        $setting = Setting::first();
        $currency = $setting->currency_symbol;
        return view('website.appointment_booking', compact('doctor','reviews' ,'patient_addressess', 'today_timeslots', 'currency', 'setting'));
    }

    public function modal($id)
    {
        $doctor = Doctor::with(['category', 'expertise'])->find($id);

        $doctor->user = User::where('id', $doctor->user_id)->first();

        $doctor['is_fav'] = $this->checkFavourite($doctor['id']);
        $doctor->hospital = (new CustomController)->getHospital($id);
        $doctor->treatment = (new CustomController)->getTreatment($id);
        $doctor->expertise = (new CustomController)->getExpertise($id);

        $reviews = Review::where('doctor_id', $id)->with('user')->get();

        $patient_addressess = UserAddress::where('user_id', auth()->user()->id)->get();
        $today_timeslots = (new CustomController)->timeSlot($id, Carbon::today(env('timezone'))->format('Y-m-d'));
        $doctor->hospital = (new CustomController)->getHospital($id);
        $setting = Setting::first();
        $currency = $setting->currency_symbol;
        //$r = ['success' => true, 'data' => ['doctor'=>$doctor,'reviews'=>$reviews,'today_timeslots'=>$today_timeslots]];
        return response()->json(['doctor'=>$doctor,'reviews'=>$reviews,'today_timeslots'=>$today_timeslots]);
        //return response([]);view('website.appointment_booking', compact('doctor','reviews' ,'patient_addressess', 'today_timeslots', 'currency', 'setting'));
    }

    public function pharmacy(Request $request)
    {
        $pharmacy = Pharmacy::whereStatus(1);
        $data = $request->all();
        $data = array_filter($data, function ($a) {
            return $a !== "";
        });
        if (isset($data['address']) && isset($data['pharmacy_lat']) && isset($data['pharmacy_lang'])) {
            $data = $request->all();
            $radius = Setting::first()->radius;
            $pharmacies = $pharmacy->GetByDistance($data['pharmacy_lat'], $data['pharmacy_lang'], $radius);
        } elseif (isset($data['search_pharmacy'])) {
            $pharmacies = $pharmacy->where('name', 'LIKE', '%' . $request->search_pharmacy . "%")->get();
        } elseif ($request->has('category')) {
            $data = $request->all();
            if (in_array('latest', $data['category'])) {
                $pharmacies = $pharmacy->orderBy('id', 'DESC');
            }
            if (in_array('opening', $data['category'])) {
                $Ids = array();
                $current_time = Carbon::now(env('timezone'));
                $current_day = Carbon::now(env('timezone'))->format('l');
                $tempPharmacy = Pharmacy::whereStatus(1)->get();
                foreach ($tempPharmacy as $value) {
                    $pharmacyHours = PharmacyWorkingHour::where([['pharmacy_id', $value->id], ['day_index', $current_day], ['status', 1]])->first();
                    if ($pharmacyHours) {
                        $hours = json_decode($pharmacyHours->period_list);
                        foreach ($hours as $hour) {
                            $temp = $current_time->between($hour->start_time, $hour->end_time);
                            if ($temp)
                                array_push($Ids, $value->id);
                        }
                    }
                }
                $pharmacies = $pharmacy->whereIn('id', $Ids);
            }
        }
        if (isset($data['from'])) {
            $pharmacies = $pharmacy->get()->values()->all();
            foreach ($pharmacies as $pharmacy) {
                $dayname = Carbon::now(env('timezone'))->format('l');
                $workingHours = PharmacyWorkingHour::where([['pharmacy_id', $pharmacy->id], ['day_index', $dayname]])->first()->period_list;
                $pharmacy['openTime'] = json_decode($workingHours)[0]->start_time;
            }
            $view = view('website.display_pharmacy', compact('pharmacies'))->render();
            return response()->json(['html' => $view, 'success' => true]);
        }
        $pharmacies = $pharmacy->paginate(10);
        $pharmacies = $pharmacies->toArray();
        foreach ($pharmacies['data'] as &$pharmacy) {
            $dayname = Carbon::now(env('timezone'))->format('l');
            $workingHours = PharmacyWorkingHour::where([['pharmacy_id', $pharmacy['id']], ['day_index', $dayname]])->first()->period_list;
            $pharmacy['openTime'] = json_decode($workingHours)[0]->start_time;
        }

        if ($request->ajax()) {
            $view = view('website.display_pharmacy', compact('pharmacies'))->render();
            return response()->json(['html' => $view, 'meta' => $pharmacies, 'success' => true]);
        }
        return view('website.pharmacy', compact('pharmacies'));
    }

    public function downloadPDF($id)
    {
        $id = Prescription::find($id);
        $pathToFile = public_path() . "/prescription/upload/" . $id->pdf;
        $name = $id->pdf;
        $headers = array('Content-Type: application/pdf',);
        return response()->download($pathToFile, $name, $headers);
    }

    public function pharmacyDetails($id, $name)
    {
        $dayname = Carbon::now(env('timezone'))->format('l');
        $pharmacy = Pharmacy::find($id);
        $workingHours = PharmacyWorkingHour::where([['pharmacy_id', $pharmacy['id']], ['day_index', $dayname]])->first()->period_list;
        $pharmacy['openTime'] = json_decode($workingHours)[0]->start_time;
        $today_date[] = Carbon::now(env('timezone'))->format('M d, Y');
        $today_date[] =  PharmacyWorkingHour::where([['pharmacy_id', $id], ['day_index', Carbon::now(env('timezone'))->format('l')]])->first()->period_list;
        $currently_open = 0;
        foreach (json_decode($today_date[1]) as $tDate) {
            if (Carbon::now(env('timezone'))->between(Carbon::parse($tDate->start_time), Carbon::parse($tDate->end_time))) {
                $currently_open = 1;
            }
        }
        $pharmacyWorkingHours = PharmacyWorkingHour::where('pharmacy_id', $pharmacy['id'])->get();
        return view('website.pharmacy_detail', compact('pharmacy', 'today_date', 'workingHours', 'pharmacyWorkingHours'));
    }

    public function pharmacyProduct(Request $request, $id, $name)
    {
        $pharmacy = Pharmacy::find($id);
        $categories = MedicineCategory::whereStatus(1)->orderBy('id', 'DESC')->get();
        $currency = Setting::first()->currency_symbol;
        $medicine = Medicine::where('pharmacy_id', $id);
        if ($request->has('from')) {
            if ($request->has('category')) {
                $medicines = $medicine->whereIn('medicine_category_id', $request->category)->get();
                $view = view('website.display_medicine', compact('medicines', 'currency'))->render();
                return response()->json(['html' => $view, 'success' => true]);
            }

            if ($request->has('medicine_name')) {
                $medicines = $medicine->where('name', 'like', '%' . $request->medicine_name . '%')->get();
                $view = view('website.display_medicine', compact('medicines', 'currency'))->render();
                return response()->json(['html' => $view, 'success' => true]);
            }
        }

        $medicines = $medicine->paginate(5);
        $medicines = $medicines->toArray();

        if ($request->ajax()) {
            $view = view('website.display_medicine', compact('medicines', 'currency'))->render();
            return response()->json(['html' => $view, 'meta' => $medicines, 'success' => true]);
        }

        return view('website.pharmacy_product', compact('pharmacy', 'medicines', 'currency', 'categories'));
    }

    public function getDeliveryCharge(Request $request)
    {
        $data = $request->all();
        $address = UserAddress::find($data['address_id']);
        $distance = $this->getDistance($address);
        $delivery_charge = 0;
        if ($distance != 0) {
            $charges = Session::get('pharmacy')['delivery_charges'];
            foreach (json_decode($charges) as $charge) {
                if ($distance >= intval($charge->min_value) && $distance < intval($charge->max_value)) {
                    $delivery_charge = $charge->charges;
                }
            }
            if ($delivery_charge == 0) {
                $delivery_charge = max(array_column(json_decode($charges), 'charges'));
            }
        }
        dd($delivery_charge);
        return response(['success' => true, 'data' => ['delivery_charge' => intval($delivery_charge), 'currency' => Setting::first()->currency_symbol]]);
    }

    public function getDistance($address)
    {
        $lat1 = $address->lat;
        $lon1 = $address->lang;
        $lat2 = Session::get('pharmacy')['lat'];
        $lon2 = Session::get('pharmacy')['lang'];
        $unit = 'K';
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            $distance = 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);
            if ($unit == "K") {
                $distance = $miles * 1.609344;
            } else if ($unit == "N") {
                $distance = $miles * 0.8684;
            } else {
                $distance = $miles;
            }
        }
        return intval($distance);
    }

    public function signup()
    {
        return view('website.signup');
    }

    public function labs(Request $request)
    {
        $data = $request->all();
        $data = array_filter($data, function ($a) {
            return $a !== "";
        });
        $dayname = Carbon::now(env('timezone'))->format('l');
        $lab = Lab::with('user')->whereStatus(1)->orderBy('id', 'DESC');
        $getSet = 0;
        if (isset($data['lab_lat']) && isset($data['lab_lang'])) {
            $radius = Setting::first()->radius;
            $lab = $lab->GetByDistance($data['lab_lat'], $data['lab_lang'], $radius);
        } elseif (isset($data['search_val'])) {
            $lab = $lab->where('name', 'LIKE', '%' . $data['search_val'] . "%");
        } elseif (isset($data['latest'])) {
            $getSet = 1;
            $lab = $lab->get()->values();
            $labs = $lab->sortByDesc('id');
        }

        if ($request->has('from') && $request->from == 'js') {
            if (!$getSet) {
                $labs = $lab->get()->values()->all();
            }
            foreach ($labs as $lab) {
                $workingHours = LabWorkHours::where([['lab_id', $lab->id], ['day_index', $dayname]])->first()->period_list;
                $lab['openTime'] = json_decode($workingHours)[0]->start_time;
            }
            $view = view('website.display_labs', compact('labs'))->render();
            return response()->json(['html' => $view, 'success' => true]);
        }
        $labs = $lab->paginate(5);
        $labs = $labs->toArray();
        foreach ($labs['data'] as &$lab) {
            $workingHours = LabWorkHours::where([['lab_id', $lab['id']], ['day_index', $dayname]])->first()->period_list;
            $lab['openTime'] = json_decode($workingHours)[0]->start_time;
        }
        if ($request->ajax()) {
            $view = view('website.display_labs', compact('labs'))->render();
            return response()->json(['html' => $view, 'meta' => $labs, 'success' => true]);
        }
        return view('website.laboratory', compact('labs'));
    }

    public function labTest($id, $name)
    {
        $lab = Lab::find($id);
        $pathologyCategories = PathologyCategory::where('status', 1)->get();
        $doctors = Doctor::whereStatus(1)->get();
        $date = Carbon::now(env('timezone'))->format('Y-m-d');
        $timeslots = (new CustomController)->LabtimeSlot($lab->id, $date);
        $radiology_categories = RadiologyCategory::whereStatus(1)->get();
        $setting = Setting::first();
        return view('website.report_test', compact('lab', 'pathologyCategories', 'setting', 'doctors', 'timeslots', 'radiology_categories',));
    }

    public function radiology_category_wise($id, $lab_id)
    {
        $pathology = Radiology::where([['radiology_category_id', $id], ['lab_id', $lab_id], ['status', 1]])->get();
        return response(['success' => true, 'data' => $pathology]);
    }

    public function single_radiology_details(Request $request)
    {
        $radiology = Radiology::where([['lab_id', $request->lab_id], ['status', 1]])->whereIn('id', $request->id)->get();
        $currency = Setting::first()->currency_symbol;
        $total = $radiology->sum('charge');
        return response(['success' => true, 'data' => $radiology, 'total' => $total, 'currency' => $currency]);
    }


    public function lab_timeslot(Request $request)
    {
        $timeslots = (new CustomController)->LabtimeSlot($request->lab_id, $request->date);
        return response(['success' => true, 'data'  => $timeslots, 'date' => Carbon::parse($request->date)->format('d M')]);
    }

    public function pathology_category_wise($id, $lab_id)
    {
        $pathology = Pathology::where([['pathology_category_id', $id], ['lab_id', $lab_id], ['status', 1]])->get();
        return response(['success' => true, 'data' => $pathology]);
    }

    public function single_pathology_details(Request $request)
    {
        $pathology = Pathology::where([['lab_id', $request->lab_id], ['status', 1]])->whereIn('id', $request->id)->get();
        $currency = Setting::first()->currency_symbol;
        $total = $pathology->sum('charge');
        return response(['success' => true, 'total' => $total, 'data' => $pathology, 'currency' => $currency]);
    }

    public function patientLogin(Request $request)
    {
        if ($request->has('email') && $request->has('password')) {
            if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
                $user = Auth::user();

                if ($user->status) {
                    if ($user->verify) {
                        if (auth()->user()->hasRole('doctor'))
                            return redirect('doctor_home');
                        else
                            return redirect('/');
                    } else {
                        Session::put('verified_user', $user);
                        return redirect('send_otp');
                    }
                } else {
                    Auth::logout();
                    return redirect()->back()->with('error', __('You are block by admin please contact admin'));
                }
            } else
                return redirect()->back()->with('error', __('Invalid Email Or Password'));
        }
        return view('website.login');
    }

    // BookAppointment
    public function bookAppointment(Request $request)
    {
        $data = $request->all();
        // return $data;
        $request->validate([
            'appointment_for' => 'bail',
            'illness_information' => 'bail',
            'patient_name' => 'bail|required',
            'age' => 'bail|numeric',
            //'patient_address' => 'bail',
            'phone_no' => 'bail|required|numeric',
            'drug_effect' => 'bail',
            'note' => 'bail',
            'date' => 'bail|required',
            'time' => 'bail|required',
            //'hospital_id' => 'bail',
            'time' => 'bail|required|date_format:h:i a'
        ]);
        $data['appointment_id'] =  '#' . rand(100000, 999999);
        $data['user_id'] = auth()->user()->id;
        $data['appointment_status'] = 'pending';
        $data['is_from'] = '0';
        if ($request->hasFile('report_image')) {
            $report = [];
            for ($i = 0; $i < count($data['report_image']); $i++) {
                array_push($report, (new CustomController)->imageUpload($request->report_image[$i]));
            }
            $data['report_image'] = json_encode($report);
        }
        $doctor = Doctor::find($data['doctor_id']);
        $data['amount'] = $doctor->appointment_fees;
        if ($doctor->based_on == 'commission') {
            $comm = $doctor->appointment_fees * $doctor->commission_amount;
            $data['admin_commission'] = intval($comm / 100);
            $data['doctor_commission'] = intval($doctor->appointment_fees - $data['admin_commission']);
        } else {
            DoctorSubscription::where('doctor_id', $doctor->id)->latest()->first()->increment('booked_appointment');
        }
        $data['payment_type'] = strtoupper($data['payment_type']);
        $data = array_filter($data, function ($a) {
            return $a !== "";
        });
        $appointment = Appointment::create($data);
        // session()->forget('doctor_id');
        // session()->forget('time');
        // session()->forget('date');


        $setting = Setting::first();
        // doctor booked appointment
        $notification_template1 = NotificationTemplate::where('title','doctor book appointment')->first();
        $doc_msg_content = $notification_template1->msg_content;
        $doc_mail_content = $notification_template1->mail_content;
        $detail1['doctor_name'] = $doctor->name;
        $detail1['appointment_id'] = $appointment->appointment_id;
        $detail1['date'] = $appointment->date;
        $detail1['user_name'] = auth()->user()->name;
        $detail1['app_name'] = $setting->business_name;
        $doctor_data = ["{{doctor_name}}","{{appointment_id}}","{{date}}","{{user_name}}","{{app_name}}"];
        $doc_mail = str_replace($doctor_data, $detail1, $doc_mail_content);
        $doc_message = str_replace($doctor_data, $detail1, $doc_msg_content);
        $doctor_user = User::where('id',$doctor->user_id)->first();
        if($setting->doctor_mail == 1){
            try {
                $config = array(
                    'driver'     => $setting->mail_mailer,
                    'host'       => $setting->mail_host,
                    'port'       => $setting->mail_port,
                    'from'       => array('address' => $setting->mail_from_address, 'name' => $setting->mail_from_name),
                    'encryption' => $setting->mail_encryption,
                    'username'   => $setting->mail_username,
                    'password'   => $setting->mail_password
                );
                Config::set('mail', $config);
                Mail::to($doctor_user->email)->send(new SendMail($doc_mail,$notification_template1->subject));
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        
        
        
        // create Appointment to user
        $notification_template = NotificationTemplate::where('title','create appointment')->first();
        $msg_content = $notification_template->msg_content;
        $mail_content = $notification_template->mail_content;
        $detail['user_name'] = auth()->user()->name;
        $detail['appointment_id'] = $appointment->appointment_id;
        $detail['date'] = $appointment->date;
        $detail['time'] = $appointment->time;
        $detail['app_name'] = $setting->business_name;
        $user_data = ["{{user_name}}","{{appointment_id}}","{{date}}","{{time}}","{{app_name}}"];
        $mail1 = str_replace($user_data, $detail, $mail_content);
        $message1 = str_replace($user_data, $detail, $msg_content);
        if($setting->patient_mail == 1){
            try {
                $config = array(
                    'driver'     => $setting->mail_mailer,
                    'host'       => $setting->mail_host,
                    'port'       => $setting->mail_port,
                    'from'       => array('address' => $setting->mail_from_address, 'name' => $setting->mail_from_name),
                    'encryption' => $setting->mail_encryption,
                    'username'   => $setting->mail_username,
                    'password'   => $setting->mail_password
                );
                Config::set('mail', $config);
                Mail::to(auth()->user()->email)->send(new SendMail($mail1,$notification_template->subject));
            } catch (\Throwable $th) {
            }
        }


        return response(['success' => true]);
    }

    // Check Coupen
    public function checkCoupen(Request $request)
    {
        $data = $request->all();
        $coupen = Offer::where('offer_code', $request->offer_code)->whereColumn('max_use', '>', 'use_count')->first();
        if ($coupen) {
            $users = explode(',', $coupen->user_id);
            if (($key = array_search(auth()->user()->id, $users)) !== false) {
                $exploded_date = explode(' - ', $coupen->start_end_date);
                $currentDate = date('Y-m-d', strtotime($data['date']));
                if (($currentDate >= $exploded_date[0]) && ($currentDate <= $exploded_date[1])) {
                    $discount = array();
                    $discount['discount_id'] = $coupen->id;
                    if ($coupen->is_flat == 1) {
                        $discount['price'] = $coupen->flatDiscount;
                    } else {
                        if ($coupen->discount_type == 'amount')
                            $discount['price'] = $coupen->discount;
                        if ($coupen->discount_type == 'percentage') {
                            $temp = intval($data['amount']) * intval($coupen->discount);
                            $discount['price'] = $temp / 100;
                        }
                    }
                    if (intval($discount['price']) > $coupen->min_discount)
                        $discount['price'] = $coupen->min_discount;
                    $discount['finalAmount'] = intval($data['amount']) - intval($discount['price']);
                    return response(['success' => true, 'data' => $discount, 'currency' => Setting::first()->currency_symbol]);
                } else {
                    return response(['success' => false, 'data' => __('Coupen is Expired.')]);
                }
            } else {
                return response(['success' => false, 'data' => __('Coupen is not valid for this user..!!')]);
            }
        } else {
            return response(['success' => false, 'data' => __('Coupen code is invalid...!!')]);
        }
    }

    public function displayHospital(Request $request)
    {
        $request->validate([
            'patient_address_id' => 'bail|required',
            'doctor_id' => 'bail|required',
        ]);
        $hospitals = (new CustomController)->getHospital($request->doctor_id);
        $address = UserAddress::find($request->patient_address_id);
        foreach ($hospitals as $hospital) {
            $hospital->distance = number_format($this->distance($address->lat, $address->lang, $hospital->lat, $hospital->lng), 2);
        }
        return response(['success' => true, 'data' => $hospitals]);
    }

    public function displayTimeslot(Request $request)
    {
        $timeslots = (new CustomController)->timeSlot($request->doctor_id, $request->date);
        return response(['success' => true, 'data' => $timeslots, 'date' => Carbon::parse($request->date)->format('d M')]);
    }

    public function ourBlogs()
    {
        $blogs = Blog::get();
        return view('website.our_blogs', compact('blogs'));
    }

    public function kullayEmpresas()
    {
        //$blogs = Blog::get();
        return view('website.kullay_empresas');
    }

    public function seParte()
    {
        //$blogs = Blog::get();
        return view('website.se_parte');
    }

    public function ourOffers()
    {
        $offers = Offer::whereStatus(1)->get();
        $currency = Setting::first()->currency_symbol;
        return view('website.our_offers', compact('offers', 'currency'));
    }

    public function singleBlog($id, $name)
    {
        $blog = Blog::find($id);
        return view('website.our_blog_single', compact('blog'));
    }

    public function checkFavourite($doctor_id)
    {
        if (auth()->user() != null) {
            if (Faviroute::where([['user_id', auth()->user()->id], ['doctor_id', $doctor_id]])->first())
                return true;
            return false;
        }
        return false;
    }

    public function calculateRate($count, $doctor_id)
    {
        $review = Review::where([['doctor_id', $doctor_id], ['rate', $count]])->get();
        if (count($review) > 0) {
            // return (count($review) * 5) / 100;
            $totalRate = 0;
            foreach ($review as $r) {
                $totalRate = $totalRate + $r->rate;
            }
            return round($totalRate / count($review), 1);
        } else {
            return 0;
        }
    }

    public function addCart(Request $request)
    {
        // Session::forget('cart');
        $data = $request->all();
        $medicine = Medicine::find($data['id']);
        $cartString = '';
        if (Session::get('cart') == null) {
            if ($medicine->total_stock > $medicine->use_stock) {
                if ($data['operation'] == "plus") {
                    $master = array();
                    $master['id'] = $request->id;
                    $master['price'] = intval($medicine->price_pr_strip);
                    $master['original_price'] = intval($medicine->price_pr_strip);
                    $master['qty'] = 1;
                    $master['image'] = $medicine->full_image;
                    $master['name'] = $medicine->name;
                    $master['prescription_required'] = $medicine->prescription_required;
                    $master['available_stock'] = $medicine->total_stock - $medicine->use_stock;
                    $master['use_stock'] = intval($medicine->use_stock) + 1;
                    Session::push('cart', $master);
                    Session::put('pharmacy', Pharmacy::find($data['pharmacy_id']));
                    $price = intval($medicine->price_pr_strip);
                    $qty = 1;

                    $cartString .= '<div class="flex flex-row h-10 w-full rounded-lg relative bg-transparent mt-1">';
                    $cartString .= '<button id="minus' . $medicine['id'] . '" onclick="addCart(' . $medicine->id . ',' . "`minus`" . ')"  data-te-ripple-init data-te-ripple-color="light" data-action="decrement" class="border-l border-t border-b border-white-light text-black-600 hover:text-black-700 h-8 w-6 cursor-pointer">';
                    $cartString .= '<span class="m-auto text-2xl font-thin">−</span>';
                    $cartString .= '</button>';
                    $cartString .= '<input id="txtCart' . $request->id . '" type="number" readonly class="border-t border-b border-white-light outline-none focus:outline-none text-center w-10 font-semibold text-md hover:text-black focus:text-black md:text-basecursor-default flex items-center text-primary h-8" name="custom-input-number" value="1"></input>';
                    $cartString .= '<button onclick="addCart(' . $medicine->id . ',' . "`plus`" . ')"  data-te-ripple-init data-te-ripple-color="light" data-action="increment" class="border-r border-t border-b border-white-light text-black-600 hover:text-black-700 h-8 w-6 cursor-pointer">';
                    $cartString .= '<span class="m-auto text-2xl font-thin">+</span>';
                    $cartString .= '</button>';
                    $cartString .= '</div>';

                    $total_items = count(Session::get('cart'));
                    $total_price = array_sum(array_column(Session::get('cart'), 'price'));
                    return response(['success' => true, 'data' => ['cartString' => $cartString, 'item_price' =>  $medicine->price_pr_strip, 'total_items' => $total_items, 'qty' => 1, 'total_price' => $total_price]]);
                }
            } else {
                return response(['success' => false, 'data' => 'Out of stock']);
            }
        } else {
            if (Session::get('pharmacy')->id == $data['pharmacy_id']) {
                $session = Session::get('cart');
                if (in_array($request->id, array_column(Session::get('cart'), 'id'))) {
                    foreach ($session as $key => $value) {
                        if ($session[$key]['qty'] < $value['available_stock']) {
                            if ($value['id'] == $data['id']) {
                                if ($data['operation'] == "plus") {
                                    if ($medicine->total_stock > $medicine->use_stock) {
                                        $session[$key]['qty'] += 1;
                                        $session[$key]['price'] = $session[$key]['price'] + $medicine->price_pr_strip;
                                        $session[$key]['use_stock'] = $session[$key]['use_stock'] + 1;
                                        $price = $session[$key]['price'];
                                        $qty = $session[$key]['qty'];
                                    } else {
                                        return response(['success' => false, 'data' => 'out of stock']);
                                    }
                                } else {
                                    if ($session[$key]['qty'] > 0) {
                                        $session[$key]['qty'] -= 1;
                                        $session[$key]['price'] = $session[$key]['price'] - $medicine->price_pr_strip;
                                        $session[$key]['use_stock'] = $session[$key]['use_stock'] - 1;
                                        $price = $session[$key]['price'];
                                        $qty = $session[$key]['qty'];
                                    }
                                    if (intval($session[$key]['qty']) == 0) {
                                        $cartString .= '<a href="javascript:void(0);" onclick="addCart(' . $session[$key]['id'] . ',`plus`)" class="cart text-primary cursor-pointer">';
                                        $cartString .= '<i class="fa-solid fa-bag-shopping"></i></a>';
                                        unset($session[$key]);
                                    }
                                }
                            }
                        } else {
                            return response(['success' => false, 'data' => 'Out of stock']);
                        }
                    }
                    Session::put('cart', array_values($session));
                    $total_items = count(Session::get('cart'));
                    $total_price = array_sum(array_column(Session::get('cart'), 'price'));
                    return response(['success' => true, 'data' => ['qty' => $qty, 'item_price' =>  $price, 'total_items' => $total_items, 'total_price' => $total_price, 'cartString' => $cartString]]);
                } else {
                    if ($medicine->total_stock > $medicine->use_stock) {
                        if ($data['operation'] == "plus") {
                            $master = array();
                            $master['id'] = $request->id;
                            $master['price'] = intval($medicine->price_pr_strip);
                            $master['original_price'] = intval($medicine->price_pr_strip);
                            $master['qty'] = 1;
                            $master['image'] = $medicine->full_image;
                            $master['name'] = $medicine->name;
                            $master['prescription_required'] = $medicine->prescription_required;
                            $master['available_stock'] = $medicine->total_stock - $medicine->use_stock;
                            $master['use_stock'] = intval($medicine->use_stock) + 1;
                            array_push($session, $master);
                            $price = intval($medicine->price_pr_strip);
                            $qty = 1;
                            // $cartString .= '<div class="counter">';
                            // $cartString .= '<div class="d-flex align-items-center ">';
                            // $cartString .= '<span class="minus btn" onclick="addCart('.$medicine->id.','."`minus`".')"  id="minus'.$medicine->id.'" href="javascrip:void(0)">-</span>';
                            // $cartString .= '<p class="value text-center m-auto" id="txtCart'.$medicine->id.'" name="quantity'.$medicine->id.'">1</p>';
                            // $cartString .= '<span class="incris btn" onclick="addCart('.$medicine->id.','."`plus`".')"  id="plus'.$medicine->id.'" href="javascrip:void(0)">+</span>';
                            // $cartString .= '</div></div>';

                            $cartString .= '<div class="flex flex-row h-10 w-full rounded-lg relative bg-transparent mt-1">';
                            $cartString .= '<button id="minus' . $medicine['id'] . '" onclick="addCart(' . $medicine->id . ',' . "`minus`" . ')"  data-te-ripple-init data-te-ripple-color="light" data-action="decrement" class="border-l border-t border-t border-b border-white-light text-black-600 hover:text-black-700 h-8 w-6 cursor-pointer">';
                            $cartString .= '<span class="m-auto text-2xl font-thin">−</span>';
                            $cartString .= '</button>';
                            $cartString .= '<input id="txtCart' . $request->id . '" type="number" readonly class="border-t border-b border-white-light outline-none focus:outline-none text-center w-10 font-semibold text-md hover:text-black focus:text-black md:text-basecursor-default flex items-center text-primary h-8" name="custom-input-number" value="1"></input>';
                            $cartString .= '<button onclick="addCart(' . $medicine->id . ',' . "`plus`" . ')"  data-te-ripple-init data-te-ripple-color="light" data-action="increment" class="border-r border-t border-t border-b border-white-light text-black-600 hover:text-black-700 h-8 w-6 cursor-pointer">';
                            $cartString .= '<span class="m-auto text-2xl font-thin">+</span>';
                            $cartString .= '</button>';
                            $cartString .= '</div>';
                        }
                        Session::put('cart', array_values($session));
                        $total_items = count(Session::get('cart'));
                        $total_price = array_sum(array_column(Session::get('cart'), 'price'));
                        return response(['success' => true, 'data' => ['qty' => $qty, 'item_price' =>  $medicine->price_pr_strip, 'total_price' => $total_price, 'total_items' => $total_items, 'cartString' => $cartString]]);
                    } else {
                        return response(['success' => false, 'data' => 'Out of stock']);
                    }
                }
            } else {
                return response(['success' => false, 'data' => 'pharmacy not same']);
            }
        }
    }

    public function medicineDetails($id, $name)
    {
        $medicine = Medicine::find($id);
        $currency = Setting::first()->currency_symbol;
        return view('website.medicine_detail', compact('medicine', 'currency'));
    }

    public function viewCart()
    {
        if (Session::has('cart')) {
            $currency = Setting::first()->currency_symbol;
            return view('website.shopping_cart', compact('currency'));
        }
        return redirect('/');
    }

    public function removeSingleItem($cart_id)
    {
        $session = Session::get('cart');
        foreach ($session as $key => $value) {
            if (isset($cart_id)) {
                if ($value['id'] == $cart_id)
                    unset($session[$key]);
            }
        }
        session(['cart' => $session]);
        if (count(Session::get('cart')) <= 0) {
            session()->forget('cart');
            session()->forget('pharmacy');
        }
        return redirect()->back();
    }

    public function addReview(Request $request)
    {
        (new CustomController)->cancel_max_order();
        $request->validate([
            'review' => 'bail|required',
            'rate' => 'bail|required'
        ]);
        $data = $request->all();
        if (Review::where([['appointment_id', $data['appointment_id'], ['user_id', auth()->user()->id]]])->exists() != true) {
            $data['doctor_id'] = Appointment::find($data['appointment_id'])->doctor_id;
            $data['user_id'] = auth()->user()->id;
            Review::create($data);
            return response(['success' => true]);
        } else {
            return response(['success' => false, 'data' => __('Review Already Added.!')]);
        }
    }

    public function checkout()
    {
        $setting = Setting::first();
        $session = Session::get('cart');
        $grandTotal = 0;
        $prescription = 0;
        foreach ($session as $value) {
            $prescription = $value['prescription_required'] == 1 ? 1 : 0;
        }
        $master = [];
        $master['totalItems'] = count(Session::get('cart'));
        $master['setting'] = Setting::find(1);
        $master['prescription'] = $prescription;
        $master['address'] = UserAddress::where('user_id', auth()->user()->id)->get();
        return view('website.billing_detail', compact('setting', 'master'));
    }

    public function bookMedicine(Request $request)
    {
        $data = $request->all();
        if (isset($data['pdf'])) {
            $test = explode('.', $data['pdf']);
            $ext = end($test);
            $name = uniqid() . '.' . 'pdf';
            $location = public_path() . '/prescription/upload';
            $data['pdf']->move($location, $name);
            $data['pdf'] = $name;
        }
        $data['user_id'] = auth()->user()->id;
        $data['medicine_id'] = '#' . rand(100000, 999999);
        $data['payment_type'] = strtoupper($request->payment_type);
        $data['pharmacy_id'] = Session::get('pharmacy')->id;

        $commission = Session::get('pharmacy')->commission_amount;
        $com = $data['amount'] * $commission;
        $data['admin_commission'] = $com / 100;
        $data['pharmacy_commission'] = $data['amount'] - $data['admin_commission'];
        $purchase = PurchaseMedicine::create($data);
        foreach (Session::get('cart') as $value) {
            $master = array();
            $master['purchase_medicine_id'] = $purchase->id;
            $master['medicine_id'] = $value['id'];
            $master['price'] = $value['price'];
            $master['qty'] = $value['qty'];
            $medicine = Medicine::find($value['id']);
            $available_stock = $medicine->total_stock - $value['qty'];
            $medicine->update(['use_stock' => $value['use_stock']]);
            $medicine->update(['total_stock' => $available_stock]);
            MedicineChild::create($master);
        }

        $settle = array();
        $settle['purchase_medicine_id'] = $purchase->id;
        $settle['pharmacy_id'] = $purchase->pharmacy_id;
        $settle['pharmacy_amount'] = $purchase->pharmacy_commission;
        $settle['admin_amount'] = $purchase->admin_commission;
        $settle['payment'] = $purchase->payment_type == 'COD' ? 0 : 1;
        $settle['pharmacy_status'] = 0;
        PharmacySettle::create($settle);
        session()->forget('cart');
        session()->forget('pharmacy');
        return response(['success' => true]);
    }

    public function aboutUs()
    {
        $aboutUs = Setting::first()->about_us;
        return view('website.about', compact('aboutUs'));
    }

    public function privacy()
    {
        $privacy_policy = Setting::first()->privacy_policy;
        return view('website.privacy', compact('privacy_policy'));
    }

    public function userProfile()
    {
        (new CustomController)->cancel_max_order();
        $setting = Setting::first();
        $appointments = Appointment::with(['doctor', 'hospital'])->where('user_id', auth()->user()->id)->orderBy('id', 'DESC')->get();
        foreach ($appointments as $appointment) {
            $appointment->isReview = Review::where('appointment_id', $appointment->id)->exists();
        }
        $prescriptions = Prescription::with(['doctor', 'appointment'])->where('user_id', auth()->user()->id)->orderBy('id', 'DESC')->get();
        $purchaseMedicines = PurchaseMedicine::where('user_id', auth()->user()->id)->orderBy('id', 'DESC')->get();
        $currency = $setting->currency_symbol;
        $cancel_reason = $setting->cancel_reason;
        return view('website.user.user_profile', compact('appointments', 'purchaseMedicines', 'currency', 'prescriptions', 'cancel_reason'));
    }

    public function testReport()
    {
        $test_reports = Report::with('lab:id,name')->where('user_id', auth()->user()->id)->orderBy('id', 'DESC')->get();
        $currency = Setting::first()->currency_symbol;
        return view('website.user.test_report', compact('test_reports', 'currency'));
    }

    public function download_report($report_id)
    {
        $id = Report::find($report_id);
        $pathToFile = public_path() . "/report_prescription/report/" . $id->upload_report;
        $name = $id->upload_report;
        return response()->download($pathToFile, $name);
    }

    public function patientAddress()
    {
        $addresses = UserAddress::where('user_id', auth()->user()->id)->get();
        $setting = Setting::first();
        return view('website.user.patient_address', compact('addresses', 'setting'));
    }

    public function favorite()
    {
        $fav_docs = Faviroute::where('user_id', auth()->user()->id)->get(['doctor_id']);
        $doctors = Doctor::whereIn('id', $fav_docs)->get();
        foreach ($doctors as $doctor) {
            $doctor->hospital = (new CustomController)->getHospital($doctor['id']);
            $doctor['is_fav'] = $this->checkFavourite($doctor->id);
        }
        $currency = Setting::first()->currency_symbol;
        return view('website.user.favorite', compact('doctors', 'currency'));
    }

    public function profileSetting()
    {
        $languages = Language::where('status', 1)->get();
        return view('website.user.profile_setting', compact('languages'));
    }

    public function changePassword()
    {
        return view('website.user.change_password');
    }

    public function single_report($report_id)
    {
        $report = Report::find($report_id);
        $currency = Setting::first()->currency_symbol;
        return response(['success' => true, 'data' => $report, 'currency' => $currency]);
    }
    public function update_user_profile(Request $request)
    {
        $data = $request->all();
        $request->validate([
            'name' => 'bail|required',
            'dob' => 'bail|required',
            'gender' => 'bail|required',
        ]);
        $user = auth()->user();
        if ($request->hasFile('image')) {
            (new CustomController)->deleteFile($user->image);
            $data['image'] = (new CustomController)->imageUpload($request->image);
        }
        $user->update($data);
        $this->changelanguage();
        return redirect()->back()->withStatus(__('patient updated successfully..!!'));
    }
    public function changelanguage()
    {
        App::setLocale(auth()->user()->language);
        session()->put('locale', auth()->user()->language);
        $direction = Language::where('name', auth()->user()->language)->first()->direction;
        session()->put('direction', $direction);
        return true;
    }
    public function selectLanguage($id)
    {
        $language = Language::find($id);
        App::setLocale($language->name);
        session()->put('locale', $language->name);
        $direction = $language->direction;
        session()->put('direction', $direction);
        if (Auth::check()) {
            $user = auth()->user();
            $user->language = $language->name;
            $user->save();
        }
        return redirect()->back();
    }


    public function change_password(Request $request)
    {
        $request->validate([
            'old_password' => 'bail|required|min:6',
            'new_password' => 'bail|required|min:6',
            'confirm_new_password' => 'bail|required|min:6|same:new_password',
        ]);
        $data = $request->all();
        $id = auth()->user();
        if (Hash::check($data['old_password'], $id->password) == true) {
            $id->password = Hash::make($data['new_password']);
            $id->save();
            return redirect()->back()->withStatus(__('Password Changed Successfully.'));
        } else {
            return redirect()->back()->with('error', 'old password does not match');
        }
    }

    public function forgotPassword()
    {
        return view('website.user.forgot_password');
    }

    public function userForgotPassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $setting = Setting::first();
        if ($user) {
            $notification_template = NotificationTemplate::where('title', 'forgot password')->first();
            $password = rand(100000, 999999);
            $detail['user_name'] = $user->name;
            $detail['password'] = $password;
            $detail['app_name'] = $setting->business_name;
            $data = ["{{user_name}}", "{{password}}", "{{app_name}}"];
            $user->password = Hash::make($password);
            $user->save();
            $message1 = str_replace($data, $detail, $notification_template->mail_content);
            try {
                $config = array(
                    'driver'     => $setting->mail_mailer,
                    'host'       => $setting->mail_host,
                    'port'       => $setting->mail_port,
                    'from'       => array('address' => $setting->mail_from_address, 'name' => $setting->mail_from_name),
                    'encryption' => $setting->mail_encryption,
                    'username'   => $setting->mail_username,
                    'password'   => $setting->mail_password
                );
                Config::set('mail', $config);
                Mail::to($user->email)->send(new SendMail($message1, $notification_template->subject));
            } catch (\Throwable $e) {
                //throw $th;
                return $e->getMessage();
            }
            return redirect()->back()->with('status', __('Password sent into your mail'));
        } else {
            return redirect()->back()->withErrors(__('User not found'));
        }
    }

    public function addAddress(Request $request)
    {
        $request->validate([
            'address' => 'required',
        ]);
        $user_address = UserAddress::create($request->all());
        return response()->json(['success' => true, 'data' => $user_address]);
    }

    public function edit_user_address($address_id)
    {
        $address_id = UserAddress::find($address_id);
        return response(['success' => true, 'data' => $address_id]);
    }
    public function update_user_address(Request $request, $address_id)
    {

        $user_address = UserAddress::find($address_id);
        $user_address->update($request->all());

        return response()->json(['success' => true, 'data' => $user_address]);
    }
    public function delete_user_address($address_id)
    {
        $user_address = UserAddress::find($address_id);
        $user_address->delete();
        return response(['success' => true]);
    }
    public function cancelAppointment(Request $request)
    {
        $data = $request->all();
        $id = Appointment::find($data['id']);
        $data['appointment_status'] = 'cancel';
        $id->update($data);
        return response(['success' => true]);
    }

    public function deleteAccount()
    {
        $user = auth()->user();
        $booking = Appointment::where('user_id', $user->id)->where('payment_status', 0)->first();
        if (isset($booking)) {
            return response()->json(['success' => false, 'message' => 'You have upcoming appointments that need to be canceled first!']);
        } else {
            $timezone = Setting::first()->timezone;
            $user->name = 'Deleted User';
            $user->email = ' deleteduser_' . Carbon::now($timezone)->format('Y_m_d_H_i_s') . '@saasmonks.in';
            $user->phone = '0000000000';
            $user->verify = 0;
            $user->status = 0;
            $user->save();
            Auth::user()->tokens->each(function ($token, $key) {
                $token->delete();
            });
            Auth::logout();
            return response()->json(['success' => true, 'message' => 'Account Delete Successfully!']);
        }
    }
}
