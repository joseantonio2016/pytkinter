import tkinter as tk

root = tk.Tk()
root.title("Ejemplo de uso de sticky en grid")

# Crear dos Frames
frame1 = tk.Frame(root, bg="cyan", width=100, height=100)
frame2 = tk.Frame(root, bg="yellow", width=100, height=100)

frame1.grid(row=0, column=0)
frame2.grid(row=0, column=1)

# Agregar etiquetas (Labels) a los Frames
label1 = tk.Label(frame1, text="Frame 1 - Label 1", bg="red", fg="white")
label2 = tk.Label(frame2, text="Frame 2 - Label 2", bg="blue", fg="white")

label1.grid(row=0, column=0, sticky="nsew")  # Expansión en todas las direcciones
label2.grid(row=0, column=0, sticky="n")    # Expansión hacia el norte

root.mainloop()
