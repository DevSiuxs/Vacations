<?php
session_start();
if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'admin') {
    die("Acceso no autorizado");
}

$conn = new mysqli("localhost", "root", "", "pruebas");

// Validar datos
$id_empleado = intval($_POST['id_empleado']);
$dias_totales = intval($_POST['dias_totales']);
$dias_asignados = intval($_POST['dias_asignados']);
$dias_disfrutados = intval($_POST['dias_disfrutados']);

// Validar que asignados no sean mayores que totales
if ($dias_asignados > $dias_totales) {
    die("Error: Días asignados no pueden ser mayores que días totales");
}

// Validar que disfrutados no sean mayores que asignados
if ($dias_disfrutados > $dias_asignados) {
    die("Error: Días disfrutados no pueden ser mayores que días asignados");
}

// Usar sentencias preparadas para seguridad
$stmt = $conn->prepare("INSERT INTO vacaciones (id_empleado, dias_totales, dias_asignados, dias_disfrutados)
                      VALUES (?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE 
                      dias_totales = VALUES(dias_totales),
                      dias_asignados = VALUES(dias_asignados),
                      dias_disfrutados = VALUES(dias_disfrutados)");

$stmt->bind_param("iiii", $id_empleado, $dias_totales, $dias_asignados, $dias_disfrutados);

if ($stmt->execute()) {
    header("Location: admin_vacaciones.php?success=1");
} else {
    header("Location: admin_vacaciones.php?error=" . urlencode($stmt->error));
}

$stmt->close();
$conn->close();
?>