<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_POST['uid'])) {
    $uid = $_POST['uid'];
    checkUID($uid, $conn);
} elseif(isset($_POST['pin'])) {
    $pin = $_POST['pin'];
    checkPIN($pin, $conn);
} else {
    echo "No se recibió ningún dato";
}

function checkUID($uid, $conn) {
    $stmt_check = $conn->prepare("SELECT id FROM alumnos WHERE uid = ?");
    $stmt_check->bind_param("s", $uid);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        registerAttendance($uid, $conn, null);
    } else {
        echo "UID Invalido";
    }

    $stmt_check->close();
}

function checkPIN($pin, $conn) {
    $stmt_check = $conn->prepare("SELECT uid FROM alumnos WHERE pin = ?");
    $stmt_check->bind_param("s", $pin);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        registerAttendance($data['uid'], $conn, $pin);
    } else {
        echo "PIN Incorrecto";
    }

    $stmt_check->close();
}

function registerAttendance($uid, $conn, $pin) {
    $stmt = $conn->prepare("INSERT INTO asistencias (uid, pin, fecha, asistio) VALUES (?, ?, NOW(), 0)");
    $stmt->bind_param("ss", $uid, $pin);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Asistencia registrada.";
    } else {
        echo "Error al registrar la asistencia.";
    }

    $stmt->close();
}

$conn->close();

?>
