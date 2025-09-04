<?php
session_start();
if (!isset($_SESSION['empleado_id']) || ($_SESSION['empleado_rol'] !== 'admin' && $_SESSION['empleado_rol'] !== 'editor')) {
    header("Location: ../login.php");
    exit();
}

// Configuración de la base de datos
require_once '../db_config.php'; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$hoy = date('Y-m-d');

// Primero, obtener todas las solicitudes aprobadas cuya fecha de inicio ya pasó
// y que aún no han sido contabilizadas en días disfrutados
$sql = "SELECT s.id_empleado, s.dias_solicitados, s.fecha_inicio
        FROM solicitudes s
        WHERE s.estado = 'aprobada' 
        AND s.fecha_inicio <= '$hoy'
        AND s.id NOT IN (
            SELECT id_solicitud FROM vacaciones_disfrutadas
        )";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $id_empleado = $row['id_empleado'];
        $dias_solicitados = $row['dias_solicitados'];
        
        // Actualizar días disfrutados
        $update_sql = "UPDATE vacaciones 
                      SET dias_disfrutados = dias_disfrutados + $dias_solicitados 
                      WHERE id_empleado = $id_empleado";
        
        if ($conn->query($update_sql)) {
            // Registrar que estos días ya fueron contabilizados
            $insert_sql = "INSERT INTO vacaciones_disfrutadas (id_solicitud, fecha_procesamiento) 
                          VALUES ({$row['id']}, NOW())";
            $conn->query($insert_sql);
        }
    }
}

$conn->close();

// Crear la tabla vacaciones_disfrutadas si no existe
// CREATE TABLE vacaciones_disfrutadas (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     id_solicitud INT NOT NULL,
//     fecha_procesamiento DATETIME NOT NULL,
//     FOREIGN KEY (id_solicitud) REFERENCES solicitudes(id)
// );
?>