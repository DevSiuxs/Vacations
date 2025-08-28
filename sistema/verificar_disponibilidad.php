<?php
session_start();
header('Content-Type: application/json');

// Configuración de la base de datos
require_once '../db_config.php'; // Archivo con configuración de BD

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    echo json_encode(['disponible' => true, 'error' => "Conexión fallida"]); // En caso de error, permitir
    exit();
}

// Obtener datos del POST
$inicio = $_POST['inicio'] ?? null;
$fin = $_POST['fin'] ?? null;

if (!$inicio || !$fin) {
    echo json_encode(['disponible' => true]); // Datos incompletos, permitir
    exit();
}


// En guardar_solicitud.php, modificar la consulta de verificación de solapamiento:
$stmt = $conn->prepare("SELECT s.id, e.nombre 
    FROM solicitudes s
    JOIN empleados e ON s.id_empleado = e.id
    WHERE (s.estado = 'aprobada' OR s.estado = 'pendiente')
    AND (
        (? BETWEEN s.fecha_inicio AND s.fecha_fin) OR 
        (? BETWEEN s.fecha_inicio AND s.fecha_fin) OR 
        (s.fecha_inicio BETWEEN ? AND ?) OR 
        (s.fecha_fin BETWEEN ? AND ?)
    )
    LIMIT 1");
$stmt->bind_param("ssssss", $inicio, $fin, $inicio, $fin, $inicio, $fin);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $conflicto = $result->fetch_assoc();
    echo json_encode(['disponible' => false, 'mensaje' => "Estas fechas ya están solicitadas por {$conflicto['nombre']}"]);
} else {
    echo json_encode(['disponible' => true]);
}

$stmt->close();
$conn->close();
?>