import tkinter as tk
from tkinter import messagebox, ttk
def show_selection():
    try:
        # Obtener el ID del primer elemento seleccionado.
        item = treeview.selection()[0]
    except IndexError:
        # Si la tupla está vacía, entonces no hay ningún
        # elemento seleccionado.
        messagebox.showwarning(
            message="Debe seleccionar un elemento.",
            title="No hay selección"
        )
    else:
        # A partir de este ID retornar el texto del elemento.
        text = treeview.item(item, option="text")
        # Mostrarlo en un cuadro de diálogo.
        messagebox.showinfo(message=text, title="Selección")
main_window = tk.Tk()
main_window.title("Vista de árbol en Tkinter")
treeview = ttk.Treeview()
treeview.insert("", tk.END, text="Elemento 1")
treeview.insert("", tk.END, text="Elemento 2")
treeview.pack()
button = ttk.Button(text="Mostrar selección",
                    command=show_selection)
button.pack()
main_window.mainloop()