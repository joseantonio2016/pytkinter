import tkinter as tk
from tkinter import ttk
from tkinter import filedialog,messagebox
import json
from tkinter import font
import visorfileln
from CustomTag import CustomTag
import os

def add_wgroup_file(t_file='',t_ini='',t_fin=''):
    
    def highlight_text(args=[]):
        text_widget.highlight_pattern(r"\b(function|return|class|as)\b","dev")
        text_widget.highlight_pattern(r"\b(if|else|for|in|async|await|yield)\b","dev2")
        text_widget.highlight_pattern(r"get|set|id|value|query|object|request|client|array|log\(","wc")
        if extf != '.py':
            text_widget.highlight_pattern(r"\b(const|var|let|new|extends|namespace|public|private|protected|using|void)\b","dev-nopy")
            text_widget.highlight_pattern(r"document|this|static","w-nopy")
        else:
            text_widget.highlight_pattern(r"\b(def|from)\b","dev-py")

        if extf == '.php':
            text_widget.highlight_pattern(r"\b(use|include)\b","dev-php")
        else:
            text_widget.highlight_pattern(r"\b(import|export)\b","dev-nophp")

        text_widget.highlight_pattern(r"[=,\.\?:\+><\*$-]", "assign")
        text_widget.highlight_pattern(r"[\[\](){}]", "group")
        text_widget.highlight_pattern(r"(?<![\\])['\"](.*?)(?<![\\])['\"]", "string")
        text_widget.highlight_pattern(r"(#|\/\/)(.*)", "lcomment")
        text_widget.highlight_pattern(r"/\*[\s\S]*?\*/", "bcomment")
        #text_widget.highlight_pattern(r"(?:'|\")(.*?[^\\])(?:'|\")", "string")
    
    def reemplazar_contenido(pathfile, l_ini, l_fin, text_widget):
        try:
            with open(pathfile, 'r', encoding='utf-8') as archivo:
                lineas = archivo.readlines()

            txt = text_widget.get("1.0", "end-1c")
            nuevas_lineas = txt.split('\n')
            l_ini = int(l_ini)
            l_fin = int(l_fin)
            # Reemplaza el rango de líneas con el nuevo texto del widget Text
            linea_init = lineas[:l_ini-1]
            linea_last = lineas[l_fin:]
            #lineas[l_ini-1:l_fin] = nuevas_lineas
            print(len(nuevas_lineas))

            with open(pathfile, 'w', encoding='utf-8') as archivo:
                archivo.writelines(linea_init)
                archivo.writelines(linea + '\n' for linea in nuevas_lineas)
                archivo.writelines(linea_last)
                m_showInfo("Reemplazo de líneas exitoso","")
        except Exception as e:
            m_showInfo(f"Error al reemplazar las líneas: {e}","Error")

    def agregar_contenido(valor_archivo, linea_inicio, linea_fin):
        # Aquí puedes utilizar los valores capturados para agregar contenido al final del archivo
        print(f"Archivo: {valor_archivo}")
        print(f"Línea inicio: {linea_inicio}")
        print(f"Línea fin: {linea_fin}")
    
    def show_content(pathfile, l_ini, l_fin, text_widget):
        try:
            with open(pathfile, 'r', encoding='utf-8') as file:
                lineas = file.readlines()

                if l_fin is None or int(l_fin) > len(lineas):
                    l_fin = len(lineas)

                lineas_a_mostrar = lineas[int(l_ini)- 1:int(l_fin)]

                textof = ''.join(lineas_a_mostrar)
                text_widget.delete(1.0, tk.END)
                text_widget.insert(tk.END, textof)
                highlight_text()
        except UnicodeDecodeError as e:
            txt = f"Error al decodificar el archivo: {e}"
            m_showInfo(txt,"Seleccione un archivo plano")
    
    def seleccionar_archivo():
        filename = filedialog.askopenfilename(filetypes=[("All Files", "*.*")])
        entry_archivo.delete(0, 'end')
        entry_archivo.insert(0, filename)
        if filename:
            lb_filename.config(text=filename.split("/")[-1])
    
    
    
    def on_validate(P):
        if P.isdigit() or P == "":
            return True
        else:
            return False
    
    def m_showInfo(msg,head):
        messagebox.showinfo(message=msg, title=head)
    
    def scroll_x(*args):
        text_widget.xview(*args)

    conjunto = tk.Frame(panel, borderwidth=1, relief="sunken", width=450, bg="lightblue")
    #conjunto.pack(padx=5, pady=5, fill='x')
    conjunto.pack(fill='y')

    validate_cmd = conjunto.register(on_validate)

    mando1 = tk.Frame(conjunto)
    mando1.pack(padx=5, pady=5, fill='x')

    lb_filename = tk.Label(mando1, text='Archivo:')
    lb_filename.pack(side='left', padx=1)

    entry_archivo = ttk.Entry(mando1, width=30)
    entry_archivo.pack(side='left', padx=5)
    entry_archivo.insert(0, t_file)
    namefile, extf = os.path.splitext(t_file)


    btn_seleccionar_archivo = ttk.Button(mando1, text="Ver", command=lambda path=entry_archivo: visorfileln.show(root,path.get()),style='estiloBoton.TButton')
    btn_seleccionar_archivo.pack(side='left', padx=5)

    btn_eliminar = ttk.Button(mando1, text="Eliminar", style='estiloBoton.TButton')
    btn_eliminar.pack(side='right', padx=5)
    btn_eliminar.config(command=conjunto.destroy)

    mando2 = tk.Frame(conjunto)
    mando2.pack(padx=5, pady=5, fill='x')

    label = tk.Label(mando2, text="Linea:")
    label.pack(side='left', padx=5)
    
    entry_linea_inicio = ttk.Entry(mando2, width=5, validate="key", validatecommand=(validate_cmd, '%P'))
    entry_linea_inicio.pack(side='left', padx=5)
    entry_linea_inicio.insert(0, t_ini)
    
    entry_linea_fin = ttk.Entry(mando2, width=5, validate="key", validatecommand=(validate_cmd, '%P'))
    entry_linea_fin.pack(side='left', padx=5)
    entry_linea_fin.insert(0, t_fin)

    btn_showref = ttk.Button(mando2, text="Visualizar",style='estiloBoton.TButton')
    btn_showref.pack(side='left', padx=5)
    btn_showref.config(command=lambda file=entry_archivo, ini=entry_linea_inicio, fin=entry_linea_fin: show_content(file.get(), ini.get(), fin.get(),text_widget))
    
    btn_reemplazar = ttk.Button(mando2, text="Reemplazar",style='estiloBoton.TButton')
    btn_reemplazar.pack(side='left', padx=5)
    btn_reemplazar.config(command=lambda archivo=entry_archivo, inicio=entry_linea_inicio, fin=entry_linea_fin: reemplazar_contenido(archivo.get(), inicio.get(), fin.get(),text_widget))
    
    btn_agregar = ttk.Button(mando2, text="Agregar", style='estiloBoton.TButton')
    btn_agregar.pack(side='left', padx=5)
    btn_agregar.config(command=lambda archivo=entry_archivo, inicio=entry_linea_inicio, fin=entry_linea_fin: agregar_contenido(archivo.get(), inicio.get(), fin.get()))

    text_widget = CustomTag(conjunto, height=12, width=50, wrap="none", bg="gray20", fg="white", font=("Courier", 9))
    text_widget.pack(expand=True, padx=5, pady=5, fill="both")  # Efecto visual del Text en la parte inferior
    # Barra de desplazamiento horizontal
    scrollbar_x = tk.Scrollbar(conjunto, orient="horizontal", command=scroll_x)
    scrollbar_x.pack(fill="x")

    # Vincular la barra de desplazamiento al widget Text
    text_widget.config(xscrollcommand=scrollbar_x.set, spacing1=2)
    text_widget.tag_config("dev", foreground="#007FFF")
    text_widget.tag_config("dev2", foreground="mediumorchid3")
    text_widget.tag_config("dev-nopy", foreground="#007FFF")
    text_widget.tag_config("wc", foreground="navajowhite3")
    text_widget.tag_config("w-nopy", foreground="navajowhite3")
    text_widget.tag_config("dev-py", foreground="#007FFF")
    text_widget.tag_config("dev-php", foreground="#007FFF")
    text_widget.tag_config("dev-nophp", foreground="#007FFF")
    text_widget.tag_config("assign", foreground="coral")
    text_widget.tag_config("group", foreground="darkgoldenrod1")
    text_widget.tag_config("lcomment", foreground="antiquewhite4")
    text_widget.tag_config("bcomment", foreground="antiquewhite4")
    text_widget.tag_config("string", foreground="seagreen4")

    text_widget.bind("<KeyRelease>", highlight_text)
    # Agrega las referencias a la lista de Entry
    lista_entry.append((entry_archivo,entry_linea_inicio,entry_linea_fin))

