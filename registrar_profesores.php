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

// Si se envía el formulario, registrar al alumno
if(isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['pin']) && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])) {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $pin = $_POST['pin'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hashing the password
    // Validar que el PIN no esté ya registrado y que no tenga más de 4 dígitos
    $sql_check_pin = "SELECT * FROM profesores WHERE pin='$pin'";
    $result_pin = $conn->query($sql_check_pin);
        if ($result_pin->num_rows > 0) {
            echo "El PIN ya está registrado.<br>";
        } elseif (strlen($pin) > 4) {
            echo "El PIN no puede tener más de 4 dígitos.<br>";
        } else {
            $sql = "INSERT INTO profesores (nombre, apellido, pin, username, email, password) VALUES ('$nombre', '$apellido', '$pin', '$username', '$email', '$password')";
            if ($conn->query($sql) === TRUE) {
                echo "Profesor registrado con éxito.<br>";
            } else {
                echo "Error al registrar al profesor: " . $sql . "<br>" . $conn->error . "<br>";
            }
        }
}

?>

<?php include('nav_admin.php'); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar profesor</title>
    <!-- Incluir Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    Registrar profesor
                </div>
                <div class="card-body">
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="nombre">Nombre:</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo isset($_POST['nombre']) ? $_POST['nombre'] : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="apellido">Apellido:</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo isset($_POST['apellido']) ? $_POST['apellido'] : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="username">Nombre de Usuario:</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Contraseña:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="pin">PIN:</label>
                            <input type="number" class="form-control" id="pin" name="pin" value="<?php echo isset($_POST['pin']) ? $_POST['pin'] : ''; ?>" required minlength="4" maxlength="4" pattern="\d{4}">
                        </div>
                        <button type="submit" class="btn btn-primary">Registrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir Bootstrap JS y Popper.js -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>