<?php
header('Content-Type: application/json');

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PreisaVacaciones";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(['error' => "Conexión fallida: " . $conn->connect_error]));
}

$id_solicitud = $_POST['id'] ?? null;
$accion = $_POST['accion'] ?? null; // 'aprobar' o 'rechazar'

if (!$id_solicitud || !$accion) {
    die(json_encode(['error' => 'Datos incompletos']));
}

// Obtener los datos de la solicitud
$solicitud = $conn->query("SELECT * FROM solicitudes WHERE id = $id_solicitud")->fetch_assoc();

if (!$solicitud) {
    die(json_encode(['error' => 'Solicitud no encontrada']));
}

if ($accion === 'aprobar') {
    // Actualizar estado de la solicitud
    $conn->query("UPDATE solicitudes SET estado = 'aprobada' WHERE id = $id_solicitud");
    
    // Actualizar días disfrutados en la tabla vacaciones
    $conn->query("UPDATE vacaciones 
                 SET dias_disfrutados = dias_disfrutados + {$solicitud['dias_solicitados']} 
                 WHERE id_empleado = {$solicitud['id_empleado']}");
    
    echo json_encode(['success' => 'Solicitud aprobada y días actualizados']);
} else {
    // Solo actualizar estado a rechazada
    $conn->query("UPDATE solicitudes SET estado = 'rechazada' WHERE id = $id_solicitud");
    echo json_encode(['success' => 'Solicitud rechazada']);
}

$conn->close();
?>