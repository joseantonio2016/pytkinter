import tkinter as tk
from tkinter import ttk
from tkinter import filedialog

def leer_archivo_php():
    # Mostrar ventana de selección de archivo
    filename = filedialog.askopenfilename(filetypes=[("PHP files", "*.php")])

    # Verificar si se seleccionó un archivo
    if filename:
        with open(filename, "r") as file:
            lineas = file.readlines()
            lineas = [line.strip() for line in lineas]

        # Separar las líneas según las reglas establecidas
        use_include_lines = []
        class_name = ""
        functions = []
        class_block = False

        for line in lineas:
            if line.startswith('use') or line.startswith('include'):
                use_include_lines.append(line)
            elif 'class' in line and not class_name:
                class_name = line.split('class')[-1].split('{')[0].strip()
                class_block = True
            elif 'function' in line and class_block:
                functions.append(line.split('function')[-1].split('{')[0].strip())

        # Crear la interfaz para mostrar la información
        root = tk.Tk()
        root.title("Visor de Archivo PHP")

        label_frame = ttk.LabelFrame(root, text="use/include lines")
        label_frame.pack(padx=10, pady=10, fill='both', expand='yes')
        scrollbar = ttk.Scrollbar(label_frame, orient='vertical')
        scrollbar.pack(side='right', fill='y')

        text_use_include = tk.Text(label_frame,height=5, yscrollcommand=scrollbar.set)
        text_use_include.pack(fill='both', expand=True)
        scrollbar.config(command=text_use_include.yview)

        panel = ttk.Frame(root)
        panel.pack(padx=10, pady=10, fill='both', expand='yes')
        scrollbar_panel = ttk.Scrollbar(panel, orient='vertical')
        scrollbar_panel.pack(side='right', fill='y')

        text = tk.Text(panel, height=12, wrap='word', yscrollcommand=scrollbar_panel.set)
        text.pack(fill='both', expand=True)
        scrollbar_panel.config(command=text.yview)

        # Mostrar las líneas use/include en un LabelFrame
        for line in use_include_lines:
            text_use_include.insert('end', line + '\n')

        # Mostrar el nombre de la clase
        ttk.Label(root, text=f"Nombre de la Clase: {class_name}").pack()

        # Mostrar las funciones dentro de la clase en el widget Text
        text.insert('end', "Funciones:\n")
        for function in functions:
            text.insert('end', f"{function}\n")

        root.mainloop()

# Crear la barra de menú y submenú
root = tk.Tk()
root.title("Lector de Archivos PHP")

menubar = tk.Menu(root)
file_menu = tk.Menu(menubar, tearoff=0)
file_menu.add_command(label="Seleccionar", command=leer_archivo_php)

menubar.add_cascade(label="Archivo", menu=file_menu)
root.config(menu=menubar)

root.mainloop()
