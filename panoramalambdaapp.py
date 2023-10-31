import tkinter as tk
from tkinter import ttk
from tkinter import filedialog

class Application(ttk.Frame):
    def __init__(self, master=None):
        super().__init__(master)
        self.pack()
        self.create_widgets()

    def reemplazar_contenido(self, archivo, linea_inicio, linea_fin):
        # Aquí puedes utilizar los valores capturados para reemplazar contenido en el archivo
        print(f"Archivo: {archivo}")
        print(f"Línea inicio: {linea_inicio}")
        print(f"Línea fin: {linea_fin}")

    def agregar_contenido(self, archivo, linea_inicio, linea_fin):
        # Aquí puedes utilizar los valores capturados para agregar contenido al final del archivo
        print(f"Archivo: {archivo}")
        print(f"Línea inicio: {linea_inicio}")
        print(f"Línea fin: {linea_fin}")

    def agregar_conjunto(self):
        conjunto = tk.Frame(self, borderwidth=1, relief="sunken")
        conjunto.pack(padx=5, pady=5, fill='x')
        
        entry_archivo = ttk.Entry(conjunto, width=30)
        entry_archivo.pack(side='left', padx=5)
        
        entry_linea_inicio = ttk.Entry(conjunto, width=5)
        entry_linea_inicio.pack(side='left', padx=5)
        
        entry_linea_fin = ttk.Entry(conjunto, width=5)
        entry_linea_fin.pack(side='left', padx=5)
        
        btn_reemplazar = ttk.Button(conjunto, text="Reemplazar")
        btn_reemplazar.pack(side='left', padx=5)
        btn_reemplazar.config(command=lambda archivo=entry_archivo, inicio=entry_linea_inicio, fin=entry_linea_fin: self.reemplazar_contenido(archivo.get(), inicio.get(), fin.get()))
        
        btn_agregar = ttk.Button(conjunto, text="Agregar")
        btn_agregar.pack(side='left', padx=5)
        btn_agregar.config(command=lambda archivo=entry_archivo, inicio=entry_linea_inicio, fin=entry_linea_fin: self.agregar_contenido(archivo.get(), inicio.get(), fin.get()))
        
        btn_eliminar = ttk.Button(conjunto, text="Eliminar")
        btn_eliminar.pack(side='right', padx=5)
        btn_eliminar.config(command=conjunto.destroy)

    def create_widgets(self):
        btn_agregar_conjunto = ttk.Button(self, text="Agregar conjunto", command=self.agregar_conjunto)
        btn_agregar_conjunto.pack(padx=10, pady=10)

root = tk.Tk()
root.title("Agregar conjuntos de widgets")

app = Application(master=root)
app.pack()

root.mainloop()
