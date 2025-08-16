<?php
include '../db.php'; // Ruta corregida

$query = "SELECT id, nombre FROM empleados";
$result = $conn->query($query);

echo "<h2>Usuarios en la base de datos:</h2>";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<p>{$row['id']}: {$row['nombre']}</p>";
    }
} else {
    echo "<p>No hay usuarios registrados</p>";
}
$conn->close();
?>