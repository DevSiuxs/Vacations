<?php
// Iniciar o reanudar la sesión
session_start();

// Verificar si el usuario está autenticado correctamente
if (isset($_SESSION['empleado_id']) && !empty($_SESSION['empleado_id'])) {
    header("Location: sistema/index.php");
    exit();
} else {
    // Destruir cualquier sesión existente por seguridad
    session_unset();
    session_destroy();
    
    // Redirigir al login
    header("Location: login.php");
    exit();
}
?>