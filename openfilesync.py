import tkinter as tk
from tkinter import messagebox



def show_file_content():
    def on_scroll(*args):
        text_widget.yview(*args)
        line_numbers.yview(*args)

    file_path = entry.get()

    try:
        with open(file_path, 'r') as file:
            content = file.read()

        frame_file = tk.Toplevel(root)
        frame_file.title("File Content")

        text_frame = tk.Frame(frame_file)
        text_frame.pack(fill=tk.BOTH, expand=True)

        scrollbar = tk.Scrollbar(text_frame)
        scrollbar.pack(side=tk.RIGHT, fill=tk.Y)

        line_numbers = tk.Text(text_frame, width=4, padx=2,borderwidth=0, background='light gray',yscrollcommand=scrollbar.set)
        line_numbers.pack(side=tk.LEFT, fill=tk.Y)

        text_widget = tk.Text(text_frame, wrap="none", yscrollcommand=scrollbar.set)
        text_widget.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)

        scrollbar.config(command=on_scroll)

        for i, line in enumerate(content.split('\n'), 1):
            text_widget.insert(tk.END, line + '\n')
            line_numbers.insert(tk.END, f'{i}\n')

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
