<?php
session_start();

// 1. Verificación de sesión
if (!isset($_SESSION['empleado_id'])) {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Sesión no iniciada']);
    exit();
}

require_once '../db_config.php';

// 2. Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    header("Content-Type: application/json");
    die(json_encode(['error' => 'Conexión fallida: ' . $conn->connect_error]));
}

// 3. Recepción de datos y limpieza
$fecha_inicio = $_POST['inicio'] ?? null;
$fecha_fin = $_POST['fin'] ?? null;
$dias_solicitados = isset($_POST['dias']) ? intval($_POST['dias']) : 0;
$id_empleado = $_SESSION['empleado_id'];

if (!$fecha_inicio || !$fecha_fin || $dias_solicitados <= 0) {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Datos de solicitud incompletos o inválidos']);
    exit();
}

// --- LÓGICA DEL CANDADO DE SEGURIDAD ---

// A. Obtener el derecho total de días del empleado
$sql_total = "SELECT dias_totales FROM vacaciones WHERE id_empleado = $id_empleado";
$res_total = $conn->query($sql_total);
$row_total = $res_total->fetch_assoc();
$DERECHO_TOTAL = intval($row_total['dias_totales'] ?? 0);

// B. Calcular días ya "consumidos" (Aprobados que ya pasaron por el proceso de cierre/disfrutadas)
$sql_gastados = "SELECT IFNULL(SUM(s.dias_solicitados), 0) as gastados
                 FROM solicitudes s
                 INNER JOIN vacaciones_disfrutadas vd ON s.id = vd.id_solicitud
                 WHERE s.id_empleado = $id_empleado";
$res_gastados = $conn->query($sql_gastados);
$YA_GASTADOS = intval($res_gastados->fetch_assoc()['gastados'] ?? 0);

// C. Calcular días "apartados" (Pendientes + Aprobados que aún no son 'disfrutadas')
$sql_en_espera = "SELECT IFNULL(SUM(dias_solicitados), 0) as espera
                  FROM solicitudes
                  WHERE id_empleado = $id_empleado
                  AND estado IN ('pendiente', 'aprobada')
                  AND id NOT IN (SELECT id_solicitud FROM vacaciones_disfrutadas)";
$res_espera = $conn->query($sql_en_espera);
$EN_ESPERA = intval($res_espera->fetch_assoc()['espera'] ?? 0);

// D. CÁLCULO DEL SALDO REAL DISPONIBLE
$SALDO_LIBRE = $DERECHO_TOTAL - ($YA_GASTADOS + $EN_ESPERA);

// E. BLOQUEO ESTRICTO
if ($dias_solicitados > $SALDO_LIBRE) {
    header("Content-Type: application/json");
    echo json_encode([
        'error' => "No tienes días suficientes. Dias Reales: $SALDO_LIBRE (Tienes $EN_ESPERA días en solicitudes pendientes o por disfrutar)."
    ]);
    exit();
}

// --- FIN DEL CANDADO ---

// 4. Preparar la inserción de la nueva solicitud
$sql_insert = "INSERT INTO solicitudes (id_empleado, fecha_inicio, fecha_fin, dias_solicitados, estado)
               VALUES (?, ?, ?, ?, 'pendiente')";

$stmt = $conn->prepare($sql_insert);

header("Content-Type: application/json"); // Aseguramos que la respuesta siempre sea JSON

if (!$stmt) {
    echo json_encode(['error' => 'Error al preparar la consulta: ' . $conn->error]);
    exit();
}

$stmt->bind_param('issi', $id_empleado, $fecha_inicio, $fecha_fin, $dias_solicitados);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Solicitud guardada correctamente. Ahora está en revisión.']);
} else {
    echo json_encode(['error' => 'Error al ejecutar el guardado: ' . $stmt->error]);
}

// 5. Cierre de conexiones
$stmt->close();
$conn->close();
?>