def save_file():
    file_path = filedialog.asksaveasfilename(defaultextension=".txt", filetypes=[("Archivos de texto", "*.txt"), ("Todos los archivos", "*.*")])
    if file_path:
        with open(file_path, 'w') as file:
            file.write("Contenido a guardar en el archivo")

def save_crossref(lista_entry):
    # Obtiene los valores de los Entry y los almacena en un diccionario
    datos = {}
    data = []
    for entry in lista_entry:
        fjs = entry[0].get()  # Se asume que el primer elemento de cada tupla es la etiqueta/clave
        if fjs=='':
            continue
        ini = entry[1].get()  # Se asume que el segundo elemento de cada tupla es el Entry
        end = entry[2].get()
        data.append({'file':fjs,'ini':ini,'end':end})
    
    file_path = filedialog.asksaveasfilename(defaultextension=".json", filetypes=[("json", "*.json")])
    if file_path:
        # Guarda los datos en un archivo JSON
        with open(file_path, "w") as file:
            json.dump(data, file)

def open_crossref():
    ruta_archivo = filedialog.askopenfilename(filetypes=[("Archivos JSON", "*_cr.json")])
    if ruta_archivo:
        with open(ruta_archivo, 'r') as file:
            datos = json.load(file)
            for obj in datos:
                try:
                    add_wgroup_file(obj['file'],obj['ini'],obj['end'])
                except:
                    messagebox.showwarning(
        "Error de clave", "No tiene el formato json esperado."
    )

