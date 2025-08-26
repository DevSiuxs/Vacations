<?php
session_start();
require_once 'db_config.php'; // Archivo con configuración de BD

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'login') {
        // Procesar login
        $nombre = trim($_POST['nombre']);
        $password = $_POST['password'];
        
        $stmt = $conn->prepare("SELECT id, nombre, password, rol FROM empleados WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $empleado = $result->fetch_assoc();
            if (password_verify($password, $empleado['password'])) {
                $_SESSION['empleado_id'] = $empleado['id'];
                $_SESSION['empleado_nombre'] = $empleado['nombre'];
                $_SESSION['empleado_rol'] = $empleado['rol'];
                
                // Inicializar vacaciones si no existen
                inicializarVacaciones($conn, $empleado['id']);
                
               header("Location: sistema/index.php");
                exit();
            } else {
                header("Location: login.php?error=Credenciales incorrectas");
                exit();
            }
        } else {
            header("Location: login.php?error=Usuario no encontrado");
            exit();
        }
    } 
    elseif ($action === 'register') {
        // Procesar registro
        $nombre = trim($_POST['nombre']);
        $puesto = $_POST['puesto'];
        $fecha_ingreso = $_POST['fecha_ingreso'];
        $rol = $_POST['rol'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validaciones
        if ($password !== $confirm_password) {
            header("Location: register.php?error=Las contraseñas no coinciden");
            exit();
        }
        
        // Verificar si el usuario ya existe
        $stmt = $conn->prepare("SELECT id FROM empleados WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            header("Location: register.php?error=El usuario ya existe");
            exit();
        }
        
        // Hash de la contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar nuevo empleado
        $stmt = $conn->prepare("INSERT INTO empleados (nombre, puesto, fecha_ingreso, rol, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nombre, $puesto, $fecha_ingreso, $rol, $hashed_password);
        
        if ($stmt->execute()) {
            $nuevo_id = $stmt->insert_id;
            
            // Inicializar registro de vacaciones
            inicializarVacaciones($conn, $nuevo_id, $puesto, $fecha_ingreso);
            
            // Iniciar sesión automáticamente
            $_SESSION['empleado_id'] = $nuevo_id;
            $_SESSION['empleado_nombre'] = $nombre;
            $_SESSION['empleado_rol'] = $rol;
            
            header("Location: sistema/index.php");
            exit();
        } else {
            header("Location: register.php?error=Error al registrar usuario");
            exit();
        }
    }
}

// Función para inicializar vacaciones según puesto y antigüedad
function inicializarVacaciones($conn, $empleado_id, $puesto = null, $fecha_ingreso = null) {
    // Si no se proporcionan, obtener de la BD
    if (!$puesto || !$fecha_ingreso) {
        $stmt = $conn->prepare("SELECT puesto, fecha_ingreso FROM empleados WHERE id = ?");
        $stmt->bind_param("i", $empleado_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $empleado = $result->fetch_assoc();
            $puesto = $empleado['puesto'];
            $fecha_ingreso = $empleado['fecha_ingreso'];
        } else {
            return false;
        }
    }
    
    // Calcular años de antigüedad
    $hoy = new DateTime();
    $ingreso = new DateTime($fecha_ingreso);
    $antiguedad = $ingreso->diff($hoy)->y;
    
    // Determinar días según puesto y antigüedad
    $dias_totales = 0;
    $dias_asignados = 0;
    
    if ($puesto === 'administrativo') {
        if ($antiguedad == 1) {
            $dias_totales = 12;
            $dias_asignados = 12;
        } elseif ($antiguedad == 2) {
            $dias_totales = 14;
            $dias_asignados = 12;
        } elseif ($antiguedad == 3) {
            $dias_totales = 16;
            $dias_asignados = 12;
        } elseif ($antiguedad == 4) {
            $dias_totales = 18;
            $dias_asignados = 16;
        } elseif ($antiguedad == 5) {
            $dias_totales = 20;
            $dias_asignados = 15;
        } elseif ($antiguedad >= 6 && $antiguedad <= 10) {
            $dias_totales = 22;
            $dias_asignados = 17;
        } elseif ($antiguedad >= 11 && $antiguedad <= 15) {
            $dias_totales = 24;
            $dias_asignados = 19;
        } elseif ($antiguedad >= 16 && $antiguedad <= 20) {
            $dias_totales = 26;
            $dias_asignados = 21;
        } elseif ($antiguedad >= 21 && $antiguedad <= 25) {
            $dias_totales = 28;
            $dias_asignados = 22;
        } elseif ($antiguedad >= 26) {
            $dias_totales = 30;
            $dias_asignados = 22;
        }
    } elseif ($puesto === 'operativo') {
        if ($antiguedad == 1) {
            $dias_totales = 12;
            $dias_asignados = 10;
        } elseif ($antiguedad == 2) {
            $dias_totales = 14;
            $dias_asignados = 10;
        } elseif ($antiguedad == 3) {
            $dias_totales = 16;
            $dias_asignados = 10;
        } elseif ($antiguedad == 4) {
            $dias_totales = 18;
            $dias_asignados = 10;
        } elseif ($antiguedad == 5) {
            $dias_totales = 20;
            $dias_asignados = 10;
        } elseif ($antiguedad >= 6 && $antiguedad <= 10) {
            $dias_totales = 22;
            $dias_asignados = 10;
        } elseif ($antiguedad >= 11 && $antiguedad <= 15) {
            $dias_totales = 24;
            $dias_asignados = 10;
        } elseif ($antiguedad >= 16 && $antiguedad <= 20) {
            $dias_totales = 26;
            $dias_asignados = 10;
        } elseif ($antiguedad >= 21 && $antiguedad <= 25) {
            $dias_totales = 28;
            $dias_asignados = 10;
        } elseif ($antiguedad >= 26) {
            $dias_totales = 30;
            $dias_asignados = 10;
        }
    }
    
    // Verificar si ya existe registro
    $check = $conn->prepare("SELECT id FROM vacaciones WHERE id_empleado = ?");
    $check->bind_param("i", $empleado_id);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        // Actualizar existente
        $stmt = $conn->prepare("UPDATE vacaciones SET dias_totales = ?, dias_asignados = ? WHERE id_empleado = ?");
        $stmt->bind_param("iii", $dias_totales, $dias_asignados, $empleado_id);
    } else {
        // Crear nuevo
        $dias_disfrutados = 0;
        $stmt = $conn->prepare("INSERT INTO vacaciones (id_empleado, dias_totales, dias_asignados, dias_disfrutados) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $empleado_id, $dias_totales, $dias_asignados, $dias_disfrutados);
    }
    
    return $stmt->execute();
}

header("Location: login.php");
exit();