import tkinter as tk
from tkinter import messagebox

def show_file_content():
    file_path = entry.get()

    try:
        with open(file_path, 'r') as file:
            content = file.readlines()

        frame_file = tk.Toplevel(root)
        frame_file.title("File Content")

        text_widget = tk.Text(frame_file, wrap="none")
        text_widget.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)

        for i, line in enumerate(content, 1):
            text_widget.insert(tk.END, str(i) + "    " + line)

    except FileNotFoundError:
        messagebox.showerror("Error", "Archivo no encontrado")

root = tk.Tk()
root.title("Visor de Archivo")

frame = tk.Frame(root)
frame.pack(padx=20, pady=20)

label = tk.Label(frame, text="Ruta del archivo:")
label.pack()

entry = tk.Entry(frame, width=50)
entry.pack()

button = tk.Button(frame, text="Ver Archivo", command=show_file_content)
button.pack()

root.mainloop()
