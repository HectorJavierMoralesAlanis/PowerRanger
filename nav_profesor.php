<!-- nav.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Simple navigation bar styling */
        .navbar {
            background-color: #0180C8;
            overflow: hidden;
        }

        .navbar a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }

        .navbar a:hover {
            background-color: #0583CB;
            color: black;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="dashboard_profesor.php">Dashboard</a>
        <a href="editar_pase_lista.php">Editar pase de lista</a>
        <a href="grafica_asistencia.php">Grafica asistencia </a>
        <a href="grupos_profesor.php">Consultar los grupos de un profesor</a>
        <a href="consultar_horario.php">Consultar el horario</a>
        <a href="logout.php">Cerrar sesion</a>
    </div>
</body>
</html>
