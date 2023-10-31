import tkinter as tk
from tkinter import ttk

def insertar_objeto_en_treeview(objeto):
    valores = [objeto["nombre"], objeto["edad"], objeto["activo"]]
    tree.insert("", "end", values=valores)

# Crear una ventana de Tkinter
ventana = tk.Tk()
ventana.title("Ejemplo de Treeview con Objetos")

# Crear un Treeview con columnas
tree = ttk.Treeview(ventana, columns=("Nombre", "Edad", "Activo"))
tree.heading("#1", text="Nombre")
tree.heading("#2", text="Edad")
tree.heading("#3", text="Activo")
tree.pack()

# Ejemplo de un objeto representado como diccionario
objeto1 = {"nombre": "Ejemplo1", "edad": 30, "activo": True}
objeto2 = {"nombre": "Ejemplo2", "edad": 25, "activo": False}

# Insertar objetos en el Treeview
insertar_objeto_en_treeview(objeto1)
insertar_objeto_en_treeview(objeto2)

ventana.mainloop()
