<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$profesor_id = $_SESSION['user']['id'];

// Connection details
$servername = "localhost";
$username = "admin";
$password = "20c655592b90f29474c9fb6c04d39c83f1fb2249c93d27be";
$dbname = "rfid";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener los horarios del profesor junto con el nombre del grupo
    $stmt = $conn->prepare("SELECT h.dia_semana, h.hora_inicio, h.hora_fin, g.nombre_grupo FROM horarios h INNER JOIN grupos g ON h.grupo_id = g.id WHERE h.profesor_id = :profesor_id ORDER BY FIELD(h.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes')");
    $stmt->bindParam(':profesor_id', $profesor_id, PDO::PARAM_INT);
    $stmt->execute();

    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<?php include('nav_profesor.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Horario</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Mi Horario</h1>
    <div class="mb-3">
        <label>Filtrar por día:</label>
        <select class="form-control" id="filterDay">
            <option value="all">Todos</option>
            <option value="Lunes">Lunes</option>
            <option value="Martes">Martes</option>
            <option value="Miércoles">Miércoles</option>
            <option value="Jueves">Jueves</option>
            <option value="Viernes">Viernes</option>
        </select>
    </div>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Día</th>
                <th>Grupo</th>
                <th>Hora de Inicio</th>
                <th>Hora de Fin</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($horarios as $horario): ?>
                <tr data-day="<?php echo $horario['dia_semana']; ?>">
                    <td><?php echo $horario['dia_semana']; ?></td>
                    <td><?php echo $horario['nombre_grupo']; ?></td>
                    <td><?php echo $horario['hora_inicio']; ?></td>
                    <td><?php echo $horario['hora_fin']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#filterDay').on('change', function() {
            let selectedDay = $(this).val();
            if (selectedDay === "all") {
                $('tbody tr').show();
            } else {
                $('tbody tr').hide();
                $('tbody tr[data-day="' + selectedDay + '"]').show();
            }
        });
    });
</script>
</body>
</html>