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

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $alumno_id = $_POST['alumno_id'];
    $grupo_id = $_POST['grupo_id'];

    $stmt = $conn->prepare("INSERT INTO alumnos_grupos (alumno_id, grupo_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $alumno_id, $grupo_id);

    if ($stmt->execute()) {
        echo "Alumno agregado al grupo con éxito!";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Fetch all students and groups for the dropdowns
$alumnos = $conn->query("SELECT id, nombre, apellido FROM alumnos");
$grupos = $conn->query("SELECT id, nombre_grupo FROM grupos");

?>

<?php include('nav_admin.php'); ?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Alumno a Grupo</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Agregar Alumno a Grupo</h2>
    <form action="" method="post">
        <div class="form-group">
            <label for="alumno_id">Alumno:</label>
            <select class="form-control" id="alumno_id" name="alumno_id">
                <?php while($row = $alumnos->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['nombre'] . ' ' . $row['apellido']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="grupo_id">Grupo:</label>
            <select class="form-control" id="grupo_id" name="grupo_id">
                <?php while($row = $grupos->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['nombre_grupo']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Agregar</button>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
