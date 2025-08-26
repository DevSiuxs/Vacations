<?php
session_start();
if (isset($_SESSION['empleado_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Preisa Vacaciones</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <div class="logo">
                <img src="Assets/imagenes/URANUS-LOGO.png" alt="Logo Preisa">
            </div>
            <h2>Iniciar Sesión</h2>
            <form action="auth.php" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <input type="text" name="nombre" placeholder="Nombre" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                <button type="submit">Ingresar</button>
            </form>
            <p class="register-link">¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
        </div>
    </div>
</body>
</html>