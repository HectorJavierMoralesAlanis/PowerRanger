<?php
// Suponiendo que ya has iniciado sesión y tienes el ID del profesor en la sesión
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$profesor_id = isset($user['id']) ? $user['id'] : null;

if (!$profesor_id) {
    die("No se encontró el ID del profesor en la sesión.");
}


// Connection details
$servername = "localhost";
$username = "admin";
$password = "20c655592b90f29474c9fb6c04d39c83f1fb2249c93d27be";
$dbname = "rfid";
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT 
        g.nombre_grupo,
        a.nombre,
        a.apellido,
        COALESCE(SUM(CASE WHEN asis.asistio = 0 THEN 1 ELSE 0 END), 0) AS asistencias,
        COALESCE(SUM(CASE WHEN asis.asistio = 1 THEN 1 ELSE 0 END), 0) AS inasistencias
    FROM grupos g
    LEFT JOIN alumnos_grupos ag ON g.id = ag.grupo_id
    LEFT JOIN alumnos a ON ag.alumno_id = a.id
    LEFT JOIN asistencias asis ON a.uid = asis.uid
    WHERE g.profesor_id = :profesor_id
    GROUP BY g.nombre_grupo, a.nombre, a.apellido
");
    $stmt->bindParam(':profesor_id', $profesor_id, PDO::PARAM_INT);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organizar los resultados en un formato más manejable
    $grupos = [];
    foreach ($results as $result) {
        $grupoNombre = $result['nombre_grupo'];
        if (!isset($grupos[$grupoNombre])) {
            $grupos[$grupoNombre] = [];
        }
        if ($result['nombre'] && $result['apellido']) {
            $grupos[$grupoNombre][] = $result['nombre'] . ' ' . $result['apellido'];
        }
    }

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
function obtenerPorcentajeAsistencias($grupoNombre, $conn) {
    try {
        $stmt = $conn->prepare("SELECT asistio, COUNT(*) as count 
                                FROM asistencias a
                                JOIN alumnos al ON a.uid = al.uid
                                JOIN alumnos_grupos ag ON al.id = ag.alumno_id
                                JOIN grupos g ON ag.grupo_id = g.id
                                WHERE g.nombre_grupo = :grupoNombre
                                GROUP BY asistio");
        $stmt->bindParam(':grupoNombre', $grupoNombre);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $asistencias = 0;
        $inasistencias = 1;
        foreach ($results as $result) {
            if ($result['asistio'] == 0) {  // 0 es asistencia
                $asistencias = $result['count'];
            } else {  // 1 es inasistencia
                $inasistencias = $result['count'];
            }
        }
        $total = $asistencias + $inasistencias;
        if ($total == 0) return ['asistencias' => 0, 'inasistencias' => 100];  // Evitar división por cero
        return [
            'asistencias' => ($asistencias / $total) * 100,
            'inasistencias' => ($inasistencias / $total) * 100
        ];
    } catch(PDOException $e) {
        return [
            'asistencias' => 0,
            'inasistencias' => 1
        ];
    }
}



?>


<?php include('nav_profesor.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupos del Profesor</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Grupos del Profesor</h1>
    <div class="row">
        <?php foreach ($grupos as $nombreGrupo => $alumnos): ?>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <?php echo htmlspecialchars($nombreGrupo); ?>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($alumnos as $alumno): ?>
                            <li class="list-group-item"><?php echo htmlspecialchars($alumno); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="card-body">
                        <canvas id="chart-<?php echo htmlspecialchars($nombreGrupo); ?>"></canvas>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
   $(document).ready(function() {
    <?php foreach ($grupos as $nombreGrupo => $alumnos): ?>
        var ctx = document.getElementById('chart-<?php echo htmlspecialchars($nombreGrupo); ?>').getContext('2d');
        var porcentajes = <?php echo json_encode(obtenerPorcentajeAsistencias($nombreGrupo, $conn)); ?>;
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Asistencias', 'Inasistencias'],
                datasets: [{
                    data: [porcentajes.asistencias, porcentajes.inasistencias],
                    backgroundColor: ['green', 'red']
                }]
            },
            options: {
                responsive: true,
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Porcentaje de Asistencias e Inasistencias'
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                },
                maintainAspectRatio: true,
                aspectRatio: 1
            }
        });
    <?php endforeach; ?>
});

</script>
</body>
</html>