import sqlite3

conexion = sqlite3.connect('rfid.db')
cursor = conexion.cursor()

# Recuperamos los registros de la tabla de usuarios
cursor.execute("SELECT * FROM alumnos")

# Mostrar el cursos a ver que hay ?
print(cursor)

# Recorremos el primer registro con el método fetchone, devuelve una tupla
usuario = cursor.fetchone()
print(usuario)

conexion.close()