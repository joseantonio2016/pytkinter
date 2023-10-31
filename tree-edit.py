import tkinter as tk
from tkinter import ttk

def editar_celda(event):
    item = tree.selection()
    columna = tree.identify_column(event.x)
    columna = int(columna.split("#")[-1]) - 1
    if item:
        item = item[0]
        valor_anterior = tree.item(item, "values")[columna]

        # Crear un Entry para la edición
        entry = tk.Entry(tree, justify="center")
        entry.insert(0, valor_anterior)
        entry.bind("<Return>", lambda e: guardar_cambios(item, columna, entry.get()))

        # Colocar el Entry en la celda seleccionada
        tree.window_create(item, column=columna, window=entry)
        entry.focus_set()

def guardar_cambios(item, columna, nuevo_valor):
    tree.item(item, values=tree.item(item, "values")[:columna] + (nuevo_valor,) + tree.item(item, "values")[columna+1:])

root = tk.Tk()
root.title("Ejemplo de Treeview Editable")

tree = ttk.Treeview(root, columns=("Columna1", "Columna2"))
tree.heading("#1", text="Columna 1")
tree.heading("#2", text="Columna 2")

tree.insert("", "end", values=("Dato1", "Dato2"))
tree.insert("", "end", values=("Dato3", "Dato4"))

tree.bind("<Double-1>", editar_celda)
tree.pack()

root.mainloop()
