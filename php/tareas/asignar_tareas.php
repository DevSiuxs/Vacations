<?php
session_start();
include '../db.php'; // Ruta corregida

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['usuario_rol'] == 'admin') {
    $id_empleado = $_POST['usuario'];
    $asignado_por = $_SESSION['usuario_id'];
    
    // Primero borramos las tareas anteriores
    $stmt = $conn->prepare("DELETE FROM tareas_asignadas WHERE id_empleado = ?");
    $stmt->bind_param("i", $id_empleado);
    $stmt->execute();
    
    // Procesamos cada checkbox marcado
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'tarea_') === 0) {
            $tarea_completa = str_replace('tarea_', '', $key);
            list($modulo, $tarea) = explode('|', $tarea_completa);
            
            $stmt = $conn->prepare("INSERT INTO tareas_asignadas 
                                  (id_empleado, modulo, tarea, asignado_por) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issi", $id_empleado, $modulo, $tarea, $asignado_por);
            $stmt->execute();
        }
    }
    
    header("Location: ../../tareas.html?asignacion=exitoso");
    exit();
} else {
    header("Location: ../../login.html");
    exit();
}
?>