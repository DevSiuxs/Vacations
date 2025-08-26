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
    <title>Registro - Preisa Vacaciones</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <div class="logo">
                <img src="Assets/imagenes/URANUS-LOGO.png" alt="Logo Preisa">
            </div>
            <h2>Registro de Empleado</h2>
            <form action="auth.php" method="POST">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <input type="text" name="nombre" placeholder="Nombre completo" required>
                </div>
                <div class="form-group">
                    <select name="puesto" required>
                        <option value="">Selecciona tu puesto</option>
                        <option value="administrativo">Administrativo</option>
                        <option value="operativo">Operativo</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="date" name="fecha_ingreso" required>
                </div>
                <div class="form-group">
                    <select name="rol" required>
                        <option value="">Selecciona tu rol</option>
                        <option value="admin">Administrador</option>
                        <option value="editor">Editor</option>
                        <option value="usuario">Usuario</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirmar contraseña" required>
                </div>
                <button type="submit">Registrarse</button>
            </form>
            <p class="register-link">¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
        </div>
    </div>
</body>
</html>