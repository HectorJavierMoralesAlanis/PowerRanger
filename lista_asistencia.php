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
$sql = "SELECT nombre, apellido, uid, last_attendance, asistencia FROM alumnos ORDER BY last_attendance DESC";
$result = $conn->query($sql);


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pase de Lista</title>
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
        }
        h2 {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        table {
            transition: all 0.3s;
        }
        table:hover {
            transform: scale(1.01);
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2>Lista de Asistencia</h2>
    <table class="table table-bordered table-hover">
    <thead class="bg-primary text-white">
    <tr>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>UID</th>
        <th>Última Asistencia</th>
        <th>Estado</th>
    </tr>
</thead>
<tbody>
    <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['nombre']; ?></td>
            <td><?php echo $row['apellido']; ?></td>
            <td><?php echo $row['uid']; ?></td>
            <td><?php echo $row['last_attendance']; ?></td>
            <td><?php echo $row['asistencia'] == 0 ? "Presente" : "Ausente"; ?></td>
        </tr>
    <?php endwhile; ?>
</tbody>
    </table>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('table').on('mouseenter', 'tbody tr', function() {
            $(this).css('background-color', '#e8f0fe');
        }).on('mouseleave', 'tbody tr', function() {
            $(this).css('background-color', '');
        });
    });
</script>
</body>
</html>
