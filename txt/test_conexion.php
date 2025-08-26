<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PreisaVacaciones";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
} else {
    echo "Conexión exitosa a MySQL<br>";
    
    // Verificar tablas
    $result = $conn->query("SHOW TABLES");
    echo "Tablas encontradas: " . $result->num_rows . "<br>";
    while($row = $result->fetch_array()) {
        echo $row[0] . "<br>";
    }
    
    // Verificar datos en solicitudes
    $solicitudes = $conn->query("SELECT * FROM solicitudes");
    echo "Solicitudes encontradas: " . $solicitudes->num_rows;
}
$conn->close();
?>