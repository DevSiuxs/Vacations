<?php
session_start();
header('Content-Type: application/json');

// Verificar que sea admin o editor
if (!in_array($_SESSION['usuario_rol'] ?? '', ['admin', 'editor'])) {
    header("Location: ../index.php");
    exit();
}

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pruebas";

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

    // Usar sentencias preparadas para seguridad
    $stmt = $conn->prepare("SELECT * FROM solicitudes WHERE id = ?");
    $stmt->bind_param("i", $id_solicitud);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $solicitud = $result->fetch_assoc();
    if (!$solicitud) {
        throw new Exception('Solicitud no encontrada');
    }

    if ($accion === 'aprobar') {
        $stmt = $conn->prepare("UPDATE solicitudes SET estado = 'aprobada', fecha_aprobacion = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id_solicitud);
        if (!$stmt->execute()) {
            throw new Exception("Error al aprobar: " . $stmt->error);
        }
        
        echo json_encode(['success' => 'Solicitud aprobada']);
    } else {
        $stmt = $conn->prepare("UPDATE solicitudes SET estado = 'rechazada', fecha_aprobacion = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id_solicitud);
        if (!$stmt->execute()) {
            throw new Exception("Error al rechazar: " . $stmt->error);
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