<?php
header('Content-Type: application/json');

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PreisaVacaciones";

try {
    // Crear conexión
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Conexión fallida: " . $conn->connect_error);
    }

    $id_solicitud = $_POST['id'] ?? null;
    $accion = $_POST['accion'] ?? null;

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
        // Actualizar estado a rechazada
        if (!$conn->query("UPDATE solicitudes SET estado = 'rechazada', fecha_aprobacion = NOW() WHERE id = $id_solicitud")) {
            throw new Exception("Error al rechazar: " . $conn->error);
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