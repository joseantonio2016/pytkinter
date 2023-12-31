import tkinter as tk
from tkinter import filedialog, font, messagebox, ttk
from threading import Thread
import os, json
import visorfileln

class LineaCode:
    def __init__(self, check, num, text):
        self.check_button = check
        self.entry_num = num
        self.entry_text = text

    def grid(self, row):
        self.check_button.grid(row=row, column=0)
        self.entry_num.grid(row=row, column=1)
        self.entry_text.grid(row=row, column=2)

def limpiar_frame():
    for widget in frame.winfo_children():
        widget.destroy()

def reload_memory(lineas=[]):
    list_wf_lineacode.clear()
    for i, linea in enumerate(lineas):
        check_var.append(tk.IntVar(value=0))
        show_var.append(tk.BooleanVar(value=True))
        add_line = tk.Checkbutton(frame, variable=check_var[i])
        #add_line.grid(row=i, column=0)

        num_l = tk.Entry(frame, width=5)
        num_l.insert(0, str(i + 1))
        #num_l.grid(row=i, column=1)

        line_text = tk.Entry(frame, width=60,bg="gray20", fg="white", font=("Courier", 9), bd=0)
        line_text.insert(0, linea.strip())
        #line_text.grid(row=i, column=2, sticky="ew")

        check_var[i].set(0)
        list_wf_lineacode.append(LineaCode(add_line,num_l,line_text))
        #check_buttons.append(add_line)
        #num_entries.append(num_l)
        #text_entries.append(line_text)
    render()

def render(ignore=False):
    if ignore:
        counter = 0
        for wf, show in zip(list_wf_lineacode,show_var):
            if show.get() == True:
                wf.grid(counter)
                counter = counter + 1
            if counter == 500:
                messagebox.showwarning('Render Sesion','Maximo 500 filas')
                return
            
    else:
        if len(list_wf_lineacode) == 0:
            messagebox.showwarning('Archivo','Seleccione un archivo')
            return
        for i, wf in enumerate(list_wf_lineacode):
            if i == 500:
                messagebox.showwarning('Render Inicial','Maximo 500 filas')
                return
            else:
                wf.grid(i)

def open_load_file(file):
    try:
        with open(file, "r") as file:
                    lineas = file.readlines()
                    reload_memory(lineas)
    except:
        messagebox.showerror('Archivo','No se pudo abrir')
        return
    
def seleccionar_archivo():
    sizelim = 100 * 1024
    archivo = filedialog.askopenfilename()
    if archivo:
        sizefile = os.path.getsize(archivo)
        if sizefile > sizelim:
            messagebox.showwarning('Archivo pesado','Excedio el limite 100KB')
            return
        ruta_archivo.set(archivo)
        et_target.insert(0, archivo)
        open_load_file(ruta_archivo.get())
    else:
        messagebox.showwarning('Advertencia','Archivo no encontrado')

def command_replace_line():
    lines=[]
    try:
        with open(et_target.get(), "w") as file:
            for i, lc in enumerate(list_wf_lineacode):
                if check_var[i].get() == 1:
                    lines.append(lc.entry_text.get())
            file.writelines(line + '\n' for line in lines)
            messagebox.showinfo('Reemplazo completado','Se escribio en archivo destino')
    except FileNotFoundError:
        messagebox.showerror('Error al abrir archivo',"No se puede abrir el archivo indicado")


def on_configure(event):
    # Actualiza el área desplegable del lienzo cuando el marco cambia de tamaños
    canvas.configure(scrollregion=canvas.bbox("all"))


def all_show():
    for var in show_var:
        var.set(True)

def all_check(check=1):
    for var in check_var:
        var.set(check)

def check_by_entry(check=1):
    entrada = et_select.get()
    elementos = entrada.split(",")
    elementos = [item for item in elementos if item != ""]
    if len(elementos) == 0:
        messagebox.showwarning('Sin datos','Indique numero o rango')
        return
    
    for elem in elementos:
        if "-" in elem:
            inicio, fin = map(int, elem.split("-"))
            for i in range(inicio, fin + 1):
                check_var[i - 1].set(check)
        else:
            index = int(elem)
            check_var[index - 1].set(check)

