<?php

// lOCAL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PreisaVacaciones";

// Para entorno REMOTO (servidor)
// $servername_remote = "funeralesuribe.com.mx";
// $username_remote = "sistemas2025@funeralesuribe.com.mx";
// $password_remote = "sistemas@2025";
// $dbname_remote = "21";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>