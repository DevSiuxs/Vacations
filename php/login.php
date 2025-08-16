<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    $stmt = $conn->prepare("SELECT id, nombre, contrasena, rol FROM empleados WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $usuario = $result->fetch_assoc();
        
        if (password_verify($contrasena, $usuario['contrasena'])) {
            $_SESSION['usuario_id'] = $usuario['id'];   
            $_SESSION['usuario_nombre'] = $usuario['nombre']; 
            $_SESSION['usuario_rol'] = $usuario['rol'];
            // Redirección corregida (usa ruta absoluta desde la raíz)
            header("Location: /Prueba/home.php");
            exit();
        } else {
            // Mostrar error en el login
            header("Location: /Prueba/login.php?error=contrasena");
            exit();
        }
    } else {
        header("Location: /Prueba/login.php?error=usuario");
        exit();
    }
    
    $stmt->close();
    $conn->close();
}
?>