<?php
session_start();
require_once '../db_config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['disponible' => false, 'mensaje' => 'Error de conexión']));
}

$fecha_inicio = $_POST['inicio'];
$fecha_fin = $_POST['fin'];
$id_empleado = $_SESSION['empleado_id'];

// Obtener el puesto del empleado actual desde la tabla empleados
$sql_puesto = "SELECT puesto FROM empleados WHERE id = ?";
$stmt_puesto = $conn->prepare($sql_puesto);
$stmt_puesto->bind_param('i', $id_empleado);
$stmt_puesto->execute();
$result_puesto = $stmt_puesto->get_result();

if ($result_puesto->num_rows === 0) {
    echo json_encode(['disponible' => false, 'mensaje' => 'Empleado no encontrado']);
    exit();
}

$empleado_actual = $result_puesto->fetch_assoc();
$puesto_actual = $empleado_actual['puesto'];

// Verificar disponibilidad por puesto (usando JOIN con empleados)
$sql = "SELECT e.nombre, e.puesto 
        FROM solicitudes s 
        JOIN empleados e ON s.id_empleado = e.id 
        WHERE e.puesto = ? 
        AND s.estado IN ('aprobada', 'pendiente')
        AND ((s.fecha_inicio BETWEEN ? AND ?) 
             OR (s.fecha_fin BETWEEN ? AND ?)
             OR (? BETWEEN s.fecha_inicio AND s.fecha_fin)
             OR (? BETWEEN s.fecha_inicio AND s.fecha_fin))";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['disponible' => false, 'mensaje' => 'Error en la consulta']);
    exit();
}

$stmt->bind_param('sssssss', $puesto_actual, $fecha_inicio, $fecha_fin, $fecha_inicio, $fecha_fin, $fecha_inicio, $fecha_fin);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $conflicto = $result->fetch_assoc();
    echo json_encode([
        'disponible' => false, 
        'mensaje' => "Estas fechas están ocupadas por {$conflicto['nombre']}"
    ]);
} else {
    echo json_encode(['disponible' => true]);
}

$stmt->close();
$conn->close();
?>