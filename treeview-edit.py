import tkinter as tk
from tkinter import ttk

# Función para comenzar la edición de una celda
def iniciar_edicion(event):
    global celda_editable
    item = tree.selection()
    if not item:
        return
    item = item[0]  # Obtener el elemento seleccionado
    columna = tree.identify_column(event.x)  # Identificar la columna haciendo clic
    col = int(columna[1:])-1
    celda_editable = item, col
    valor_actual = tree.item(item, "values")[col]
    cuadro_entrada.delete(0, "end")
    cuadro_entrada.insert(0, valor_actual)
    cuadro_entrada.grid(row=tree.bbox(item, column=col)[0], column=col, sticky="nsew")
	
# Función para aplicar los cambios de edición
def aplicar_edicion(event):
    print ('ya paso focus')
    item, columna= celda_editable
    nuevo_valor = cuadro_entrada.get()
    tree.item(item, values=(tree.item(item, "values")[0], nuevo_valor))
    cuadro_entrada.grid_remove()

# Crear la ventana
ventana = tk.Tk()
ventana.title("Treeview Editable")

# Crear un Treeview con dos columnas
tree = ttk.Treeview(ventana, columns=("Nombre", "Valor"), show="headings")
tree.heading("Nombre", text="Nombre")
tree.heading("Valor", text="Valor")
tree.insert("", "end", values=("Dato1", "Valor1"))
tree.insert("", "end", values=("Dato2", "Valor2"))
tree.pack()

# Cuadro de entrada para la edición
cuadro_entrada = ttk.Entry(tree)

# Configurar eventos para la edición
tree.bind("<ButtonRelease-1>", iniciar_edicion)
cuadro_entrada.bind("<FocusOut>", aplicar_edicion)

ventana.mainloop()

