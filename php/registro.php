<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $puesto = $_POST['puesto'];
    $empresa = $_POST['empresa'] ?? null;
    $fecha_ingreso = date('Y-m-d');
    $rol = $_POST['rol']; // Ahora se obtiene del formulario

    $stmt = $conn->prepare("INSERT INTO empleados (nombre, correo, contrasena, puesto, fecha_ingreso, rol, empresa) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nombre, $correo, $contrasena, $puesto, $fecha_ingreso, $rol, $empresa);
    
    if ($stmt->execute()) {
        header("Location: /Prueba/login.html?registro=exitoso");
    } else {
        echo "Error al registrar: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>