import tkinter as tk
from tkinter import messagebox

def show_file_content():
    file_path = entry.get()

    try:
        with open(file_path, 'r') as file:
            content = file.read()

        frame_file = tk.Toplevel(root)
        frame_file.title("File Content")

        text_widget = tk.Text(frame_file, wrap="none")
        text_widget.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)

        scroll_bar = tk.Scrollbar(frame_file, command=text_widget.yview)
        scroll_bar.pack(side=tk.RIGHT, fill=tk.Y)
        text_widget['yscrollcommand'] = scroll_bar.set

        line_numbers = tk.Text(frame_file, width=4, padx=2, borderwidth=0, background='light gray')
        line_numbers.pack(side=tk.LEFT, fill=tk.Y)

        for i, line in enumerate(content.split('\n'), 1):
            text_widget.insert(tk.END, line + '\n')
            line_numbers.insert(tk.END, f'{i}\n')

        menu_bar = tk.Menu(frame_file)
        menu_bar.add_command(label="About", command=show_about)
        frame_file.config(menu=menu_bar)

    except FileNotFoundError:
        messagebox.showerror("Error", "Archivo no encontrado")

def show_about():
    messagebox.showinfo("About", "Esta es una ventana de ejemplo con información sobre el archivo.")

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
