
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

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['eliminar_asistencia'])) {
    $asi_id = $_POST['eliminar_asistencia'];
    $sql = "DELETE FROM asistencias WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $asi_id);
    if (!$stmt->execute()) {
        echo "Error al eliminar: " . $stmt->error;
    } else {
        echo "Asistencia eliminada con éxito.";
    }
    $stmt->close();
} 

if (isset($_POST['actualizar_asistencias'])) {
    foreach ($_POST['asistencia'] as $asi_id => $estado) {
        if ($estado == "Asistencia") {
            $sql = "UPDATE asistencias SET asistio=0 WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $asi_id);
        } else {
            $sql = "UPDATE asistencias SET asistio=1 WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $asi_id);
        }

        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$sql = "SELECT 
    asi.id AS asi_id,
    asi.uid,
    al.nombre,
    al.apellido,
    CASE 
        WHEN asi.fecha IS NULL THEN 'Ausente' 
        ELSE DATE_FORMAT(asi.fecha, '%Y-%m-%d %H:%i:%s') 
    END AS fecha_asistencia,
    asi.asistio
FROM asistencias asi
LEFT JOIN alumnos al ON asi.uid = al.uid
ORDER BY asi.fecha DESC;
";

$result = $conn->query($sql);
?>

<?php include('nav_profesor.php'); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencias</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Registro de Asistencias</h1>
    
    <form action="" method="post"> <!-- Inicio del formulario de actualización -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>UID</th>
                    <th>Fecha de Asistencia</th>
                    <th>Asistencia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                    echo "<td>" . (isset($row['nombre']) ? $row['nombre'] : 'Desconocido') . "</td>";  // Mostramos el nombre o 'Desconocido' si es NULL
                    echo "<td>" . (isset($row['apellido']) ? $row['apellido'] : 'Desconocido') . "</td>";  // Mostramos el apellido o 'Desconocido' si es NULL
                    echo "<td>" . $row['uid'] . "</td>";  // Mostramos el UID
                    echo "<td>" . $row['fecha_asistencia'] . "</td>";  // Mostramos la fecha de asistencia
                    echo "<td>";
                    echo '<select name="asistencia[' . $row['asi_id'] . ']">';
                    echo '<option value="Asistencia"' . ($row['asistio'] == 0 ? ' selected' : '') . '>Asistencia</option>';
                    echo '<option value="Inasistencia"' . ($row['asistio'] == 1 ? ' selected' : '') . '>Inasistencia</option>';
                    echo '</select>';
                    echo "</td>";
                    echo "<td>";
                    echo '<button type="button" class="btn btn-danger" onclick="submitDeleteForm(' . $row['asi_id'] . ')">Eliminar</button>'; // Botón para eliminar
                    echo "</td>";
                    echo "</tr>";
                }
            }
            ?>
               </tbody>
        </table>
        <button type="submit" name="actualizar_asistencias" class="btn btn-primary">Actualizar Asistencias</button>
    </form> <!-- Fin del formulario de actualización -->
</div>

<script>
    function submitDeleteForm(asi_id) {
        let form = document.createElement('form');
        form.method = 'post';
        form.action = '';
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'eliminar_asistencia';
        input.value = asi_id;
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>