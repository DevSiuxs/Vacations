<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['usuario_rol'] == 'admin') {
    $id_empleado = $_POST['usuario'];
    $asignado_por = $_SESSION['usuario_id'];
    
    // Primero borramos las tareas anteriores para este empleado
    $stmt = $conn->prepare("DELETE FROM tareas_asignadas WHERE id_empleado = ?");
    $stmt->bind_param("i", $id_empleado);
    $stmt->execute();
    
    // Procesamos cada checkbox marcado
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'tarea_') === 0) {
            $parts = explode('_', $key);
            $modulo = $parts[1];
            $submodulo = $parts[2];
            $tarea = $parts[3];
            
            $stmt = $conn->prepare("INSERT INTO tareas_asignadas (id_empleado, modulo, submodulo, tarea, asignado_por) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $id_empleado, $modulo, $submodulo, $tarea, $asignado_por);
            $stmt->execute();
        }
    }
    
    header("Location: ../tareas.html?asignacion=exitoso");
    exit();
} else {
    header("Location: ../login.html");
    exit();
}
?>