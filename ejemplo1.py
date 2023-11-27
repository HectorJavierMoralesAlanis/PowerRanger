import sqlite3

# Connect to the SQLite database
con = sqlite3.connect("rfid.db")
cur = con.cursor()

try:
    # Check if the "alumnos" table exists
    cur.execute("SELECT name FROM sqlite_master WHERE type='table' AND name='alumnos';")
    table_exists = cur.fetchone()

    if table_exists:
        # If the table exists, fetch a record from it
        cur.execute("SELECT * FROM alumnos")
        result = cur.fetchone()

        if result:
            print(result)
        else:
            print("Table 'alumnos' is empty.")
    else:
        print("Table 'alumnos' does not exist.")

except sqlite3.Error as e:
    print(f"SQLite error: {e}")

finally:
    # Close the cursor and connection
    cur.close()
    con.close()
