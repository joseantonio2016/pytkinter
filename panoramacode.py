import tkinter as tk
from tkinter import ttk
from tkinter import filedialog

def agregar_conjunto():
    conjunto = tk.Frame(panel, borderwidth=1, relief="sunken")
    conjunto.pack(padx=5, pady=5, fill='x')
    
    # Entry y Botón para seleccionar archivo
    entry_archivo = ttk.Entry(conjunto, width=30)
    entry_archivo.pack(side='left', padx=5)
    
    def seleccionar_archivo():
        filename = filedialog.askopenfilename(filetypes=[("All Files", "*.*")])
        entry_archivo.delete(0, 'end')
        entry_archivo.insert(0, filename)
    
    btn_seleccionar_archivo = ttk.Button(conjunto, text="Seleccionar archivo", command=seleccionar_archivo)
    btn_seleccionar_archivo.pack(side='left', padx=5)
    
    # Entrys para número de líneas
    entry_linea_inicio = ttk.Entry(conjunto, width=5)
    entry_linea_inicio.pack(side='left', padx=5)
    
    entry_linea_fin = ttk.Entry(conjunto, width=5)
    entry_linea_fin.pack(side='left', padx=5)
    
    # Botones para reemplazar y agregar contenido
    def reemplazar_contenido():
        # Aquí puedes implementar la lógica para reemplazar el contenido del archivo entre las líneas indicadas
        pass
    
    btn_reemplazar = ttk.Button(conjunto, text="Reemplazar", command=reemplazar_contenido)
    btn_reemplazar.pack(side='left', padx=5)
    
    def agregar_contenido():
        # Aquí puedes implementar la lógica para agregar contenido al final del archivo
        pass
    
    btn_agregar = ttk.Button(conjunto, text="Agregar", command=agregar_contenido)
    btn_agregar.pack(side='left', padx=5)
    
    # Botón para eliminar este conjunto
    def eliminar_conjunto():
        conjunto.destroy()
    
    btn_eliminar = ttk.Button(conjunto, text="Eliminar", command=eliminar_conjunto)
    btn_eliminar.pack(side='right', padx=5)

root = tk.Tk()
root.title("Agregar conjuntos de widgets")

# Panel vertical para los conjuntos
panel = ttk.Frame(root)
panel.pack(padx=10, pady=10, fill='x')

# Botón para agregar conjunto
btn_agregar_conjunto = ttk.Button(root, text="Agregar conjunto", command=agregar_conjunto)
btn_agregar_conjunto.pack()

root.mainloop()
