<?php
session_start();
if (!isset($_SESSION['empleado_id']) || ($_SESSION['empleado_rol'] !== 'admin' && $_SESSION['empleado_rol'] !== 'editor')) {
    header("Location: ../login.php");
    exit();
}

require_once '../../db_config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_empleado = intval($_POST['id_empleado']);
    $fecha_inicio = $conn->real_escape_string($_POST['fecha_inicio']);
    $fecha_fin = $conn->real_escape_string($_POST['fecha_fin']);
    $dias_solicitados = intval($_POST['dias_solicitados']);

    // Validar fechas (solo verificar que inicio sea antes que fin)
    if ($fecha_inicio > $fecha_fin) {
        $mensaje = "La fecha de inicio debe ser anterior a la fecha de fin.";
        $tipo_mensaje = "error";
    } else {
        // Insertar la solicitud con estado aprobado
        $sql = "INSERT INTO solicitudes (id_empleado, fecha_inicio, fecha_fin, dias_solicitados, estado, fecha_aprobacion)
                VALUES (?, ?, ?, ?, 'aprobada', NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $id_empleado, $fecha_inicio, $fecha_fin, $dias_solicitados);

        if ($stmt->execute()) {
            $mensaje = "Solicitud aprobada y registrada correctamente.";
            $tipo_mensaje = "exito";
        } else {
            $mensaje = "Error al registrar la solicitud: " . $conn->error;
            $tipo_mensaje = "error";
        }
        $stmt->close();
    }
}

// Obtener lista de empleados
$empleados = $conn->query("SELECT id, nombre, puesto FROM empleados ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Solicitud Aprobada - Sistema de Vacaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

           body {
    background: url(../../Assets/imagenes/Uranus-Background.png);
    background-repeat: no-repeat;
    background-size: cover; /* Cubre todo el área */
    background-position: center center; /* Centra la imagen */
    background-attachment: fixed; /* Fija la imagen para que no se alargue */
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    color: #333;

    /* Agregar para el overlay */
    position: relative;
}

/* AGREGAR ESTO: Overlay oscuro/difuminado */
body::before {
    content: '';
    position: fixed; /* Fijo para cubrir toda la pantalla */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4); /* Oscurece la imagen */
    backdrop-filter: blur(4px); /* Difumina la imagen de fondo */
    z-index: -1; /* Coloca detrás del contenido */
}

/* Asegúrate que el contenido esté sobre el overlay */
main, nav, .container {
    position: relative;
    z-index: 1;
}

        nav {
            background-color: #f2f2f280;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 1);
        }

        nav ul {
            display: flex;
            list-style: none;
            width: 100%;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        nav img {
            height: 50px;
            border-radius: 25%;
            border: 1px solid #fff;
        }

        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 600px;
            padding: 30px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1, h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #1a2a6c;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group select:focus,
        .form-group input:focus {
            border-color: #2193b0;
            outline: none;
            box-shadow: 0 0 0 3px rgba(33, 147, 176, 0.25);
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, #00b09b, #96c93d);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
        }

        .exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .nav-links {
            display: flex;
            justify-content: center;
            margin-top: 25px;
            gap: 15px;
            flex-wrap: wrap;
        }

        .nav-links a {
            padding: 12px 20px;
            background: linear-gradient(to right, #1a2a6c, #2c3e50);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: opacity 0.3s;
        }

        .nav-links a:hover {
            opacity: 0.9;
        }

        .info-box {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                width: 95%;
            }

            h1, h2 {
                font-size: 1.4rem;
            }

            .form-group select,
            .form-group input {
                padding: 14px;
                font-size: 16px;
            }

            .btn-submit {
                padding: 16px;
                font-size: 16px;
            }

            .nav-links {
                flex-direction: column;
                gap: 10px;
            }

            .nav-links a {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            nav ul {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            nav li {
                margin-bottom: 8px;
            }

            .container {
                padding: 15px;
            }

            h1, h2 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li>
                <img src="../../Assets/icons/Uranus.ico" alt="Logo Preisa">
                <li>
                    <span style="color: white; padding-left: 0.8rem;"><?php echo $_SESSION['empleado_nombre']; ?> <br><b style="color: Green; padding-left: 0.8rem;"><?php echo $_SESSION['empleado_rol']; ?></b></span>
                </li>
            </li>
            <li style="margin-left: auto;">
                <a href="../../logout.php" style="margin-left:15px; text-decoration:none; font-size:24px; color:red; title:cerrar sesion;"><i class="fas fa-power-off"></i></a>
            </li>
        </ul>
    </nav>

    <main>
        <div class="container">
            <h1>Agregar Solicitud Aprobada</h1>

            <div class="info-box">
                <i class="fas fa-info-circle"></i> Todas las solicitudes agregadas aquí se registrarán automáticamente como APROBADAS.
            </div>

            <?php if ($mensaje): ?>
                <div class="message <?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="id_empleado">Empleado:</label>
                    <select id="id_empleado" name="id_empleado" required>
                        <option value="">Seleccionar empleado</option>
                        <?php while($empleado = $empleados->fetch_assoc()): ?>
                            <option value="<?php echo $empleado['id']; ?>">
                                <?php echo htmlspecialchars($empleado['nombre']) . ' - ' . htmlspecialchars($empleado['puesto']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha_inicio">Fecha de Inicio:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                </div>

                <div class="form-group">
                    <label for="fecha_fin">Fecha de Fin:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" required>
                </div>

                <div class="form-group">
                    <label for="dias_solicitados">Días Solicitados:</label>
                    <input type="number" id="dias_solicitados" name="dias_solicitados"
                           min="1" required placeholder="Número de días">
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-check-circle"></i> Registrar Solicitud Aprobada
                </button>
            </form>

            <div class="nav-links">
                <a href="../../sistema/index.php"><i class="fas fa-home"></i> Volver al Inicio</a>
                <a href="../../historial/historial.php"><i class="fas fa-history"></i> Ver Historial</a>
            </div>
        </div>
    </main>

    <script>
        // Calcular días automáticamente al cambiar fechas
        document.getElementById('fecha_inicio').addEventListener('change', calcularDias);
        document.getElementById('fecha_fin').addEventListener('change', calcularDias);

        function calcularDias() {
            const inicio = new Date(document.getElementById('fecha_inicio').value);
            const fin = new Date(document.getElementById('fecha_fin').value);

            if (inicio && fin && inicio <= fin) {
                const diffTime = fin - inicio;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                document.getElementById('dias_solicitados').value = diffDays;
            } else if (inicio && fin && inicio > fin) {
                // Si las fechas están al revés, mostrar advertencia
                document.getElementById('dias_solicitados').value = '';
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
            }
        }

        // Validar que la fecha fin sea mayor que la inicio
        document.querySelector('form').addEventListener('submit', function(e) {
            const inicio = new Date(document.getElementById('fecha_inicio').value);
            const fin = new Date(document.getElementById('fecha_fin').value);

            if (inicio > fin) {
                e.preventDefault();
                alert('Error: La fecha de inicio debe ser anterior a la fecha de fin');
            }

            // Validar que se hayan ingresado días
            const dias = document.getElementById('dias_solicitados').value;
            if (!dias || dias < 1) {
                e.preventDefault();
                alert('Error: Debe ingresar un número válido de días');
            }
        });
    </script>
</body>
</html>

<?php
// Cerrar conexión
$conn->close();
?>
