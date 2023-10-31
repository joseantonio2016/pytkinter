import tkinter as tk
from tkinter import Menu, messagebox, Text, filedialog
from tkinter import ttk
import json

def nuevo():
    # Lógica para la acción "Nuevo"
    pass

def guardar():
    # Lógica para la acción "Guardar"
    pass

# Función para mostrar la ventana "About"
def show_about():
    messagebox.showinfo("About", "Esta es una aplicación de ejemplo con Tkinter")

# Función para abrir archivos y agregar sus nombres a la tabla
def abrir_archivo():
    archivo = filedialog.askopenfilename(filetypes=[("Archivos", "*.*")])
    if archivo:
        nombre_archivo = archivo.split("/")[-1]
        tree.insert("", "end", values=(nombre_archivo,))

# Función para eliminar una fila de la tabla
def eliminar_fila():
    seleccion = tree.selection()
    for item in seleccion:
        tree.delete(item)
        
# Función para agregar texto al widget Text
def agregar_texto():
    texto = input_centro.get()
    text_widget.insert("end", texto + "\n")  # Agrega el texto al final y agrega una nueva línea

# Función para cargar datos desde un archivo JSON
def cargar_desde_json():
    try:
        with open('datos.json', 'r') as archivo:
            datos = json.load(archivo)
            for dato in datos:
                tree.insert("", "end", values=dato)
    except FileNotFoundError:
        print("El archivo JSON no existe.")

# Función para guardar datos en un archivo JSON
def guardar_a_json():
    datos = []
    for item in tree.get_children():
        datos.append(tree.item(item)['values'])
    with open('datos.json', 'w') as archivo:
        json.dump(datos, archivo, indent=2)

def toggle_panel():
    global panel_opened
    if panel_opened:
        close_panel()
        panel_opened = False
    else:
        open_panel()
        panel_opened = True

def open_panel():
    paneLeft.pack(side="left", fill="both", expand=True)
    for i in range(0, 200, 5):  # Anima el panel desde 0 hasta su ancho deseado
        ventana.after(i, paneLeft.lift)  # Aumenta la opacidad gradualmente

def close_panel():
    for i in range(195, -1, -5):  # Anima el panel desde su ancho hasta 0
        ventana.after(200 - i, paneLeft.lower)  # Disminuye la opacidad gradualmente
    ventana.after(200, paneLeft.pack_forget)  # Oculta el panel después de la animación

""" def toggle_panel():
    if paneLeft.winfo_viewable():
        paneLeft.pack_forget()
    else:
        paneLeft.pack(side="left", fill="both", expand=True) """
# Crear la ventana principal
ventana = tk.Tk()
ventana.title("Mi Aplicación")
ventana.geometry("600x500")

# Menú Inicio
menu_bar = Menu(ventana)
# Menú "Inicio"
inicio_menu = Menu(menu_bar, tearoff=0)
inicio_menu.add_command(label="Archivo", command=None)  # Agrega un submenú "Archivo" que estará vacío
inicio_menu.add_command(label="Editar", command=None)
menu_bar.add_cascade(label="Inicio", menu=inicio_menu)

# Submenú "Archivo"
archivo_menu = Menu(inicio_menu, tearoff=0)
archivo_menu.add_command(label="Nuevo", command=nuevo)
archivo_menu.add_command(label="Guardar", command=guardar)
inicio_menu.add_cascade(label="Archivo", menu=archivo_menu)

# Menú "Editar"
menu_bar.add_command(label="Editar", command=None)

# Menú "Relacionar"
menu_bar.add_command(label="Relacionar", command=None)

# Menú "Configuración"
menu_bar.add_command(label="Configuración", command=None)


menu_bar.add_command(label="About", command=show_about)
ventana.config(menu=menu_bar)

# Crear un botón para ocultar/abrir el panel
toggle_button = tk.Button(ventana, text="Ocultar/abrir panel", command=toggle_panel)
toggle_button.pack(side="top")
# Dividir en la barra derecha e izquierda
paneLeft = tk.Frame(ventana, width=150, bg="#3366FF")
paneLeft.pack(side="left", fill="both", expand=True)
# Coloca contenido en el panel
label = tk.Label(paneLeft, text="Contenido del panel")
label.pack(pady=20)

barra_derecha = tk.Frame(ventana, bg="lightgray")
barra_derecha.pack(side="right", fill="both", expand=True)

# Fila de botones en la parte inferior
fila_botones = tk.Frame(ventana, height=50, bg="lightblue")
fila_botones.pack(side="bottom", fill="x")

# Centro con un botón, un input y un textarea
centro = tk.Frame(ventana, bg="white")
centro.pack(expand=True, fill="both")

boton_centro = tk.Button(centro, text="Botón en el centro",command=agregar_texto)
boton_centro.pack()

input_centro = tk.Entry(centro)
input_centro.pack()

# Crear un widget Text con algunas propiedades personalizadas
text_widget = tk.Text(centro, height=7, width=30, wrap="word", font=("Arial", 9))
text_widget.pack()
text_widget.insert("end", "Este es un ejemplo de Text Widget en Tkinter.\n")
#text_widget.config(state="disabled")  # Hacer el widget de solo lectura

# Botón para abrir archivos
abrir_btn = tk.Button(centro, text="Abrir Archivo", command=abrir_archivo)
abrir_btn.pack(pady=10)

# Botones para cargar y guardar datos en JSON
boton_cargar = tk.Button(centro, text="Cargar desde JSON", command=cargar_desde_json)
boton_cargar.pack()

boton_guardar = tk.Button(centro, text="Guardar a JSON", command=guardar_a_json)
boton_guardar.pack()

# Tabla (Treeview) para mostrar los nombres de los archivos
tree = ttk.Treeview(centro, columns=("Archivo", "Editar", "Eliminar"))
tree.heading("Archivo", text="Archivo")
tree.heading("Editar", text="Editar")
tree.heading("Eliminar", text="Eliminar")
tree.column("#0", stretch=tk.NO, minwidth=0, width=0)
tree.column("Archivo", anchor=tk.W, width=200)
tree.column("Editar", anchor=tk.W, width=50)
tree.column("Eliminar", anchor=tk.W, width=50)
tree.pack()

panel_opened = False

ventana.mainloop()

