<?php
session_start();
if (!isset($_SESSION['empleado_id'])) {
    header("Location: ../login.php");
    exit();
}
// Configuración de la base de datos
require_once '../db_config.php'; // Archivo con configuración de BD

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obtener solicitudes aprobadas cuya fecha de inicio ya pasó pero no se han marcado como disfrutadas
$hoy = date('Y-m-d');
$sql = "SELECT s.id_empleado, SUM(s.dias_solicitados) as total 
        FROM solicitudes s
        LEFT JOIN vacaciones v ON s.id_empleado = v.id_empleado
        WHERE s.estado = 'aprobada' 
        AND s.fecha_inicio <= '$hoy'
        AND (v.dias_disfrutados IS NULL OR 
             s.dias_solicitados > (SELECT SUM(dias_solicitados) FROM solicitudes 
                                   WHERE id_empleado = s.id_empleado 
                                   AND estado = 'aprobada' 
                                   AND fecha_inicio <= '$hoy'))
        GROUP BY s.id_empleado";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $conn->query("UPDATE vacaciones SET dias_disfrutados = dias_disfrutados + {$row['total']} 
                     WHERE id_empleado = {$row['id_empleado']}");
    }
}

$conn->close();
?>