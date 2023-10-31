<?php

use App\Http\Controllers\Doctor\DoctorSubscriptionController;
use App\Http\Controllers\lab\PathologyController;
use App\Http\Controllers\lab\RadiologyController;
use App\Http\Controllers\LabSettingController;
use App\Http\Controllers\MultiDeleteController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\AdminUserController;
use App\Http\Controllers\SuperAdmin\AppointmentController;
use App\Http\Controllers\SuperAdmin\BannerController;
use App\Http\Controllers\SuperAdmin\BlogController;
use App\Http\Controllers\SuperAdmin\CategoryController;
use App\Http\Controllers\SuperAdmin\CustomController;
use App\Http\Controllers\SuperAdmin\DoctorController;
use App\Http\Controllers\SuperAdmin\ExpertiseController;
use App\Http\Controllers\SuperAdmin\HospitalController;
use App\Http\Controllers\SuperAdmin\HospitalGalleryController;
use App\Http\Controllers\SuperAdmin\LabController;
use App\Http\Controllers\SuperAdmin\LanguageController;
use App\Http\Controllers\SuperAdmin\MedicineCategoryController;
use App\Http\Controllers\SuperAdmin\MedicineController;
use App\Http\Controllers\SuperAdmin\NotificationTemplateController;
use App\Http\Controllers\SuperAdmin\OfferController;
use App\Http\Controllers\SuperAdmin\PathologyCategoryController;
use App\Http\Controllers\SuperAdmin\PharmacyController;
use App\Http\Controllers\SuperAdmin\RadiologyCategoryController;
use App\Http\Controllers\SuperAdmin\ReportController;
use App\Http\Controllers\SuperAdmin\RoleController;
use App\Http\Controllers\SuperAdmin\SettingController;
use App\Http\Controllers\SuperAdmin\SubscriptionController;
use App\Http\Controllers\SuperAdmin\TreatmentsController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\UserApiController;
use App\Http\Controllers\MiControlador;
use App\Http\Controllers\Website\WebsiteController;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Auth::routes();

// Route::get('installer',[AdminController::class,'installer']);
Route::any('installer',[AdminController::class,'installer']);

Route::get('/spatie-cache-clear',function ()
{
    Artisan::call('cache:forget spatie.permission.cache');
    return 'Cache cleared';
});

Route::get('/clear-cache',function ()
{
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    return "Cache is cleared";
});

