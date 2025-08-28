<?php
session_start();
if (!isset($_SESSION['empleado_id'])) {
    header("Location: ../login.php");
    exit();
}
header('Content-Type: application/json');

// Configuración de la base de datos
require_once '../db_config.php'; // Archivo con configuración de BD

try {
    // Crear conexión
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Conexión fallida: " . $conn->connect_error);
    }

    $id_solicitud = $_POST['id'] ?? null;
    $accion = $_POST['accion'] ?? null;
    $motivo = $_POST['motivo'] ?? '';

    if (!$id_solicitud || !$accion) {
        throw new Exception('Datos incompletos');
    }

    // Obtener los datos de la solicitud
    $result = $conn->query("SELECT * FROM solicitudes WHERE id = $id_solicitud");
    if (!$result) {
        throw new Exception("Error en consulta: " . $conn->error);
    }
    
    $solicitud = $result->fetch_assoc();
    if (!$solicitud) {
        throw new Exception('Solicitud no encontrada');
    }

    if ($accion === 'aprobar') {
        // Actualizar estado de la solicitud (sin tocar días disfrutados)
        if (!$conn->query("UPDATE solicitudes SET estado = 'aprobada', fecha_aprobacion = NOW() WHERE id = $id_solicitud")) {
            throw new Exception("Error al aprobar: " . $conn->error);
        }
        
        echo json_encode(['success' => 'Solicitud aprobada']);
    } else {
        // Validar que se haya proporcionado un motivo para el rechazo
        if (empty($motivo)) {
            throw new Exception('Debe proporcionar un motivo para el rechazo');
        }
        
        // Actualizar estado a rechazada
        if (!$conn->query("UPDATE solicitudes SET estado = 'rechazada', fecha_aprobacion = NOW() WHERE id = $id_solicitud")) {
            throw new Exception("Error al rechazar: " . $conn->error);
        }
        
        // Guardar el motivo del rechazo en la nueva tabla
        $motivo_escapado = $conn->real_escape_string($motivo);
        if (!$conn->query("INSERT INTO rechazos_vacaciones (id_solicitud, motivo) VALUES ($id_solicitud, '$motivo_escapado')")) {
            throw new Exception("Error al guardar motivo: " . $conn->error);
        }
        
        echo json_encode(['success' => 'Solicitud rechazada']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>