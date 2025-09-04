<?php
session_start();
if (!isset($_SESSION['empleado_id']) || ($_SESSION['empleado_rol'] !== 'admin' && $_SESSION['empleado_rol'] !== 'editor')) {
    header("Location: ../login.php");
    exit();
}

header('Content-Type: application/json');

// Configuración de la base de datos
require_once '../db_config.php';

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => "Conexión fallida: " . $conn->connect_error]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// Verificar si los datos están completos
if (!$input || !isset($input['id']) || !isset($input['dias_totales']) || !isset($input['dias_asignados']) || !isset($input['dias_disfrutados'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit();
}

$id = intval($input['id']);
$dias_totales = intval($input['dias_totales']);
$dias_asignados = intval($input['dias_asignados']);
$dias_disfrutados = intval($input['dias_disfrutados']);

// Validar que los valores sean números positivos
if ($dias_totales < 0 || $dias_asignados < 0 || $dias_disfrutados < 0) {
    echo json_encode(['success' => false, 'error' => 'Los valores no pueden ser negativos']);
    exit();
}

// Validar que los días asignados + disfrutados no superen los días totales
if (($dias_asignados + $dias_disfrutados) > $dias_totales) {
    echo json_encode(['success' => false, 'error' => 'La suma de días asignados y disfrutados no puede superar los días totales']);
    exit();
}

// Verificar si el empleado existe
$check_sql = "SELECT id FROM empleados WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Empleado no encontrado']);
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Actualizar datos en la base de datos
$sql = "UPDATE vacaciones SET 
        dias_totales = ?,
        dias_asignados = ?,
        dias_disfrutados = ?
        WHERE id_empleado = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $dias_totales, $dias_asignados, $dias_disfrutados, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$stmt->close();
$conn->close();
?>