Route::middleware(['XssSanitizer'])->group(function ()
{
    Route::get('/',[WebsiteController::class,'index']);
    Route::any('/especialistas',[WebsiteController::class,'doctor']);
    
    Route::any('/show-doctors',[WebsiteController::class,'doctor']);
    Route::get('/consigue-cita', [MiControlador::class,'miMetodo'])->name('mi.cita');
    Route::get('/doctor-profile/{id}/{name}',[WebsiteController::class,'doctor_profile']);
    Route::any('/all-pharmacies',[WebsiteController::class,'pharmacy']);
    Route::any('/all-labs',[WebsiteController::class,'labs']);
    Route::get('/signup',[WebsiteController::class,'signup']);
    Route::any('/patient-login',[WebsiteController::class,'patientLogin'])->name('k.login');
    Route::get('/our_blogs',[WebsiteController::class,'ourBlogs']);
    Route::get('/se_parte',[WebsiteController::class,'seParte']);
    Route::get('/kullay_empresas',[WebsiteController::class,'kullayEmpresas']);
    Route::get('/blog-details/{id}/{blog_name}',[WebsiteController::class,'singleBlog']);
    Route::get('/our-offers',[WebsiteController::class,'ourOffers']);
    Route::post('/signUp',[WebsiteController::class,'sign_up']);
    Route::get('/send_otp',[WebsiteController::class,'sendOtp']);   
    Route::post('/verify_user',[WebsiteController::class,'verify_user']);
    Route::post('/doctorRegister',[WebsiteController::class,'doctorRegister']);
    Route::get('/pharmacy-details/{id}/{name}',[WebsiteController::class,'pharmacyDetails']);
    Route::any('/pharmacy-product/{id}/{name}',[WebsiteController::class,'pharmacyProduct']);
    Route::any('/addCart',[WebsiteController::class,'addCart']);
    Route::get('medicine-details/{id}/{name}',[WebsiteController::class,'medicineDetails']);
    Route::get('view-cart',[WebsiteController::class,'viewCart']);
    Route::get('remove_single_item/{id}',[WebsiteController::class,'removeSingleItem']);
    Route::get('/addBookmark/{doctor_id}',[WebsiteController::class,'addBookmark']);
    Route::get('/radiology_category_wise/{id}/{lab_id}',[WebsiteController::class,'radiology_category_wise']);
    Route::post('/single_radiology_details',[WebsiteController::class,'single_radiology_details']);
    Route::get('/about-us',[WebsiteController::class,'aboutUs']);
    Route::get('/privacy-policy',[WebsiteController::class,'privacy']);
    Route::get('/select_language/{id}',[WebsiteController::class,'selectLanguage']);

    Route::get('/forgot_password',[WebsiteController::class,'forgotPassword']);
    Route::post('/user_forget_password',[WebsiteController::class,'userForgotPassword']);

    Route::middleware(['auth'])->group(function ()
    {
        Route::get('/user_profile',[WebsiteController::class,'user_profile']);
        Route::get('/lab_test/{id}/{name}',[WebsiteController::class,'labTest']);
        Route::get('/booking/{id}/{name}',[WebsiteController::class,'booking']);
        Route::any('/especialista/{id}',[WebsiteController::class,'modal']);
        Route::post('/bookAppointment',[WebsiteController::class,'bookAppointment']);
        Route::post('cancelAppointment',[WebsiteController::class,'cancelAppointment']);
        Route::post('checkCoupen',[WebsiteController::class,'checkCoupen']);
        Route::get('/checkout',[WebsiteController::class,'checkout']);
        Route::post('addReview',[WebsiteController::class,'addReview']);

        // Lab
        Route::get('/pathology_category_wise/{id}/{lab_id}',[WebsiteController::class,'pathology_category_wise']);
        Route::post('/single_pathology_details',[WebsiteController::class,'single_pathology_details']);
        Route::post('test_report',[WebsiteController::class,'test_report']);
        Route::post('lab_timeslot',[WebsiteController::class,'lab_timeslot']);
        Route::post('addAddress',[WebsiteController::class,'addAddress']);
        Route::get('edit_user_address/{address_id}',[WebsiteController::class,'edit_user_address']);
        Route::post('update_user_address/{address_id}',[WebsiteController::class,'update_user_address']);
        Route::get('address_delete/{address_id}',[WebsiteController::class,'delete_user_address']);
        Route::post('/displayHospital',[WebsiteController::class,'displayHospital']);
        Route::post('/displayTimeslot',[WebsiteController::class,'displayTimeslot']);
        Route::post('getDeliveryCharge',[WebsiteController::class,'getDeliveryCharge']);
        Route::post('bookMedicine',[WebsiteController::class,'bookMedicine']);

        // User
        Route::get('/user_profile',[WebsiteController::class,'userProfile']);
        Route::get('/test-report',[WebsiteController::class,'testReport']);
        Route::get('/patient-address',[WebsiteController::class,'patientAddress']);
        Route::get('/favorite',[WebsiteController::class,'favorite']);
        Route::get('/profile-setting',[WebsiteController::class,'profileSetting']);
        Route::get('/change-password',[WebsiteController::class,'changePassword']);
        Route::get('downloadPDF/{id}',[WebsiteController::class,'downloadPDF']);
        Route::get('download_report/{id}',[WebsiteController::class,'download_report']);
        Route::get('single_report/{id}',[WebsiteController::class,'single_report']);
        Route::post('/update_user_profile',[WebsiteController::class,'update_user_profile']);
        Route::post('/update_change_password',[WebsiteController::class,'change_password']);
        Route::get('/delete_account',[WebsiteController::class,'deleteAccount']);

    });

    /******* SUPER ADMIN PANEL */
    Route::get('/login',[AdminController::class,'admin_login']);
    Route::post('/admin/verify_admin',[AdminController::class,'verify_admin']);
    Route::get('/admin_forgot_password',[AdminController::class,'admin_forgot_password']);
    Route::get('/pharmacy_forgot_password',[AdminController::class,'pharmacy_forgot_password']);
    Route::get('/lab_forgot_password',[AdminController::class,'lab_forgot_password']);
    Route::post('/send_forgot_password',[AdminController::class,'send_forgot_password']);
    Route::get('/doctor_forgot_password',[AdminController::class,'doctor_forgot_password']);
    Route::get('/change_language/{id}',[AdminController::class,'changeLanguage']);

    Route::middleware(['auth'])->group(function ()
    {
        Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
        // Settings
        Route::get('/setting',[SettingController::class,'setting']);
        Route::post('/update_payment_setting',[SettingController::class,'update_payment_setting']);
        Route::post('/update_verification_setting',[SettingController::class,'update_verification_setting']);
        Route::post('/update_notification',[SettingController::class,'update_notification']);
        Route::post('/update_licence_setting',[SettingController::class,'update_licence_setting']);
        Route::post('/update_video_call_setting',[SettingController::class,'update_video_call_setting']);
        Route::post('/test_mail',[AdminController::class,'testMail']);

        // pending blog
        Route::get('/blog/pending-blog',[BlogController::class,'pending_blog']);

        Route::resources([
            'treatments' => TreatmentsController::class,
            'category' => CategoryController::class,
            'expertise' => ExpertiseController::class,
            'hospital' => HospitalController::class,
            'role' => RoleController::class,
            'subscription' => SubscriptionController::class,
            'patient' => UserController::class,
            'offer' => OfferController::class,
            'banner' => BannerController::class,
            'medicineCategory' => MedicineCategoryController::class,
            'language' => LanguageController::class,
            'laboratory' => LabController::class,
            'pathology_category' => PathologyCategoryController::class,
            'radiology_category' => RadiologyCategoryController::class,
            'admin_users' => AdminUserController::class,
        ]);

        Route::get('test_reports',[LabController::class,'test_reports']);
        Route::post('upload_report',[LabController::class,'upload_report']);

        // pharmacy commission
        Route::get('pharmacy_commission/{pharmacy_id}',[pharmacyController::class,'pharmacy_commission']);
        Route::post('show_pharmacy_settle_details',[pharmacyController::class,'show_pharmacy_settalement']);
        Route::get('pharmacy_schedule/{pharmacy_id}',[pharmacyController::class,'pharmacy_schedule']);

        // Doctor
        Route::get('/doctor/{id}/{name}/{with}',[DoctorController::class,'show']);
        Route::get('/display_timeslot/{id}',[DoctorController::class,'display_timeslot']);
        Route::get('/edit_timeslot/{id}',[DoctorController::class,'edit_timeslot']);
        Route::post('/update_timeslot',[DoctorController::class,'update_timeslot']);
        Route::post('/doctor/doc_change_password',[DoctorController::class,'change_password']);
        Route::resource('doctor',DoctorController::class)->except([
            'show'
        ]);

        // Hospital Gallery
        Route::get('/hospitalGallery/{hospital_id}',[HospitalGalleryController::class,'index']);
        Route::resource('hospitalGallery',HospitalGalleryController::class)->except([
            'index','create'
        ]);

        // Appointment
        Route::get('/appointment',[AppointmentController::class,'appointment']);
        Route::get('/inCalendar',[AppointmentController::class,'inCalendar']);
        Route::get('/create_appointment/{create_id}',[AppointmentController::class,'createAppointment']);
        Route::post('/store_appointment/{store_id}',[AppointmentController::class,'storeAppointment']);
        Route::get('/edit_appointment/{edit_id}',[AppointmentController::class,'editAppointment']);
        Route::post('/update_appointment/{update_id}',[AppointmentController::class,'updateAppointment']);
        Route::get('/delete_appointment/{delete_id}',[AppointmentController::class,'deleteAppointment']);
        Route::post('/changeTimeslot',[AppointmentController::class,'changeTimeslot']);
        Route::post('/add-address',[AppointmentController::class,'addAddr']);

        // display and update stock in medicine
        Route::get('medicine/display_stock/{id}',[MedicineController::class,'display_stock']);
        Route::post('medicine/update_stock',[MedicineController::class,'update_stock']);

        // change status
        Route::POST('/treatments/change_status',[TreatmentsController::class,'change_status']);
        Route::POST('/category/change_status',[CategoryController::class,'change_status']);
        Route::POST('/expertise/change_status',[ExpertiseController::class,'change_status']);
        Route::POST('/medicine/change_status',[MedicineController::class,'change_status']);
        Route::POST('/hospital/change_status',[HospitalController::class,'change_status']);
        Route::POST('/doctor/change_status',[DoctorController::class,'change_status']);
        Route::POST('/user/change_status',[UserController::class,'change_status']);
        Route::POST('/banner/change_status',[BannerController::class,'change_status']);
        Route::POST('/pharmacy/change_status',[pharmacyController::class,'change_status']);
        Route::POST('/medicineCategory/change_status',[MedicineCategoryController::class,'change_status']);
        Route::POST('/language/change_status',[LanguageController::class,'change_status']);
        Route::POST('/offer/change_status',[OfferController::class,'change_status']);
        Route::POST('/lab/change_status',[LabController::class,'change_status']);
        Route::POST('/pathology_category/change_status',[PathologyCategoryController::class,'change_status']);
        Route::POST('/radiology_category/change_status',[RadiologyCategoryController::class,'change_status']);
        Route::POST('/pathology/change_status',[PathologyController::class,'change_status']);
        Route::POST('/radiology/change_status',[RadiologyController::class,'change_status']);
        Route::POST('/admin_users/change_status',[AdminUserController::class,'change_status']);

        // display category ( treatment wise category )
        Route::get('display_category/{treatment_id}',[CustomController::class,'display_category']);
        Route::get('display_expertise/{expertise_id}',[CustomController::class,'display_expertise']);

        // Notification template
        Route::get('notification_template',[NotificationTemplateController::class,'index']);
        Route::get('edit_notification/{id}',[NotificationTemplateController::class,'edit_notification']);

        // admin profile
        Route::get('profile',[AdminController::class,'profile']);
        Route::post('update_profile',[AdminController::class,'update_profile']);
        Route::post('change_password',[AdminController::class,'change_password']);

        // subscription history
        Route::get('subscription_history',[SubscriptionController::class,'subscription_history']);

        // commission
        Route::get('show_appointment/{appointment_id}',[AppointmentController::class,'show_appointment']);

        // report
        Route::get('/user_report',[ReportController::class,'user_report']);
        Route::post('/user_report',[ReportController::class,'user_report']);
        Route::get('/doctor_report',[ReportController::class,'doctor_report']);
        Route::post('/doctor_report',[ReportController::class,'doctor_report']);

        // Download Language Sample File
        Route::get('downloadFile',[LanguageController::class,'downloadFile']);

        // Multi delete
        Route::post('appointment_all_delete',[MultiDeleteController::class,'appointment_all_delete']);
        Route::post('category_all_delete',[MultiDeleteController::class,'category_all_delete']);
        Route::post('treatment_all_delete',[MultiDeleteController::class,'treatment_all_delete']);
        Route::post('expertise_all_delete',[MultiDeleteController::class,'expertise_all_delete']);
        Route::post('medicineCategory_all_delete',[MultiDeleteController::class,'medicineCategory_all_delete']);
        Route::post('hospital_all_delete',[MultiDeleteController::class,'hospital_all_delete']);
        Route::post('doctor_all_delete',[MultiDeleteController::class,'doctor_all_delete']);
        Route::post('pharmacy_all_delete',[MultiDeleteController::class,'pharmacy_all_delete']);
        Route::post('patient_all_delete',[MultiDeleteController::class,'patient_all_delete']);
        Route::post('offer_all_delete',[MultiDeleteController::class,'offer_all_delete']);
        Route::post('medicine_all_delete',[MultiDeleteController::class,'medicine_all_delete']);
        Route::post('pathology_all_delete',[MultiDeleteController::class,'pathology_all_delete']);
        Route::post('pathology_all_delete',[MultiDeleteController::class,'pathology_all_delete']);
        Route::post('lab_all_delete',[MultiDeleteController::class,'lab_all_delete']);
        Route::post('pathology_cat_all_delete',[MultiDeleteController::class,'pathology_cat_all_delete']);
        Route::post('radiology_cat_all_delete',[MultiDeleteController::class,'radiology_cat_all_delete']);
    });

    /******* DOCTOR PANEL */
    Route::get('/doctor/doctor_login',[App\Http\Controllers\Doctor\DoctorController::class,'doctorLogin']);
    Route::get('/doctor/doctor_signup',[App\Http\Controllers\Doctor\DoctorController::class,'doctorSignup']);
    Route::post('/doctor/verify_doctor',[App\Http\Controllers\Doctor\DoctorController::class,'verify_doctor']);
    Route::post('/doctor/doctor_register',[App\Http\Controllers\Doctor\DoctorController::class,'doctor_register']);
    Route::get('/doctor/send_otp/{id}',[App\Http\Controllers\Doctor\DoctorController::class,'send_otp']);
    Route::post('/doctor/verify_otp',[App\Http\Controllers\Doctor\DoctorController::class,'verify_otp']);

    Route::middleware(['auth'])->group(function ()
    {
        Route::get('/doctor_home',[App\Http\Controllers\Doctor\DoctorController::class,'doctor_home']);
        Route::get('/calendar',[AppointmentController::class,'calendar']);
        Route::get('/subscription_purchase/{subscription_id}',[DoctorSubscriptionController::class,'subscription_purchase']);
        Route::post('/stripePayment',[DoctorSubscriptionController::class,'stripePayment']);

        // payment
        Route::post('/purchase',[DoctorSubscriptionController::class,'purchase']);
        Route::get('/subscription_flutter/{id}',[DoctorSubscriptionController::class,'subscription_flutter']);
        Route::get('/subscription_flutter_verify/{id}',[DoctorSubscriptionController::class,'subscription_flutter_verify']);

        // schedule
        Route::get('/schedule',[App\Http\Controllers\Doctor\DoctorController::class,'schedule']);

        // doctor profile
        Route::get('/doctor_profile',[App\Http\Controllers\Doctor\DoctorController::class,'doctor_profile']);
        Route::post('/update_doctor_profile',[App\Http\Controllers\Doctor\DoctorController::class,'update_doctor_profile']);
        Route::get('/changePassword',[App\Http\Controllers\Doctor\DoctorController::class,'changePassword']);

        // change subscriptiom payment status changePaymentStatus
        Route::get('subscription/changePaymentStatus/{id}',[SubscriptionController::class,'changePaymentStatus']);

        Route::get('prescription/{appointment_id}',[AppointmentController::class,'prescription']);
        Route::get('allMedicine',[AppointmentController::class,'all_medicine']);
        Route::post('addPrescription',[AppointmentController::class,'addPrescription']);

        Route::get('commission',[AppointmentController::class,'commission']);
        Route::post('show_settalement',[AppointmentController::class,'show_settlement']);

        Route::get('acceptAppointment/{appointment_id}',[AppointmentController::class,'acceptAppointment']);
        Route::get('cancelAppointment/{appointment_id}',[AppointmentController::class,'cancelAppointment']);
        Route::get('completeAppointment/{appointment_id}',[AppointmentController::class,'completeAppointment']);

        // doctor review
        Route::get('doctor_review',[App\Http\Controllers\Doctor\DoctorController::class,'doctor_review']);

        // Zoom Metting
        Route::get('/list',[App\Http\Controllers\Doctor\ZoomController::class,'list']);
        Route::get('set_key',[App\Http\Controllers\Doctor\ZoomController::class,'setKey']);
        Route::post('/store_key',[App\Http\Controllers\Doctor\ZoomController::class,'storekey']);
        Route::get('create_zoom_metting/{appoinment_id}',[App\Http\Controllers\Doctor\ZoomController::class,'createZoomMetting']);
        Route::post('/store/{app_stor_id}',[App\Http\Controllers\Doctor\ZoomController::class,'store']);
        Route::get('edit_meeting/{edit_id}',[App\Http\Controllers\Doctor\ZoomController::class,'editMeeting']);
        Route::post('/update_meeting/{update_id}',[App\Http\Controllers\Doctor\ZoomController::class,'updateMeeting']);
        Route::get('/delete_meeting/{delete_id}',[App\Http\Controllers\Doctor\ZoomController::class,'deleteMeeting']);

    });

    /******* PHARMACY PANEL */
    Route::get('/pharmacy_login',[App\Http\Controllers\Pharmacy\PharmacyController::class,'pharmacyLogin']);
    Route::post('/verify_pharmacy',[App\Http\Controllers\Pharmacy\PharmacyController::class,'verify_pharmacy']);
    Route::get('/pharmacy_signUp',[App\Http\Controllers\Pharmacy\PharmacyController::class,'pharmacy_signUp']);
    Route::post('/pharmacy_register',[App\Http\Controllers\Pharmacy\PharmacyController::class,'pharmacy_register']);
    Route::get('/pharmacy_send_otp/{id}/{name}',[App\Http\Controllers\Pharmacy\PharmacyController::class,'pharmacy_send_otp']);
    Route::post('pharmacy_verify_otp',[App\Http\Controllers\Pharmacy\PharmacyController::class,'pharmacy_verify_otp']);
    Route::middleware(['auth'])->group(function ()
    {
        Route::get('/pharmacy_home',[App\Http\Controllers\Pharmacy\PharmacyController::class,'pharmacy_home']);
        Route::get('/pharmacy_schedule',[App\Http\Controllers\Pharmacy\PharmacyController::class,'pharmacy_schedule']);
        Route::get('/display_pharmacy_timeslot/{id}',[App\Http\Controllers\Pharmacy\PharmacyController::class,'display_pharmacy_timeslot']);
        Route::get('/edit_pharmacy_timeslot/{id}',[App\Http\Controllers\Pharmacy\PharmacyController::class,'edit_pharmacy_timeslot']);
        Route::post('/update_pharmacy_timeslot',[App\Http\Controllers\Pharmacy\PharmacyController::class,'update_pharmacy_timeslot']);

        Route::get('/pharmacy_profile',[App\Http\Controllers\Pharmacy\PharmacyController::class,'pharmacy_profile']);
        Route::get('pharmacyCommission',[App\Http\Controllers\Pharmacy\PharmacyController::class,'pharmacyCommission']);
        Route::get('purchased_medicines',[App\Http\Controllers\Pharmacy\PharmacyController::class,'purchased_medicines']);
        Route::get('display_purchase_medicine/{id}',[App\Http\Controllers\Pharmacy\PharmacyController::class,'display_purchase_medicine']);
    });
    // Medicies
    Route::get('app_medicine_flutter_payment/{id}',[UserApiController::class,'app_medicine_flutter_payment']);
    Route::get('app_medicine_transction_confirm/{id}',[UserApiController::class,'app_medicine_transction_confirm']);

    Route::post('/saveEnvData',[AdminController::class,'saveEnvData']);
    Route::post('/saveAdminData',[AdminController::class,'saveAdminData']);

    /******* LABORATORY PANEL */
    Route::get('pathologist_login',[LabSettingController::class,'lab_login']);
    Route::post('verify_pathologist',[LabSettingController::class,'verify_pathologist']);
    Route::get('pathologist_sign_up',[LabSettingController::class,'pathologist_sign_up']);
    Route::post('verify_sign_up',[LabSettingController::class,'verify_sign_up']);
    Route::get('pathologist_send_otp',[LabSettingController::class,'pathologist_send_otp']);
    Route::post('verify_pathologist_otp',[LabSettingController::class,'verify_pathologist_otp']);

    Route::middleware(['auth'])->group(function (){
        Route::get('pathologist_home',[LabSettingController::class,'pathologist_home']);
        Route::get('lab_commission',[LabSettingController::class,'lab_commission']);
        Route::post('show_lab_settalement',[LabSettingController::class,'show_lab_settalement']);
        Route::get('lab_timeslot',[LabSettingController::class,'lab_timeslot']);
        Route::get('/display_lab_timeslot/{id}',[LabSettingController::class,'display_lab_timeslot']);
        Route::get('/edit_lab_timeslot/{id}',[LabSettingController::class,'edit_lab_timeslot']);
        Route::post('/update_lab_timeslot',[LabSettingController::class,'update_lab_timeslot']);
        Route::get('/change_lab_payment_status/{id}',[LabSettingController::class,'change_lab_payment_status']);
        Route::get('/lab_profile',[LabSettingController::class,'lab_profile']);

        Route::resources([
            'pathology' => PathologyController::class,
            'radiology' => RadiologyController::class,
        ]);
    });
});

Route::middleware(['auth'])->group(function ()
{
    Route::post('/update_static_page',[SettingController::class,'update_static_page']);
    Route::post('/update_content',[SettingController::class,'update_content']);
    Route::post('update_template/{id}',[NotificationTemplateController::class,'update_template']);
    Route::post('/update_pharmacy_profile',[App\Http\Controllers\Pharmacy\PharmacyController::class,'update_pharmacy_profile']);

    Route::resource('blog',BlogController::class);
    Route::resource('medicines',App\Http\Controllers\Pharmacy\MedicineController::class);
    Route::resource('pharmacy',PharmacyController::class);

    // Settings
    Route::post('/update_general_setting',[SettingController::class,'update_general_setting']);

    // Medicine
    Route::get('medicine/{pharmacy_id}',[MedicineController::class,'index']);
    Route::get('medicine/create/{pharmacy_id}',[MedicineController::class,'create']);
    Route::resource('medicine',MedicineController::class)->except([
        'index','create'
    ]);
});