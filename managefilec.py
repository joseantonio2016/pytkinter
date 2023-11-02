import tkinter as tk
from tkinter import filedialog, font, messagebox, ttk
import os

class LineaManage:
    def __init__(self, root, idx):
        self.check = tk.BooleanVar()
        self.content_num = tk.StringVar()
        self.content_text = tk.StringVar()

        self.check_button = tk.Checkbutton(root, variable=self.check)
        self.entry_num = tk.Entry(root)
        self.entry_text = tk.Entry(root)

    def grid(self, row):
        self.check_button.grid(row=row, column=0)
        self.entry_num.grid(row=row, column=1)
        self.entry_text.grid(row=row, column=2)

def reload_memory(lineas=[]):
    for i, linea in enumerate(lineas):
            check_var.append(tk.IntVar(value=0))
            add_line = tk.Checkbutton(frame, variable=check_var[i])
            add_line.grid(row=i, column=0)

            num_l = tk.Entry(frame, width=5)
            num_l.insert(0, str(i + 1))
            num_l.grid(row=i, column=1)

            line_text = tk.Entry(frame, width=60)
            line_text.insert(0, linea.strip())
            line_text.grid(row=i, column=2, sticky="ew")

            check_var[i].set(0)
            check_buttons.append(add_line)
            num_entries.append(num_l)
            text_entries.append(line_text)

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
        with open(archivo, "r") as file:
            lineas = file.readlines()
            reload_memory(lineas)
    else:
        messagebox.showwarning('Advertencia','Archivo no encontrado')

def command_replace_line():
    lines=[]
    try:
        with open(et_target.get(), "w") as file:
            for i, check in enumerate(check_buttons):
                if check_var[i].get() == 1:
                    lines.append(text_entries[i].get())
            file.writelines(line + '\n' for line in lines)
            messagebox.showinfo('Reemplazo completado','Se escribio en archivo destino')
    except FileNotFoundError:
        messagebox.showerror('Error al abrir archivo',"No se puede abrir el archivo indicado")


def on_configure(event):
    # Actualiza el área desplegable del lienzo cuando el marco cambia de tamaños
    canvas.configure(scrollregion=canvas.bbox("all"))


def all_check(check=1):
    for var in check_var:
        var.set(check)


def check_by_entry(check=1):
    entrada = entrada_marcar_desde.get()
    elementos = entrada.split(",")
    elementos = [item for item in elementos if item != ""]
    if len(elementos) == 0:
        messagebox.showwarning('Indique numero o rango')
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
lineas = []


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
top_menu.add_command(label="Abrir Sesion", command=about)
top_menu.add_command(label="Guardar Sesion", command=about)
top_menu.add_separator()
top_menu.add_command(label="Abrir Archivo", command=seleccionar_archivo)
top_menu.add_command(label="Desde Referencia", command=about)
top_menu.add_separator()
top_menu.add_command(label="Salir", command=root.quit)
file_menu = tk.Menu(menu)
menu.add_cascade(label="Archivo", menu=file_menu)
file_menu.add_command(label="Ver archivo fuente", command=about)
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

# Agregar pestañas al Notebook con su respectivo contenido
notebook.add(frm_file, text="Archivo")
notebook.add(frm_sele, text="Seleccion")
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

lbl_r = tk.Label(frm_sele, text="Escriba numero o intervalo de lineas: 1,2-5,7")
lbl_r.grid(row=0, column=0, columnspan=3)

entrada_marcar_desde = tk.Entry(frm_sele)
entrada_marcar_desde.grid(row=1, column=0, columnspan=3, sticky="ew")

checking_entry = tk.Button(frm_sele, text="Marcar", command=lambda: check_by_entry(1))
checking_entry.grid(row=2, column=0)

unckecking_entry = tk.Button(
    frm_sele, text="Desmarcar", command=lambda: check_by_entry(0)
)
unckecking_entry.grid(row=2, column=1)

tg_allcheck = tk.Button(frm_sele, text="Todo", command=all_checkpoint)
tg_allcheck.grid(row=2, column=2)

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
