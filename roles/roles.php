<?php
session_start();
// Solo permitir acceso a administradores
if (!isset($_SESSION['empleado_id']) || $_SESSION['empleado_rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Empleados - Preisa Vacaciones</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    
 
    
    <div class="login-container">
        <div class="login-form">
            <div class="logo">
                <img src="../Assets/imagenes/URANUS-LOGO.png" alt="Logo Preisa">
            </div>
         
            
            <form action="admin_auth.php" method="POST">
                <input type="hidden" name="action" value="admin_register">
                <div class="form-group">
                    <input type="text" name="nombre" placeholder="Nombre completo" required>
                </div>
                <div class="form-group">
                    <select name="puesto" required>
                        <option value="">Selecciona el puesto</option>
                        <option value="administrativo">Administrativo</option>
                        <option value="operativo">Operativo</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="date" name="fecha_ingreso" required>
                </div>
                <div class="form-group">
                    <select name="rol" required>
                        <option value="">Selecciona el rol</option>
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
                <button type="submit">Registrar Empleado</button>
                <a href="../sistema/index.php" style="text-decoration:none; color:red; text-aling:center;">Volver al Sistema</a>
            </form>
        </div>
    </div>

    
</body>
</html>