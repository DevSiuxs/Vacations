<?php
session_start();
if (!isset($_SESSION['empleado_id'])) {
    header("Location: ../login.php");
    exit();
}
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

// Obtener datos del POST
$inicio = $_POST['inicio'] ?? null;
$fin = $_POST['fin'] ?? null;
$dias = $_POST['dias'] ?? null;
$id_empleado = $_SESSION['empleado_id'] ?? null;

// Validar datos básicos
if (!$inicio || !$fin || !$dias) {
    die(json_encode(['' => 'Datos incompletos'])); // ← Cambiado
}

// Validar que los días sean un número positivo
if (!is_numeric($dias)) {  
    die(json_encode(['' => 'Número de días no válido']));
}

// Convertir días a entero
$dias = (int)$dias;
if ($dias <= 0) {
    die(json_encode(['' => 'Debes solicitar al menos 1 día']));
}

// Validar que la fecha de inicio sea al menos 15 días después de hoy
$hoy = new DateTime();
$fechaInicioObj = new DateTime($inicio);
$diferencia = $hoy->diff($fechaInicioObj);

if ($diferencia->days < 15 || $fechaInicioObj <= $hoy) {
    die(json_encode(['' => 'La fecha de inicio debe ser al menos 15 días después de hoy']));
}

// Validar que la fecha fin sea mayor o igual a la fecha inicio
$fechaFinObj = new DateTime($fin);
if ($fechaFinObj < $fechaInicioObj) {
    die(json_encode(['' => 'La fecha de fin no puede ser anterior a la fecha de inicio']));
}

// Verificar si hay solapamiento con otras solicitudes aprobadas O PENDIENTES
$stmt = $conn->prepare("SELECT s.id, e.nombre 
    FROM solicitudes s
    JOIN empleados e ON s.id_empleado = e.id
    WHERE (s.estado = 'aprobada' OR s.estado = 'pendiente')
    AND s.id_empleado != ?  -- Excluir al usuario actual para evitar autoconsulta
    AND (
        (? BETWEEN s.fecha_inicio AND s.fecha_fin) OR 
        (? BETWEEN s.fecha_inicio AND s.fecha_fin) OR 
        (s.fecha_inicio BETWEEN ? AND ?) OR 
        (s.fecha_fin BETWEEN ? AND ?)
    )");
$stmt->bind_param("issssss", $id_empleado, $inicio, $fin, $inicio, $fin, $inicio, $fin);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $empleado_conflicto = $result->fetch_assoc();
    die(json_encode(['error' => "ESTOS DÍAS YA ESTÁN SOLICITADOS POR {$empleado_conflicto['nombre']}, SELECCIONA OTRA FECHA"])); 
}
$stmt->close();

// Obtener días disponibles
$result = $conn->query("SELECT 
    (dias_totales - dias_asignados - dias_disfrutados) as disponibles 
    FROM vacaciones WHERE id_empleado = $id_empleado");
    
if ($result->num_rows === 0) {
    die(json_encode(['' => 'No se encontraron datos de vacaciones']));
}

$row = $result->fetch_assoc();
$diasDisponibles = $row['disponibles'];

// Validar días disponibles
if ($dias > $diasDisponibles) {
    die(json_encode(['' => "No tienes suficientes días disponibles. Disponibles: $diasDisponibles"]));
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