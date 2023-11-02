import tkinter as tk

root = tk.Tk()
root.title("Usando grid en Tkinter")

# Crear algunos widgets (Labels)
label1 = tk.Label(root, text="Widget 1")
label2 = tk.Label(root, text="Widget 2")
label3 = tk.Label(root, text="Widget 3")

# Usar grid para posicionar los widgets
label1.grid(row=0, column=0)
label2.grid(row=0, column=1)
label3.grid(row=1, column=0, columnspan=2)  # Ocupa dos columnas

root.mainloop()
