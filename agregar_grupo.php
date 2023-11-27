<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid";

// Crear conexión
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Obtener todos los profesores para el dropdown
$stmt = $conn->prepare("SELECT id, nombre, apellido FROM profesores");
$stmt->execute();
$profesores = $stmt->fetchAll();

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_grupo = $_POST['nombre_grupo'];
    $profesor_id = $_POST['profesor_id'];

    $stmt = $conn->prepare("INSERT INTO grupos (nombre_grupo, profesor_id) VALUES (:nombre_grupo, :profesor_id)");
    $stmt->bindParam(':nombre_grupo', $nombre_grupo);
    $stmt->bindParam(':profesor_id', $profesor_id);
    $stmt->execute();

    echo "Grupo agregado con éxito!";
}
?>

<?php include('nav_admin.php'); ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Grupo</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1>Agregar Grupo</h1>
    <form action="" method="post">
        <div class="form-group">
            <label for="nombre_grupo">Nombre del Grupo:</label>
            <input type="text" class="form-control" id="nombre_grupo" name="nombre_grupo" required>
        </div>
        <div class="form-group">
            <label for="profesor_id">Profesor:</label>
            <select class="form-control" id="profesor_id" name="profesor_id">
                <?php foreach ($profesores as $profesor): ?>
                    <option value="<?php echo $profesor['id']; ?>"><?php echo $profesor['nombre'] . ' ' . $profesor['apellido']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Agregar Grupo</button>
    </form>
</div>
</body>
</html>
