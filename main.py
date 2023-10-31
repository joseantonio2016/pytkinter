import tkinter as tk
from CustomTag import CustomTag
 
# Example usage

class MainFrame(tk.Frame):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, *kwargs)
        self.ct = CustomTag(self)
        self.ct.pack()
        self.ct.tag_config("match2", foreground="green")
        self.ct.tag_config("match3", foreground="lightblue")
        # This is not the best way, but it works.
        # instead, see: https://stackoverflow.com/a/40618152/14507110
        self.ct.bind("<KeyRelease>", self.highlight_text)

    def highlight_text(self, args):
        self.ct.highlight_pattern(r"\bfunction\b")
        self.ct.highlight_pattern(r"\breturn\b", "match2")
        self.ct.highlight_pattern(r"\bif\b", "match3")
 
 
def main():
    app = tk.Tk()
    app.title("Main Window")
    app.geometry("600x400")
    app.eval("tk::PlaceWindow . center")
    main_frame = MainFrame(app)
    main_frame.pack()
    app.mainloop()
 
 
if __name__ == "__main__":
    main()