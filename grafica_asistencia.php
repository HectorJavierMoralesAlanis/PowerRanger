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

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$grupoSeleccionado = isset($_POST['grupo']) ? $_POST['grupo'] : null;

// Obtener grupos
$sqlGrupos = "SELECT id, nombre_grupo FROM grupos";
$resultGrupos = $conn->query($sqlGrupos);
$grupos = $resultGrupos->fetch_all(MYSQLI_ASSOC);

// Si no se ha seleccionado un grupo, seleccionar el primer grupo por defecto
if (!$grupoSeleccionado && count($grupos) > 0) {
    $grupoSeleccionado = $grupos[0]['id'];
}

// Obtener asistencias e inasistencias por alumno del grupo seleccionado
$sql = "SELECT 
a.nombre,
SUM(CASE WHEN asi.asistio = 0 THEN 1 ELSE 0 END) as asistencias,
SUM(CASE WHEN asi.asistio = 1 THEN 1 ELSE 0 END) as inasistencias
FROM 
alumnos a
LEFT JOIN 
asistencias asi ON a.uid = asi.uid
JOIN 
alumnos_grupos ag ON a.id = ag.alumno_id
WHERE ag.grupo_id = $grupoSeleccionado
GROUP BY 
a.nombre
ORDER BY 
a.nombre;

";
$result = $conn->query($sql);

$datos = [];
while($row = $result->fetch_assoc()) {
    $datos[$row["nombre"]] = ['asistencias' => $row["asistencias"], 'inasistencias' => $row["inasistencias"]];
}

$conn->close();
?>

<?php include('nav_profesor.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfica de Asistencias</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chart-container {
            width: 150px;  /* Ajusta según tus necesidades */
            height: 150px; /* Ajusta según tus necesidades */
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h3>Asistencias por Alumno</h3>

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

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Gráfica</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($datos as $nombre => $asistidos) { ?>
                <tr>
                    <td><?php echo $nombre; ?></td>
                    <td>
                        <div class="chart-container">
                            <canvas id="chart_<?php echo md5($nombre); ?>"></canvas>
                        </div>
                        <script>
var ctx = document.getElementById('chart_<?php echo md5($nombre); ?>').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Asistencias', 'Inasistencias'],
        datasets: [{
            data: [<?php echo $datos[$nombre]['asistencias']; ?>, <?php echo $datos[$nombre]['inasistencias']; ?>],
            backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(255, 99, 132, 0.2)'],
            borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'],
            borderWidth: 1
        }]
    }
});
</script>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
