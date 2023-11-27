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
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Si se envía el formulario, registrar al alumno
if(isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['uid']) && isset($_POST['pin']) && isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])) {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $uid = $_POST['uid'];
    $pin = $_POST['pin'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hashing the password

    // Validar que el UID no esté ya registrado
    $sql_check_uid = "SELECT * FROM alumnos WHERE uid='$uid'";
    $result_uid = $conn->query($sql_check_uid);
    if ($result_uid->num_rows > 0) {
        echo "El UID ya está registrado.<br>";
    } else {
        // Validar que el PIN no esté ya registrado y que no tenga más de 4 dígitos
        $sql_check_pin = "SELECT * FROM alumnos WHERE pin='$pin'";
        $result_pin = $conn->query($sql_check_pin);
        if ($result_pin->num_rows > 0) {
            echo "El PIN ya está registrado.<br>";
        } elseif (strlen($pin) > 4) {
            echo "El PIN no puede tener más de 4 dígitos.<br>";
        } else {
            $sql = "INSERT INTO alumnos (nombre, apellido, uid, pin, username, email, password) VALUES ('$nombre', '$apellido', '$uid', '$pin', '$username', '$email', '$password')";
            if ($conn->query($sql) === TRUE) {
                echo "Alumno registrado con éxito.<br>";
            } else {
                echo "Error al registrar al alumno: " . $sql . "<br>" . $conn->error . "<br>";
            }
        }
    }

    
// Si se recibe un UID, insertarlo en la base de datos junto con la fecha actual
if(isset($_POST['uid'])) {
    $uid = $_POST['uid'];

    // Verificar si el UID ya existe en la base de datos
    $checkStmt = $conn->prepare("SELECT uid FROM tarjetas WHERE uid = ?");
    $checkStmt->bind_param("s", $uid);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows == 0) {
        $checkStmt->close();

        $stmt = $conn->prepare("INSERT INTO tarjetas (uid, fecha_registro) VALUES (?, NOW())");
        $stmt->bind_param("s", $uid);

        if ($stmt->execute()) {
            echo "UID recibido y almacenado: " . $uid;
        } else {
            echo "Error al almacenar el UID: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "El UID ya existe en la base de datos";
        $checkStmt->close();
    }
}

// Consultar y mostrar todos los UIDs almacenados
$sql = "SELECT uid, fecha_registro FROM tarjetas ORDER BY fecha_registro DESC";
$result = $conn->query($sql);

if ($result->num_rows >= 0) {
    echo "";
} else {
    echo "No hay UIDs almacenados.";
}

}

// Obtener todos los UIDs disponibles
$sql = "SELECT uid FROM tarjetas";
$result = $conn->query($sql);
$uids = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $uids[] = $row["uid"];
    }
}


?>

<?php include('nav_admin.php'); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Alumno</title>
    <!-- Incluir Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    Registrar Alumno
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
                            <input type="number" class="form-control" id="pin" name="pin" value="<?php echo isset($_POST['pin']) ? $_POST['pin'] : ''; ?>"placeholder="Maximo 4 digitos numericos"  required minlength="4" maxlength="4" pattern="\d{4}">
                        </div>
                        <div class="form-group">
    <label for="uid">UID:</label>
    <input type="text" class="form-control" id="uid" name="uid" value="<?php echo isset($_POST['uid']) ? $_POST['uid'] : ''; ?>" placeholder="Pase su tarjeta por el lector para leer UID" required>
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