def all_checkpoint():
    if checkpoint.get() == 0:
        tg_allcheck.config(relief="sunken")  # Cambia el efecto visual A
        checkpoint.set(1)
        all_check()
    else:
        tg_allcheck.config(relief="raised")  # Cambia el efecto visual B
        checkpoint.set(0)
        all_check(0)

def ignore_view():
    entrada = et_ignore.get()
    elementos = entrada.split(",")
    elementos = [item for item in elementos if item != ""]
    if len(elementos) == 0:
        messagebox.showwarning('Sin datos','Indique numero o rango')
        return
    if len(show_var) == 0:
        messagebox.showwarning('Archivo','Seleccione un archivo')
        return
    all_show()
    for elem in elementos:
        if "-" in elem:
            inicio, fin = map(int, elem.split("-"))
            if inicio < 1:
                messagebox.showwarning('Fuera de indice','Indique minimo 1')
                return
            for i in range(inicio, fin + 1):
                show_var[i-1].set(False)
        else:
            index = int(elem)
            show_var[index - 1].set(False)
    render(True)

def save_session_linefile():
    # Obtiene los valores de los Entry y los almacena en un diccionario
    datos = {
        "source" : ruta_archivo.get(),
        "target" : et_target.get(),
        "select" : et_select.get(),
        "ignore" : et_ignore.get()
    }
    if ruta_archivo.get() == "":
        messagebox.showwarning('Sin archivo','Indique un archivo')
        return
    file_path = filedialog.asksaveasfilename(defaultextension=".json", filetypes=[("json", "*_lf.json")])
    if file_path:
        # Guarda los datos en un archivo JSON
        with open(file_path, "w") as file:
            json.dump(datos, file)

def open_session_linefile():
    pathfile = filedialog.askopenfilename(filetypes=[("Archivos JSON", "*_lf.json")])
    if pathfile:
        with open(pathfile, 'r') as file:
            datos = json.load(file)
            ruta_archivo.set(datos['source'])
            et_target.insert(0,datos['target'])
            et_select.insert(0,datos['select'])
            et_ignore.insert(0,datos['ignore'])

root = tk.Tk()
root.title("ManageLineFile")

width_root = 500
height_root = root.winfo_screenheight()  # Utiliza toda la altura de la pantalla
x_root = 20

# Define la nueva fuente
nueva_fuente = font.Font(
    family="Helvetica", size=8
)  # Cambia "Helvetica" por la fuente que desees y 10 por el tamaño deseado
root.option_add("*Font", nueva_fuente)

s = ttk.Style(root)
# s.configure('TNotebook', tabposition='ws')
# s = ttk.Style(root)
s.configure(
    "lefttab.TNotebook", tabposition="wn"
)  # wn because I want the tab to be vertical (on the side not the top)
# s.configure('TNotebook.Tab', align=tk.LEFT)

# Variables
ruta_archivo = tk.StringVar()
check_var = []
check_buttons = []
num_entries = []
text_entries = []
checkpoint = tk.IntVar()  # Variable de control para el estado del toogle
list_wf_lineacode = []
show_var = []
#lineas = []


# Funciones
def about():
    messagebox.showinfo(
        "About", "Este es un programa para manejar líneas en un archivo de texto."
    )

def select_target():
    archivo = filedialog.askopenfilename()
    if archivo:
        et_target.insert(0, archivo)

# Menu
menu = tk.Menu(root)
root.config(menu=menu)
top_menu = tk.Menu(menu)
menu.add_cascade(label="Inicio", menu=top_menu)
top_menu.add_command(label="Abrir Sesion", command=open_session_linefile)
top_menu.add_command(label="Guardar Sesion", command=save_session_linefile)
top_menu.add_separator()
top_menu.add_command(label="Abrir Archivo", command=seleccionar_archivo)
top_menu.add_command(label="Desde Referencia", command=about)
top_menu.add_separator()
top_menu.add_command(label="Salir", command=root.quit)
file_menu = tk.Menu(menu)
menu.add_cascade(label="Archivo", menu=file_menu)
file_menu.add_command(label="Ver archivo fuente", command=lambda: visorfileln.show(root,ruta_archivo.get()))
file_menu.add_command(label="Seleccionar archivo destino", command=select_target)

