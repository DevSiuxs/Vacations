<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pruebas";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Solo para administradores
if (!in_array($_SESSION['usuario_rol'] ?? '', ['admin', 'editor'])) {
    header("Location: /Prueba/Modulos/Vacaciones/index.php");
    exit();
}

// Obtener todos los empleados con sus vacaciones
$empleados = $conn->query("SELECT e.id, e.nombre, v.dias_totales, v.dias_asignados, v.dias_disfrutados 
                          FROM empleados e 
                          LEFT JOIN vacaciones v ON e.id = v.id_empleado");

if (!$empleados) {
    die("Error al obtener empleados: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Vacaciones</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-btn">← Volver al inicio</a>
    <h1>Administrar Vacaciones</h1>
    
    <table>
        <tr>
            <th>Empleado</th>
            <th>Días Totales</th>
            <th>Días Asignados</th>
            <th>Días Disfrutados</th>
            <th>Acciones</th>
        </tr>
        <?php while($emp = $empleados->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($emp['nombre']) ?></td>
            <form method="post" action="actualizar_disfrutados.php">
                <input type="hidden" name="id_empleado" value="<?= $emp['id'] ?>">
                <td><input type="number" name="dias_totales" value="<?= $emp['dias_totales'] ?? 0 ?>" min="0"></td>
                <td><input type="number" name="dias_asignados" value="<?= $emp['dias_asignados'] ?? 0 ?>" min="0"></td>
                <td><input type="number" name="dias_disfrutados" value="<?= $emp['dias_disfrutados'] ?? 0 ?>" min="0"></td>
                <td><button type="submit">Actualizar</button></td>
            </form>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>