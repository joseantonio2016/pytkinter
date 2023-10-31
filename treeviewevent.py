import tkinter as tk
from tkinter import ttk
class Application(ttk.Frame):
    
    def __init__(self, main_window):
        super().__init__(main_window)
        main_window.title("Vista de árbol en Tkinter")
        self.treeview = ttk.Treeview(self)
        # Crear una nueva etiqueta.
        self.treeview.tag_bind("mytag", "<<TreeviewSelect>>",
                                self.item_selected)
        self.treeview.tag_bind("mytag", "<<TreeviewOpen>>",
                                self.item_opened)
        self.treeview.tag_bind("mytag", "<<TreeviewClose>>",
                                self.item_closed)
        # Añadir dos elementos indicando la etiqueta anterior para
        # que respondan a los eventos.
        item = self.treeview.insert("", tk.END, text="Elemento 1",
                                    tags=("mytag",))
        self.treeview.insert(item, tk.END, text="Subelemento 1",
                                tags=("mytag",))
        self.treeview.pack()
        self.pack()
    
    def item_selected(self, event):
        """Item seleccionado."""
        print("Seleccionado.")
    
    def item_opened(self, event):
        """Item abierto."""
        print("Abierto.")
    
    def item_closed(self, event):
        """Item cerrado."""
        print("Cerrado.")
main_window = tk.Tk()
app = Application(main_window)
app.mainloop()