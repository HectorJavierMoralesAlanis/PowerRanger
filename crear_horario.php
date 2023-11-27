<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
// Detalles de conexión
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $profesor_id = $_POST['profesor_id'];
        $grupo_id = $_POST['grupo_id'];
        $diasSeleccionados = isset($_POST['dias']) ? $_POST['dias'] : [];

        foreach ($diasSeleccionados as $dia) {
            $hora_inicio = $_POST['inicio_' . $dia];
            $hora_fin = $_POST['fin_' . $dia];

            $stmtInsert = $conn->prepare("INSERT INTO horarios (profesor_id, grupo_id, dia_semana, hora_inicio, hora_fin) VALUES (:profesor_id, :grupo_id, :dia_semana, :hora_inicio, :hora_fin)");
            $stmtInsert->bindParam(':profesor_id', $profesor_id, PDO::PARAM_INT);
            $stmtInsert->bindParam(':grupo_id', $grupo_id, PDO::PARAM_INT);
            $stmtInsert->bindParam(':dia_semana', $dia);
            $stmtInsert->bindParam(':hora_inicio', $hora_inicio);
            $stmtInsert->bindParam(':hora_fin', $hora_fin);
            $stmtInsert->execute();
        }

        echo "<script>alert('Horario guardado con éxito');</script>";
    }

    // Obtener los profesores
    $stmtProfesores = $conn->prepare("SELECT id, nombre, apellido FROM profesores");
    $stmtProfesores->execute();
    $profesores = $stmtProfesores->fetchAll(PDO::FETCH_ASSOC);

    // Obtener los grupos
    $stmtGrupos = $conn->prepare("SELECT id, nombre_grupo FROM grupos");
    $stmtGrupos->execute();
    $grupos = $stmtGrupos->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<?php include('nav_admin.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Horario</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1>Crear Horario</h1>
    <form action="" method="post">
        <div class="form-group">
            <label for="profesor">Selecciona un profesor:</label>
            <select class="form-control" id="profesor" name="profesor_id">
                <?php foreach ($profesores as $profesor): ?>
                    <option value="<?php echo $profesor['id']; ?>"><?php echo $profesor['nombre'] . " " . $profesor['apellido']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="grupo">Selecciona un grupo:</label>
            <select class="form-control" id="grupo" name="grupo_id">
                <?php foreach ($grupos as $grupo): ?>
                    <option value="<?php echo $grupo['id']; ?>"><?php echo $grupo['nombre_grupo']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        foreach ($dias as $dia):
        ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="dias[]" value="<?php echo $dia; ?>" id="<?php echo $dia; ?>">
                <label class="form-check-label" for="<?php echo $dia; ?>">
                    <?php echo $dia; ?>
                </label>
                <div class="form-inline">
                    <label for="inicio_<?php echo $dia; ?>">Desde:</label>
                    <input type="time" class="form-control ml-2" id="inicio_<?php echo $dia; ?>" name="inicio_<?php echo $dia; ?>">
                    <label for="fin_<?php echo $dia; ?>" class="ml-4">Hasta:</label>
                    <input type="time" class="form-control ml-2" id="fin_<?php echo $dia; ?>" name="fin_<?php echo $dia; ?>">
                </div>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary mt-4">Guardar Horario</button>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
