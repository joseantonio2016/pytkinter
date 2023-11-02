import tkinter as tk
from tkinter import messagebox
from CustomTag import CustomTag
import os


def show(root,file_path=''):
    # Example usage
    def highlight_text(args):
        text_widget.highlight_pattern(r"\b(function|return|class|as)\b","dev")
        text_widget.highlight_pattern(r"\b(if|else|for|in|async|await|yield)\b","dev2")
        text_widget.highlight_pattern(r"get|set|id|value|query|object|request|client|array|log\(","wc")
        if extf != '.py':
            text_widget.highlight_pattern(r"\b(const|var|let|new|extends|namespace|public|private|protected|using|void)\b","dev-nopy")
            text_widget.highlight_pattern(r"document|this|static","w-nopy")
        else:
            text_widget.highlight_pattern(r"\b(def|from)\b","dev-py")

        if extf == '.php':
            text_widget.highlight_pattern(r"\b(use|include)\b","dev-php")
        else:
            text_widget.highlight_pattern(r"\b(import|export)\b","dev-nophp")

        text_widget.highlight_pattern(r"[=,\.\?:\+><\*$-]", "assign")
        text_widget.highlight_pattern(r"[\[\](){}]", "group")
        text_widget.highlight_pattern(r"(?<![\\])['\"](.*?)(?<![\\])['\"]", "string")
        text_widget.highlight_pattern(r"(#|\/\/)(.*)", "lcomment")
        text_widget.highlight_pattern(r"/\*[\s\S]*?\*/", "bcomment")

    def on_scroll(*args):
        text_widget.yview(*args)
        line_numbers.yview(*args)

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

        text_widget = CustomTag(text_frame, wrap="none", yscrollcommand=scrollbar.set, bg="gray20", fg="white", font=("Courier", 9))
        text_widget.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)

        scrollbar.config(command=on_scroll)

        namefile, extf = os.path.splitext(file_path)
        title = os.path.basename(file_path)
        frame_file.title(title)

        text_widget.tag_config("dev", foreground="#007FFF")
        text_widget.tag_config("dev2", foreground="mediumorchid3")
        text_widget.tag_config("dev-nopy", foreground="#007FFF")
        text_widget.tag_config("wc", foreground="navajowhite3")
        text_widget.tag_config("w-nopy", foreground="navajowhite3")
        text_widget.tag_config("dev-py", foreground="#007FFF")
        text_widget.tag_config("dev-php", foreground="#007FFF")
        text_widget.tag_config("dev-nophp", foreground="#007FFF")
        text_widget.tag_config("assign", foreground="coral")
        text_widget.tag_config("group", foreground="darkgoldenrod1")
        text_widget.tag_config("lcomment", foreground="antiquewhite4")
        text_widget.tag_config("bcomment", foreground="antiquewhite4")
        text_widget.tag_config("string", foreground="seagreen4")
        highlight_text('')
        # This is not the best way, but it works.
        # instead, see: https://stackoverflow.com/a/40618152/14507110
        text_widget.bind("<KeyRelease>", highlight_text)
        

        for i, line in enumerate(content.split('\n'), 1):
            text_widget.insert(tk.END, line + '\n')
            line_numbers.insert(tk.END, f'{i}\n')

    except FileNotFoundError:
        messagebox.showerror("Error", "Archivo no encontrado")