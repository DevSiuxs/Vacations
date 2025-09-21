<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PreisaVacaciones";

// Intentar conexión con mysqli_connect
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Verificar conexión
if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}
?>