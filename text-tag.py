import tkinter as tk

root = tk.Tk()
root.title("Begueradj")
text = tk.Text(root)
# Insert some text
text.insert(tk.INSERT, "Security ")
text.insert(tk.END, " Pentesting ")
text.insert(tk.END, "Hacking ")
text.insert(tk.END, "Coding")
text.pack()
# Create some tags
text.tag_add("one", "1.0", "1.7")
text.tag_add("two", "1.10", "1.20")
text.tag_add("three", "1.21", "1.28")
text.tag_add("four", "1.29", "1.36")
#Configure the tags
text.tag_config("one", background="yellow", foreground="blue")
text.tag_config("two", background="black", foreground="green")
text.tag_config("three", background="blue", foreground="yellow")
text.tag_config("four", background="red", foreground="black")
#Start the program
root.mainloop()