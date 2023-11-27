import sqlite3
con = sqlite3.connect("rfid.db")

cur = con.cursor()


res = cur.execute("SELECT * FROM alumnos")
res.fetchone()