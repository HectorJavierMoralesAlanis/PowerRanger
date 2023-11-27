import mysql.connector

mydb = mysql.connector.connect(
  host="localhost",
  user="admin",
  password="0c655592b90f29474c9fb6c04d39c83f1fb2249c93d27be"
)

print(mydb)