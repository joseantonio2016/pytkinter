import tkinter as tk

def scroll_y(*args):
    print(args)
    text_widget_1.yview_moveto(args[1])
    text_widget_2.yview_moveto(args[1])

root = tk.Tk()
root.title("Sincronización de scroll vertical")

scrollbar = tk.Scrollbar(root)
scrollbar.pack(side=tk.RIGHT, fill=tk.Y)

text_widget_1 = tk.Text(root, yscrollcommand=scrollbar.set)
text_widget_1.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)

text_widget_2 = tk.Text(root, yscrollcommand=scrollbar.set)
text_widget_2.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)

scrollbar.config(command=scroll_y)

content = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."
text_widget_1.insert(tk.END, content)
text_widget_2.insert(tk.END, content)

root.mainloop()
