import tkinter as tk
from idlelib.percolator import Percolator
from idlelib.colorizer import ColorDelegator
main = tk.Tk()
text = tk.Text(main)
text.pack()
Percolator(text).insertfilter(ColorDelegator())
main.mainloop()

""" Personaliza colores
cdg = ColorDelegator(); cdg.tagdefs['COMMENT'] = {'foreground': '#007FFF', 'background': '#333333'}
Percolator(text).insertfilter(cdg)
 """