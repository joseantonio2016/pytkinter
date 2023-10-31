#from tkinter import *
import pymysql
import sys,os
#import tkinter as tk,Frame,Label
from tkinter import ttk, Tk,Frame,Label,Button,Text,Entry,StringVar
from tkinter import X,LEFT,RIGHT,BOTH,BOTTOM,END
from calendar import month_name
import tkinter.scrolledtext as tkscrolled
#from tkinter.messagebox import showinfo

root = Tk()
root.title("Sugerencias")

screen_w = root.winfo_screenwidth()
screen_h = root.winfo_screenheight()

win_h = 250
y_c = int(screen_h - win_h-40)

root.geometry("350x{}+20+{}".format(win_h,y_c))
root.overrideredirect(True)

try:
	conn = pymysql.connect(host='localhost',
                             user='root',
                             password='josewilka',
                             database='guias_dev',
                             charset='utf8mb4',
                             cursorclass=pymysql.cursors.DictCursor)
except:
	#self.res = '\nError de conexion a la Database\n'
	#conn.close()
	print('\nError de conexion a la Database\n')
	x = input("Deseas seguir [s]: ?")
	if x != 's':
		os._exit(os.EX_OK)


def move_app(e):
	root.geometry(f'+{e.x_root}+{e.y_root}')

def quitter(e):
	#root.quit()
	conn.close()
	root.destroy()

def on_focus_out(e):
    root.quit()


title_bar = Frame(root, bg="darkgreen",relief="raised", bd=0)
title_bar.pack(fill=X)
title_bar.bind("<B1-Motion>",move_app)

title_label=Label(title_bar, text="Mi app",bg="darkgreen",fg="white")
title_label.pack(side=LEFT, pady=4)

close_Label=Label(title_bar,text=" X ",bg="red",fg="white",relief="sunken", bd=1)
close_Label.pack(side=RIGHT,pady=4)
close_Label.bind("<Button-1>",quitter)

contenido = Frame(root,bg="yellow",relief="sunken",bd=1)
contenido.pack(expand=1, fill=BOTH)



#root.bind("<FocusIn>", on_focus_in)
#root.bind("<FocusOut>", on_focus_out)


contexto = 'laravel'
cur = conn.cursor()
query = "SELECT descrip,guia FROM {} WHERE parent = 0".format(contexto)
nr = cur.execute(query)
# query = "SELECT deploy FROM wp_plug_dev"
# cur.execute(query)
re=cur.fetchall()
#self.res=re['deploy']
initra = list()
inifre = list()
inigui = list()
for d in re:
	g = d['descrip']
	h = g.split('>',1)
	initra.append(h[0])
	inifre.append(h[1])
	inigui.append(d['guia'])

selected_month = StringVar()
month_cb = ttk.Combobox(contenido, textvariable=selected_month)
month_cb['values'] = initra
month_cb.pack(fill=X, padx=2, pady=1)
month_cb.current(0)

d_ntry=Entry(contenido, bg = "yellow",fg="blue")
d_ntry.pack(expand=1,fill=X, pady=0)

frm1 = Frame(contenido,bg="yellow",relief="sunken",bd=0)

n_label=Label(frm1, text="",bg="darkgreen",fg="white")
n_label.pack(fill=X,side=LEFT, padx=4)




#print(re)
def month_changed(event):
    """ handle the month changed event """
    i = month_cb.current()
    #d_label['text']=month_cb.get()
    n_label['text']=inifre[i]
    d_ntry.delete(0, END)
    d_ntry.insert(0, month_cb.get())
    txtarea.delete('1.0', END)
    txtarea.insert('1.0', inigui[i])
    cur.close()


def siguientes():
	contexto = 'laravel'
	cur = conn.cursor()
	n = n_label['text']
	query = "SELECT descrip,guia FROM {} WHERE id = {}".format(contexto,n.strip())
	nr = cur.execute(query)
	re=cur.fetchone()
	d = re['descrip']
	h = d.split('>',1)
	d_ntry.delete(0, END)
	d_ntry.insert(0, h[0])
	n_label['text']=h[1]
	txtarea.delete('1.0', END)
	txtarea.insert('1.0',re['guia'])
	cur.close()
    

boton = Button(frm1,text="+", command=siguientes)
boton.pack(side=RIGHT, padx=2)

frm1.pack(fill=X)

txtarea=tkscrolled.ScrolledText(contenido, height = 6,
                width = 25,
                bg = "light yellow",undo=True)
txtarea.pack(expand=1,side=BOTTOM,fill=BOTH, padx=2,pady=2)

month_cb.bind('<<ComboboxSelected>>', month_changed)
cur.close()

root.mainloop()
