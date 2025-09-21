<?php
session_start();
// Solo permitir acceso a administradores
if (!isset($_SESSION['empleado_id']) || $_SESSION['empleado_rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'admin_register') {
    // Recibir y validar datos
    $nombre = trim($_POST['nombre']);
    $puesto = $_POST['puesto'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $rol = $_POST['rol'];
    $user_password = $_POST['password']; 
    $confirm_password = $_POST['confirm_password'];
    
    // Validaciones
    if ($user_password !== $confirm_password) {
        header("Location: roles.php?error=Las contraseñas no coinciden");
        exit();
    }
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }
    
    // Verificar si el empleado ya existe
    $stmt = $conn->prepare("SELECT id FROM empleados WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $conn->close();
        header("Location: roles.php?error=Ya existe un empleado con ese nombre");
        exit();
    }
    $stmt->close();
    
    // Hash de la contraseña del usuario
    $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
    
    // Insertar nuevo empleado
    $stmt = $conn->prepare("INSERT INTO empleados (nombre, password, puesto, fecha_ingreso, rol) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nombre, $hashed_password, $puesto, $fecha_ingreso, $rol);
    
    if ($stmt->execute()) {
        $nuevo_id = $stmt->insert_id;
        
        // Insertar registro en la tabla de vacaciones
        $stmt2 = $conn->prepare("INSERT INTO vacaciones (id_empleado, dias_totales, dias_asignados, dias_disfrutados) VALUES (?, 0, 0, 0)");
        $stmt2->bind_param("i", $nuevo_id);
        $stmt2->execute();
        $stmt2->close();
        
        $stmt->close();
        $conn->close();
        
        header("Location: roles.php?success=Empleado registrado correctamente");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: roles.php?error=Error al registrar el empleado: " . $conn->error);
        exit();
    }
} else {
    header("Location: roles.php");
    exit();
}
?>