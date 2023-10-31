import tkinter as tk
from tkinter import filedialog

def seleccionar_archivo():
    archivo = filedialog.askopenfilename()
    ruta_archivo.set(archivo)
    with open(archivo, 'r') as file:
        lineas = file.readlines()
        for i, linea in enumerate(lineas):
            check_var.append(tk.IntVar(value=0))
            add_line = tk.Checkbutton(frame, variable=check_var[i])
            add_line.grid(row=i, column=0)

            num_l = tk.Entry(frame, width=5)
            num_l.insert(0, str(i + 1))
            num_l.grid(row=i, column=1)

            line_text = tk.Entry(frame)
            line_text.insert(0, linea.strip())
            line_text.grid(row=i, column=2, sticky="ew")

            check_var[i].set(0)
            check_buttons.append(add_line)
            num_entries.append(num_l)
            text_entries.append(line_text)

def command_replace_line():
    for i, check in enumerate(check_buttons):
        if check_var[i].get() == 1:
            with open(ruta_archivo.get(), 'r') as file:
                lines = file.readlines()
            lines[i] = text_entries[i].get() + '\n'
            with open(ruta_archivo.get(), 'w') as file:
                file.writelines(lines)

def on_configure(event):
    # Actualiza el área desplegable del lienzo cuando el marco cambia de tamaño
    canvas.configure(scrollregion=canvas.bbox("all"))

root = tk.Tk()
root.title("ManageLineFile")

# Variables
ruta_archivo = tk.StringVar()
check_var = []
check_buttons = []
num_entries = []
text_entries = []

# Funciones
def about():
    tk.messagebox.showinfo("About", "Este es un programa para manejar líneas en un archivo de texto.")

# Menu
menu = tk.Menu(root)
root.config(menu=menu)
file_menu = tk.Menu(menu)
menu.add_cascade(label="File", menu=file_menu)
file_menu.add_command(label="About", command=about)

# Frame
frame = tk.Frame(root)
frame.pack()

# Botones
seleccionar_button = tk.Button(frame, text="Seleccionar Archivo", command=seleccionar_archivo)
seleccionar_button.grid(row=0, column=0)

replace_button = tk.Button(frame, text="Reemplazar", command=command_replace_line)
replace_button.grid(row=0, column=1)

delete_button = tk.Button(frame, text="Eliminar", command=lambda: print("Eliminar"))
delete_button.grid(row=0, column=2)

# Label
ruta_label = tk.Label(root, textvariable=ruta_archivo)
ruta_label.pack()

# Canvas con Scroll
canvas = tk.Canvas(root)
scroll = tk.Scrollbar(root, orient="vertical", command=canvas.yview)
scroll.pack(side="right", fill="y")
canvas.pack(side="left", fill="both", expand=True)
canvas.configure(yscrollcommand=scroll.set)

frame = tk.Frame(canvas)
canvas.create_window((0, 0), window=frame, anchor="nw")

# Configurar el evento para actualizar el lienzo al cambiar el tamaño del marco
frame.bind("<Configure>", on_configure)

root.mainloop()
