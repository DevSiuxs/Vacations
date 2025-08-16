<?php
$conn = new mysqli("localhost", "root", "", "pruebas");

// Obtener todos los empleados
$empleados = $conn->query("SELECT id FROM empleados");

while($emp = $empleados->fetch_assoc()) {
    // Verificar si ya tiene registro de vacaciones
    $check = $conn->query("SELECT * FROM vacaciones WHERE id_empleado = {$emp['id']}");
    
    if($check->num_rows == 0) {
        $conn->query("INSERT INTO vacaciones (id_empleado, dias_totales, dias_asignados, dias_disfrutados) 
                      VALUES ({$emp['id']}, 0, 0, 0)");
    }
}

echo "Registros de vacaciones inicializados correctamente";
?>