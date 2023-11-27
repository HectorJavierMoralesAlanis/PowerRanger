<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$alumno_id = $_SESSION['user']['id'];

// Connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid";

$mesActual = isset($_GET['mes']) ? intval($_GET['mes']) : date('m');  // Por defecto es el mes actual

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch grupo del alumno
    $stmtGrupo = $conn->prepare("SELECT g.id, g.nombre_grupo FROM grupos g JOIN alumnos_grupos ag ON g.id = ag.grupo_id WHERE ag.alumno_id = :alumno_id");
    $stmtGrupo->bindParam(':alumno_id', $alumno_id, PDO::PARAM_INT);
    $stmtGrupo->execute();
    $grupo = $stmtGrupo->fetch();

    // Fetch asistencias del alumno para el mes actual
    $stmtAsistencias = $conn->prepare("SELECT DATE(fecha) as fecha, asistio FROM asistencias WHERE uid = :uid AND MONTH(fecha) = :mes AND YEAR(fecha) = YEAR(CURDATE())");
    $stmtAsistencias->bindParam(':uid', $_SESSION['user']['uid']);
    $stmtAsistencias->bindParam(':mes', $mesActual, PDO::PARAM_INT);
    $stmtAsistencias->execute();
    $asistencias = $stmtAsistencias->fetchAll();

    // Convertir asistencias en un formato más manejable
    $asistenciasPorFecha = [];
    foreach ($asistencias as $asistencia) {
        $fecha = $asistencia['fecha'];
        $asistenciasPorFecha[$fecha] = $asistencia['asistio'];
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;

$meses = [
    '10' => 'octubre',
    '11' => 'noviembre',
    '12' => 'diciembre'
];

$fechaActual = new DateTime();  // Fecha actual
$fechaActual->setTime(0, 0, 0);  // Establecer la hora a medianoche para comparar solo fechas

// Calcular el total de asistencias e inasistencias
$totalAsistencias = 0;
$totalInasistencias = 0;

$start = new DateTime("2023-$mesActual-01");
$end = new DateTime("2023-$mesActual-31");
$interval = new DateInterval('P1D');
$period = new DatePeriod($start, $interval, $end);
foreach ($period as $date) {
    if ($date->format('N') < 6 && $date < $fechaActual) {  // Solo días de semana que ya han pasado
        if (!isset($asistenciasPorFecha[$date->format('Y-m-d')])) {
            $totalInasistencias++;  // Sumar 1 por cada día que ya ha pasado y no tiene registro de asistencia
        }
    }
}

foreach ($asistenciasPorFecha as $fecha => $asistio) {
    if ($asistio == 0) {
        $totalAsistencias++;
    } else {
        $totalInasistencias++;  // Sumar 1 por cada registro de inasistencia en la tabla de asistencias
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Alumno</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Mis Asistencias</h1>

    <a href="logout.php">Cerrar sesion</a>
    
    <div class="alert alert-primary" role="alert">
        <?php
        $user = $_SESSION['user'];
        echo "Bienvenido, " . $user['nombre'];
        ?>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Total Asistencias</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $totalAsistencias; ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-danger mb-3">
                <div class="card-header">Total Inasistencias</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $totalInasistencias; ?></h5>
                </div>
            </div>
        </div>
    </div>

    <h3>Asistencia del mes de <?php echo $meses[$mesActual]; ?> - Grupo: <?php echo $grupo['nombre_grupo']; ?></h3>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Asistencia</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $start = new DateTime("2023-$mesActual-01");
            $end = new DateTime("2023-$mesActual-31");
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($start, $interval, $end);
            foreach ($period as $date) {
                if ($date->format('N') < 6) {  // Solo días de semana
                    echo "<tr>";
                    echo "<td>" . $date->format('d-m-Y') . "</td>";
                    if (isset($asistenciasPorFecha[$date->format('Y-m-d')])) {
                        if ($asistenciasPorFecha[$date->format('Y-m-d')] == 0) {
                            echo "<td class='table-success'>Asistió</td>";
                        } else {
                            echo "<td class='table-danger'>No Asistió</td>";
                        }
                    } else {
                        if ($date < $fechaActual) {
                            echo "<td class='table-danger'>No Asistió</td>";
                        } else {
                            echo "<td></td>";  // Celda vacía si no hay registro de asistencia y el día aún no ha pasado o es el día actual
                        }
                    }
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>

    <nav aria-label="Page navigation example">
    <ul class="pagination">
        <?php
        foreach ($meses as $mes => $nombreMes) {
            echo '<li class="page-item ' . ($mesActual == $mes ? 'active' : '') . '"><a class="page-link" href="?mes=' . $mes . '">' . $nombreMes . '</a></li>';
        }
        ?>
    </ul>
    </nav>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
