<?php
session_start();
if (!isset($_SESSION['empleado_id']) || ($_SESSION['empleado_rol'] !== 'admin' && $_SESSION['empleado_rol'] !== 'editor')) {
    echo json_encode(['success' => false, 'error' => 'No tiene permisos para realizar esta acción']);
    exit();
}

// Habilitar reporte de errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Incluir configuración de base de datos
require_once '../db_config.php';

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => "Conexión fallida: " . $conn->connect_error]);
    exit();
}

// Obtener datos JSON
$json = file_get_contents('php://input');
$input = json_decode($json, true);

// Verificar si hay error en el JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'error' => 'JSON inválido: ' . json_last_error_msg()]);
    exit();
}

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

// Verificar si existe el registro en la tabla vacaciones
$check_sql = "SELECT id FROM vacaciones WHERE id_empleado = ?";
$check_stmt = $conn->prepare($check_sql);
if (!$check_stmt) {
    echo json_encode(['success' => false, 'error' => 'Error en la preparación de la consulta: ' . $conn->error]);
    exit();
}

$check_stmt->bind_param("i", $id);
if (!$check_stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Error al ejecutar la consulta: ' . $check_stmt->error]);
    $check_stmt->close();
    exit();
}

$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    // Si no existe, insertar en lugar de actualizar
    $sql = "INSERT INTO vacaciones (id_empleado, dias_totales, dias_asignados, dias_disfrutados) 
            VALUES (?, ?, ?, ?)";
} else {
    // Si existe, actualizar
    $sql = "UPDATE vacaciones SET 
            dias_totales = ?,
            dias_asignados = ?,
            dias_disfrutados = ?
            WHERE id_empleado = ?";
}

$check_stmt->close();

// Preparar la consulta
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Error en la preparación de la consulta: ' . $conn->error]);
    exit();
}

// Ejecutar la consulta
if ($check_result->num_rows === 0) {
    // Para INSERT
    $stmt->bind_param("iiii", $id, $dias_totales, $dias_asignados, $dias_disfrutados);
} else {
    // Para UPDATE
    $stmt->bind_param("iiii", $dias_totales, $dias_asignados, $dias_disfrutados, $id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al ejecutar la consulta: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>