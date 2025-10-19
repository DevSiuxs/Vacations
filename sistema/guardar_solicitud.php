<?php
session_start();
if (!isset($_SESSION['empleado_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../db_config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Conexión fallida: ' . $conn->connect_error]));
}

$fecha_inicio = $_POST['inicio'];
$fecha_fin = $_POST['fin'];
$dias_solicitados = intval($_POST['dias']);
$id_empleado = $_SESSION['empleado_id'];

// SOLUCIÓN TEMPORAL: Insertar sin la columna 'puesto'
$sql = "INSERT INTO solicitudes (id_empleado, fecha_inicio, fecha_fin, dias_solicitados, estado) 
        VALUES (?, ?, ?, ?, 'pendiente')";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Error al preparar la consulta: ' . $conn->error]);
    exit();
}

$stmt->bind_param('issi', $id_empleado, $fecha_inicio, $fecha_fin, $dias_solicitados);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Solicitud guardada correctamente']);
} else {
    echo json_encode(['error' => 'Error al guardar la solicitud: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>