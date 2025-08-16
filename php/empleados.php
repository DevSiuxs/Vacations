<?php
header('Content-Type: application/json');
include 'db.php'; // Cambiado a ruta relativa

$query = "SELECT id, nombre, rol FROM empleados"; // Agregado rol para debug
$result = $conn->query($query);

if (!$result) {
    // Debug mejorado
    error_log("Error en empleados.php: " . $conn->error);
    echo json_encode(['error' => true, 'message' => $conn->error]);
    exit();
}

$empleados = [];
while ($row = $result->fetch_assoc()) {
    $empleados[] = $row;
}

echo json_encode($empleados);
$conn->close();
?>