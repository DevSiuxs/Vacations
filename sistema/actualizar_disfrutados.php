<?php
session_start();
if (!isset($_SESSION['empleado_id']) || ($_SESSION['empleado_rol'] !== 'admin' && $_SESSION['empleado_rol'] !== 'editor')) {
    header("Location: ../login.php");
    exit();
}

require_once '../db_config.php'; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$hoy = date('Y-m-d');

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

?>