import tkinter as tk
from tkinter import ttk

def scroll_text(event):
    # Mover el Text horizontal y verticalmente según la posición de las barras de desplazamiento
    text.yview(*event)
    text.xview(*event)

root = tk.Tk()
root.title("Ejemplo Scrollbar TTK")

# Crear Scrollbar vertical
scrollbar_y = ttk.Scrollbar(root, orient='vertical')
scrollbar_y.pack(side='right', fill='y')

# Crear Scrollbar horizontal
scrollbar_x = ttk.Scrollbar(root, orient='horizontal')
scrollbar_x.pack(side='bottom', fill='x')

# Crear un widget Text
text = tk.Text(root, wrap='none', yscrollcommand=scrollbar_y.set, xscrollcommand=scrollbar_x.set)
text.pack(expand=True, fill='both')

# Configurar las barras de desplazamiento para controlar el widget Text
scrollbar_y.config(command=text.yview)
scrollbar_x.config(command=text.xview)

# Simular un texto largo para hacer que aparezcan las barras de desplazamiento
long_text = "Línea " * 50  # Crear una cadena larga
for _ in range(50):  # Agregar varias líneas al widget Text
    text.insert('end', long_text + '\n')

root.mainloop()