def open_file():
    pathfile = filedialog.askopenfilename(filetypes=[("Archivos", "*.*")])
    if pathfile:
        add_wgroup_file(pathfile)

def view_file():
    pathfile = filedialog.askopenfilename(filetypes=[("Archivos", "*.*")])
    if pathfile:
        visorfileln.show(root,pathfile)

def on_configure(event):
    # Actualiza el área desplegable del lienzo cuando el marco cambia de tamaño
    canvas.configure(scrollregion=canvas.bbox("all"))

def cambiar_fuente_botones():
    style = ttk.Style()
    style.configure('estiloBoton.TButton', font=('Verdana', 8))  # Cambia 'Arial' y '12' por la fuente y tamaño deseados

root = tk.Tk()
root.title("Referencia cruzada de archivos")

width_root = 500
height_root = root.winfo_screenheight()  # Utiliza toda la altura de la pantalla
x_root = root.winfo_screenwidth() - width_root  # Posiciona la ventana a la derecha

# Define la nueva fuente
nueva_fuente = font.Font(family="Helvetica", size=8)  # Cambia "Helvetica" por la fuente que desees y 10 por el tamaño deseado
root.option_add("*Font", nueva_fuente)
# Aplica la nueva fuente a la configuración global
cambiar_fuente_botones()
# Lista para almacenar las referencias a los Entry creados dinámicamente
lista_entry = []

def about():
    messagebox.showinfo(
        "About", "Este es un programa para referenciar archivos."
    )

# Menu
menu = tk.Menu(root)
root.config(menu=menu)
ini_menu = tk.Menu(menu)
menu.add_cascade(label="Referencia", menu=ini_menu)
ini_menu.add_command(label="Abrir", command=open_crossref)
ini_menu.add_command(label="Guardar", command=lambda: save_crossref(lista_entry))
ini_menu.add_separator()
ini_menu.add_command(label="Salir", command=root.quit)
file_menu = tk.Menu(menu)
menu.add_cascade(label="Archivo", menu=file_menu)
file_menu.add_command(label="Agregar", command=open_file)
file_menu.add_command(label="Contenido", command=view_file)

# Crear un lienzo (canvas) con barras de desplazamiento
canvas = tk.Canvas(root)
canvas.pack(side="left", fill="both", expand=True)

# Añadir barras de desplazamiento al lienzo
scrollbar = tk.Scrollbar(root, command=canvas.yview)
scrollbar.pack(side="right", fill="y")
canvas.configure(yscrollcommand=scrollbar.set)

# Crear un marco (frame) en el lienzo para contener los widgets
panel = tk.Frame(canvas)
canvas.create_window((0, 0), window=panel, anchor="nw")

# Configurar el evento para actualizar el lienzo al cambiar el tamaño del marco
panel.bind("<Configure>", on_configure)

#panel = ttk.Frame(root)
#panel.pack(padx=10, pady=10, fill='x')
#panel.option_add("*Font", nueva_fuente)

#estilos_botones = [s for s in root.tk.call('ttk::style', 'names') if s.endswith('TButton')]

# Aplica la nueva fuente a todos los estilos de botones
""" for estilo in estilos_botones:
    root.tk.call('ttk::style', 'configure', estilo, font=nueva_fuente) """
root.geometry(f"{width_root}x{height_root}+{x_root}+0")
root.mainloop()
