<?php

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

$conn->close();

?>
