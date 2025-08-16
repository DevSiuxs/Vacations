<?php
session_start();
session_unset();
session_destroy();
header("Location: /Prueba/login.html?logout=true"); // Ruta absoluta desde la raíz
exit();
?>