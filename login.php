<?php
session_start();

// Connection details
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

    $email = $_POST['email'];
    $password = $_POST['password']; // You should hash and then compare!

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, nombre, apellido, email, password, 'profesor' as tipo, NULL as uid FROM profesores WHERE email=?
    UNION
    SELECT id, nombre, apellido, email, password, 'administrador' as tipo, NULL as uid FROM administradores WHERE email=?
    UNION
    SELECT id, nombre, apellido, email, password, 'alumno' as tipo, CAST(uid AS CHAR) as uid FROM alumnos WHERE email=?");
    
    $stmt->bind_param("sss", $email, $email, $email);

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Usuario encontrado
        $user = $result->fetch_assoc();
        $rol = $user['tipo'];
        $_SESSION['user'] = $user;  // Almacenar toda la información del usuario en la sesión


        // Comprueba el rol y redirige al dashboard correspondiente
        if ($rol == 'administrador') {
            header('Location: dashboard_administrador.php');
        } elseif ($rol == 'profesor') {
            header('Location: dashboard_profesor.php');
        } elseif ($rol == 'alumno') {
            header('Location: dashboard_alumno.php');
        }
    } 
}

$conn->close();
?>

<!-- login.html -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header">
                    Iniciar Sesión
                </div>
                <div class="card-body">
                    <form action="login.php" method="post">
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="text" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Contraseña:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
