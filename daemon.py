import os
import time
print('getcwd: ', os.getcwd())
print('__file__:   ',__file__)
import subprocess
import mysql.connector

aux = 0
ruta = "/var/www/html/ejemplo1.py"
#time.sleep(60)
while aux == 0:
    time.sleep(1)
    subprocess.run(['python3',ruta])