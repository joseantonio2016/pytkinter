import tkinter as tk

root = tk.Tk()
root.title("Ejemplo de uso de pack")

# Crear widgets Label
label1 = tk.Label(root, text="Label 1", bg="red", fg="white")
label2 = tk.Label(root, text="Label 2", bg="blue", fg="white")

# Ejemplo de uso de parámetros en pack
label1.pack(side="top", fill="x", padx=5, pady=5)  # Posicionado arriba, se expande en la dirección X
label2.pack(side="left", fill="y", padx=5, pady=5)  # Posicionado a la izquierda, se expande en la dirección Y

root.mainloop()
