<?php
session_start();
header('Content-Type: application/json');

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pruebas";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(['error' => "Conexión fallida: " . $conn->connect_error]));
}
if (!isset($_SESSION['usuario_id'])) {
    die(json_encode(['error' => 'No autorizado']));
}

// Obtener datos del POST
$inicio = $_POST['inicio'] ?? null;
$fin = $_POST['fin'] ?? null;
$dias = $_POST['dias'] ?? null;
$id_empleado = $_SESSION['usuario_id'] ?? null;

// Validar datos básicos
if (!$inicio || !$fin || !$dias) {
    die(json_encode(['error' => 'Datos incompletos']));
}

// Validar que los días sean un número positivo
if (!is_numeric($dias) || $dias <= 0) {
    die(json_encode(['error' => 'Número de días no válido']));
}

// Validar que la fecha de inicio sea al menos 15 días después de hoy
$hoy = new DateTime();
$fechaInicioObj = new DateTime($inicio);
$diferencia = $hoy->diff($fechaInicioObj);

if ($diferencia->days < 15 || $fechaInicioObj <= $hoy) {
    die(json_encode(['error' => 'La fecha de inicio debe ser al menos 15 días después de hoy']));
}

// Obtener días disponibles
$result = $conn->query("SELECT 
    (dias_totales - dias_asignados - dias_disfrutados) as disponibles 
    FROM vacaciones WHERE id_empleado = $id_empleado");
    
if ($result->num_rows === 0) {
    die(json_encode(['error' => 'No se encontraron datos de vacaciones']));
}

$row = $result->fetch_assoc();
$diasDisponibles = $row['disponibles'];

// Validar días disponibles
if ($dias > $diasDisponibles) {
    die(json_encode(['error' => "No tienes suficientes días disponibles. Disponibles: $diasDisponibles"]));
}

// Insertar solicitud con consulta preparada (más segura)
$stmt = $conn->prepare("INSERT INTO solicitudes (id_empleado, fecha_inicio, fecha_fin, dias_solicitados, estado) VALUES (?, ?, ?, ?, 'pendiente')");
$stmt->bind_param("issi", $id_empleado, $inicio, $fin, $dias);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Solicitud guardada', 'id' => $stmt->insert_id]);
} else {
    echo json_encode(['error' => "Error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>