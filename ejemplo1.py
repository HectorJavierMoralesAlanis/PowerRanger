import _mssql

server = 'localhost'
user = 'admin'
password = '20c655592b90f29474c9fb6c04d39c83f1fb2249c93d27be'
database = 'rfid'
conn = _mssql.connect(server, user, password, database)


conn.execute_query('SELECT * FROM pets WHERE name=%s', 'alumnos')

for row in conn:
    print "ID=%d, Name=%s" % (row['id'], row['name'])