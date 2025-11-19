<?php
session_start();

if (isset($_SESSION['empleado_id']) && !empty($_SESSION['empleado_id'])) {
    header("Location: sistema/index.php");
    exit();
} else {
    
    session_unset();
    session_destroy();
    
    
    header("Location: login.php");
    exit();
}
?>