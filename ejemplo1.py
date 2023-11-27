import mysql.connector

# Replace these values with your own database connection details
db_config = {
    # Replace these values with your MySQL server credentials
    'host': 'local',
    'user': 'admin',
    'password': '20c655592b90f29474c9fb6c04d39c83f1fb2249c93d27be',
    'database': 'rfid'
}

try:
    # Establish a connection to the MySQL server
    connection = mysql.connector.connect(**db_config)

    # Create a cursor object to interact with the database
    cursor = connection.cursor()

    # Example SELECT query
    query = "SELECT * FROM your_table_name"
    cursor.execute(query)

    # Fetch all rows from the result set
    result = cursor.fetchall()

    # Print the results
    for row in result:
        print(row)

except mysql.connector.Error as err:
    print(f"MySQL Error: {err}")

finally:
    # Close the cursor and connection
    if 'connection' in locals() and connection.is_connected():
        cursor.close()
        connection.close()
        print("MySQL connection is closed.")
