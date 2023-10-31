import tkinter as tk

def insertar_boton():
    text_widget.window_create("insert", window=boton)

ventana = tk.Tk()
ventana.title("Ejemplo de window_create")

text_widget = tk.Text(ventana)
text_widget.pack()

boton = tk.Button(text_widget, text="Haz clic aquí")
boton.config(command=insertar_boton)

text_widget.insert("end", "Este es un Text widget. ")

ventana.mainloop()