# Frame
frame_top = tk.Frame(root, bg="red")
frame_top.pack(fill=tk.X)


# Crear ttk.Notebook
top_pane_info = tk.Frame(frame_top)
top_pane_info.pack(side="top", fill="x")

notebook = ttk.Notebook(top_pane_info, style="lefttab.TNotebook")
# Establecer la orientación de las pestañas a la izquierda
# Crear contenido para algunas pestañas
frm_file = ttk.Frame(notebook)
frm_sele = ttk.Frame(notebook)
frm_view = ttk.Frame(notebook)

# Agregar pestañas al Notebook con su respectivo contenido
notebook.add(frm_file, text="    Archivo")
notebook.add(frm_sele, text="  Seleccion")
notebook.add(frm_view, text="Perspectiva")
notebook.pack(fill=tk.X)


# Label
lbl_r = tk.Label(frm_file, text="Fuente:")
lbl_r.grid(row=0, column=0)

lbl_source = tk.Label(frm_file, textvariable=ruta_archivo)
lbl_source.grid(row=0, column=1, columnspan=2)

# Label
lbl_r = tk.Label(frm_file, text="Destino:")
lbl_r.grid(row=1, column=0)

et_target = tk.Entry(frm_file)
et_target.grid(row=1, column=1,columnspan=2, sticky="ew")

btn_update_source = tk.Button(frm_file, text="Actualizar desde Fuente", command=lambda: open_load_file(ruta_archivo.get()))
btn_update_source.grid(row=2, column=0,columnspan=3)

lbl_r = tk.Label(frm_sele, text="Escriba numero o intervalo de lineas: 1,2-5,7")
lbl_r.grid(row=0, column=0, columnspan=3)

et_select = tk.Entry(frm_sele)
et_select.grid(row=1, column=0, columnspan=3, sticky="ew")

checking_entry = tk.Button(frm_sele, text="Marcar", command=lambda: check_by_entry(1))
checking_entry.grid(row=2, column=0)

unckecking_entry = tk.Button(
    frm_sele, text="Desmarcar", command=lambda: check_by_entry(0)
)
unckecking_entry.grid(row=2, column=1)

tg_allcheck = tk.Button(frm_sele, text="Todo", command=all_checkpoint)
tg_allcheck.grid(row=2, column=2)

lbl_c = tk.Label(frm_view, text="Escriba numero o intervalo de lineas: 1,2-5,7")
lbl_c.grid(row=0, column=0, columnspan=3)

et_ignore = tk.Entry(frm_view)
et_ignore.grid(row=1, column=0, columnspan=2, sticky="ew")

lines_ignore = tk.Button(frm_view, text="Ocultar", command=ignore_view)
lines_ignore.grid(row=2, column=0)

lines_show = tk.Button(frm_view, text="Recargar", command=render)
lines_show.grid(row=2, column=1)

top_pane_action = tk.Frame(frame_top, bg="yellow")
top_pane_action.pack(side="top", fill="x")
# Botones

replace_button = tk.Button(
    top_pane_action, text="Reemplazar", command=command_replace_line
)
replace_button.grid(row=0, column=0)

delete_button = tk.Button(
    top_pane_action, text="Eliminar", command=lambda: print("Eliminar")
)
delete_button.grid(row=0, column=1)


# Canvas con Scroll
canvas = tk.Canvas(root)
scroll = tk.Scrollbar(root, orient="vertical", command=canvas.yview)
scroll.pack(side="left", fill="y")
canvas.pack(side="left", fill="both", expand=True)
canvas.configure(yscrollcommand=scroll.set)

frame = tk.Frame(canvas)
canvas.create_window((0, 0), window=frame, anchor="nw")

# Configurar el evento para actualizar el lienzo al cambiar el tamaño del marco
frame.bind("<Configure>", on_configure)

root.geometry(f"{width_root}x{height_root}+{x_root}+0")
root.mainloop()
