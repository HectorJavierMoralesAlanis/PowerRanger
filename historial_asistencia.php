<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Connection details
$servername = "localhost";
$username = "admin";
$password = "20c655592b90f29474c9fb6c04d39c83f1fb2249c93d27be";
$dbname = "rfid";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obtener la lista de todos los alumnos
$sql_alumnos = "SELECT id, nombre, apellido FROM alumnos";
$result_alumnos = $conn->query($sql_alumnos);

// Obtener todas las fechas de asistencia en el rango deseado (por ejemplo, la última semana)
$sql_fechas = "SELECT DISTINCT fecha FROM asistencias WHERE fecha BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE() ORDER BY fecha";
$result_fechas = $conn->query($sql_fechas);
$fechas = [];
while ($row = $result_fechas->fetch_assoc()) {
    $fechas[] = $row['fecha'];
}

// Obtener todas las asistencias en el rango deseado
$sql_asistencias = "SELECT * FROM asistencias WHERE fecha BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()";
$result_asistencias = $conn->query($sql_asistencias);
$asistencias = [];
while ($row = $result_asistencias->fetch_assoc()) {
    $asistencias[$row['alumno_id']][$row['fecha']] = $row['asistencia'];
}

?>

<!-- Aquí comienza tu código HTML -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencias Mensuales</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            overflow-x: auto;
        }
        table {
            min-width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .attendance-box {
            display: inline-block;
            width: 25px;
            height: 25px;
            line-height: 25px;
            background-color: #e9ecef;
            color: #333;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2>Asistencias Mensuales</h2>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <?php foreach ($fechas as $fecha): ?>
                    <th><?php echo $fecha; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($alumno = $result_alumnos->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $alumno['nombre'] . " " . $alumno['apellido']; ?></td>
                    <?php foreach ($fechas as $fecha): ?>
                        <td>
                            <?php 
                            if (isset($asistencias[$alumno['id']][$fecha])) {
                                echo "<div class='attendance-box'>" . $asistencias[$alumno['id']][$fecha] . "</div>";
                            } else {
                                echo "<div class='attendance-box'>0</div>";  // Si no hay un registro de asistencia para ese día, se asume que estuvo ausente
                            }
                            ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>