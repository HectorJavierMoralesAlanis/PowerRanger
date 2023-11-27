<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$profesor_id = $_SESSION['user']['id'];

// Connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid";

$mesActual = isset($_GET['mes']) ? intval($_GET['mes']) : 10;  // Por defecto es octubre
$grupoSeleccionado = isset($_POST['grupo']) ? intval($_POST['grupo']) : null;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch grupos del profesor
    $stmtGrupos = $conn->prepare("SELECT id, nombre_grupo FROM grupos WHERE profesor_id = :profesor_id");
    $stmtGrupos->bindParam(':profesor_id', $profesor_id, PDO::PARAM_INT);
    $stmtGrupos->execute();
    $grupos = $stmtGrupos->fetchAll();

    // Si no se ha seleccionado un grupo, seleccionar el primer grupo por defecto
    if (!$grupoSeleccionado && count($grupos) > 0) {
        $grupoSeleccionado = $grupos[0]['id'];
    }

    // Fetch solo los alumnos del grupo seleccionado
    $stmtAlumnos = $conn->prepare("SELECT a.id, a.nombre, a.apellido, a.uid FROM alumnos a JOIN alumnos_grupos ag ON a.id = ag.alumno_id WHERE ag.grupo_id = :grupo_id");
    $stmtAlumnos->bindParam(':grupo_id', $grupoSeleccionado, PDO::PARAM_INT);
    $stmtAlumnos->execute();
    $alumnos = $stmtAlumnos->fetchAll();

    // Fetch todas las asistencias del mes actual 2023
    $stmtAsistencias = $conn->prepare("SELECT uid, DATE(fecha) as fecha, asistio FROM asistencias WHERE MONTH(fecha) = :mes AND YEAR(fecha) = 2023");
    $stmtAsistencias->bindParam(':mes', $mesActual, PDO::PARAM_INT);
    $stmtAsistencias->execute();
    $asistencias = $stmtAsistencias->fetchAll();

    // Convertir asistencias en un formato más manejable
    $asistenciasPorAlumno = [];
    foreach ($asistencias as $asistencia) {
        $uid = $asistencia['uid'];
        $fecha = $asistencia['fecha'];
        $asistenciasPorAlumno[$uid][$fecha] = $asistencia['asistio'];
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}


$meses = [
    10 => 'octubre',
    11 => 'noviembre',
    12 => 'diciembre'
];

$fechaActual = new DateTime();  // Fecha actual
$fechaActual->setTime(0, 0, 0);  // Establecer la hora a medianoche para comparar solo fechas

$start = new DateTime("2023-$mesActual-01");
$end = new DateTime("2023-$mesActual-31");
$interval = new DateInterval('P1D');
$period = new DatePeriod($start, $interval, $end);

// Llamar a la función para registrar inasistencias faltantes
registrarInasistenciasFaltantes($alumnos, $period, $conn, $asistenciasPorAlumno, $fechaActual);

$conn = null;

function registrarInasistenciasFaltantes($alumnos, $period, $conn, $asistenciasPorAlumno, $fechaActual) {
    foreach ($alumnos as $alumno) {
        $uid = $alumno['uid'];
        foreach ($period as $date) {
            if ($date->format('N') < 6) {  // Solo días laborables
                $fecha = $date->format('Y-m-d');
                if (!isset($asistenciasPorAlumno[$uid][$fecha]) && $date < $fechaActual) {
                    // Registrar inasistencia en la base de datos
                    $stmt = $conn->prepare("INSERT INTO asistencias (uid, fecha, asistio) VALUES (:uid, :fecha, 1)");
                    $stmt->bindParam(':uid', $uid);
                    $stmt->bindParam(':fecha', $fecha);
                    $stmt->execute();
                }
            }
        }
    }
}


?>

<?php include('nav_profesor.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Dashboard</h1>
    <div class="alert alert-primary" role="alert">
        <?php
        $user = $_SESSION['user'];
        echo "Bienvenido, " . $user['nombre'];
        ?>
    </div>

    <!-- Formulario de selección de grupo -->
    <form method="post" action="">
        <div class="form-group">
            <label for="grupo">Selecciona un grupo:</label>
            <select name="grupo" id="grupo" class="form-control" onchange="this.form.submit()">
                <?php foreach ($grupos as $grupo): ?>
                    <option value="<?php echo $grupo['id']; ?>" <?php if ($grupoSeleccionado == $grupo['id']) echo 'selected'; ?>><?php echo $grupo['nombre_grupo']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
    <h3>Asistencia del mes de <?php echo $meses[$mesActual]; ?></h3>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <?php
                foreach ($period as $date) {
                    if ($date->format('N') < 6) {
                        echo "<th>" . $date->format('d') . "</th>";
                    }
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
foreach ($alumnos as $alumno) {
    echo "<tr>";
    echo "<td>" . $alumno['nombre'] . "</td>";
    foreach ($period as $date) {
        if ($date->format('N') < 6) {
            // Comprobar si el alumno asistió ese día
            if (isset($asistenciasPorAlumno[$alumno['uid']][$date->format('Y-m-d')])) {
                if ($asistenciasPorAlumno[$alumno['uid']][$date->format('Y-m-d')] == 0) {
                    echo "<td style='background-color: green;'>✓</td>";  // Asistencia con fondo verde
                } else {
                    echo "<td style='background-color: red;'>✗</td>";  // Inasistencia con fondo rojo
                }
            } else {
                // Si el día ya ha pasado y no hay registro de asistencia, la celda se llenará de rojo
                if ($date < $fechaActual) {
                    echo "<td style='background-color: red;'></td>";
                } else {
                    echo "<td></td>";  // Celda vacía si no hay registro de asistencia y el día aún no ha pasado o es el día actual
                }
            }
        }
    }
    echo "</tr>";
}

            ?>
        </tbody>
    </table>

    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <?php
            for ($i = 10; $i <= 12; $i++) {
                echo '<li class="page-item ' . ($mesActual == $i ? 'active' : '') . '"><a class="page-link" href="?mes=' . $i . '">' . $meses[$i] . '</a></li>';
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
