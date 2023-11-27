import mysql.connector

# Replace these values with your MySQL server credentials
host = "localhost"
user = "admin"
password = "20c655592b90f29474c9fb6c04d39c83f1fb2249c93d27be"
database = "rfid"

# Establish a connection to the MySQL server
try:
    connection = mysql.connector.connect(
        host=host,
        user=user,
        password=password,
        database=database
    )

    if connection.is_connected():
        print("Connected to the MySQL database")

        # Perform database operations here

         #Example SELECT query
        query = "SELECT * FROM alumnos"
        cursor.execute(query)

        # Fetch all rows from the result set
        result = cursor.fetchall()

        # Print the results
        for row in result:
            print(row)
except mysql.connector.Error as e:
    print(f"Error connecting to MySQL: {e}")

finally:
    # Close the database connection when done
    if 'connection' in locals():
        connection.close()
        print("Connection closed")
