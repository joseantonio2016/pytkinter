import tkinter as tk
from CustomTag import CustomTag

root = tk.Tk()

# Example usage
def highlight_text(args):
    text.highlight_pattern(r"\bfunction\b")
    text.highlight_pattern(r"\breturn\b", "match2")
    text.highlight_pattern(r"\bif\b", "match3")

text = CustomTag(root)
text.pack()

text.tag_config("match2", foreground="green")
text.tag_config("match3", foreground="lightblue")

# This is not the best way, but it works.
# instead, see: https://stackoverflow.com/a/40618152/14507110
text.bind("<KeyRelease>", highlight_text)

root.mainloop()