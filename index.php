<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PreisaVacaciones";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el empleado (usaremos id=1 para pruebas)
$empleado_id = 1;

// Función para obtener datos del empleado
function getEmpleado($conn, $id) {
    $sql = "SELECT * FROM empleados WHERE id = $id";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Función para obtener vacaciones del empleado
function getVacaciones($conn, $id) {
    $sql = "SELECT * FROM vacaciones WHERE id_empleado = $id";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Función para obtener solicitudes pendientes
function getSolicitudesPendientes($conn) {
    $sql = "SELECT s.*, e.nombre 
            FROM solicitudes s 
            JOIN empleados e ON s.id_empleado = e.id 
            WHERE s.estado = 'pendiente'";
    return $conn->query($sql);
}

// Obtener datos
$empleado = getEmpleado($conn, $empleado_id);
$vacaciones = getVacaciones($conn, $empleado_id);
$solicitudes = getSolicitudesPendientes($conn);

// Calcular años laborales
$fecha_ingreso = new DateTime($empleado['fecha_ingreso']);
$hoy = new DateTime();
$anios_laborales = $fecha_ingreso->diff($hoy)->y;

// Calcular días a disfrutar
$a_disfrutar = $vacaciones['dias_totales'] - $vacaciones['dias_asignados'] - $vacaciones['dias_disfrutados'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Vacaciones - Preisa</title>
    <link rel="stylesheet" href="vacaciones.css">
    <link rel="stylesheet" href="pending.css">

</head>
<body>
    <nav>
        <ul>
            <li>
                <a href="#" class="back-btn" onclick="confirmarSalida()">←</a>
            </li>
            <li>
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCI+PGNpcmNsZSBjeD0iNTAiIGN5PSI1MCIgcj0iNDUiIGZpbGw9IiMxYTJhNmMiLz48cGF0aCBkPSJNMzAgMzVoNDBtLTIwIDIwaDQwbS0yMCAyMGg0MCIgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjgiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPjwvc3ZnPg==" alt="Logo Preisa">
            </li>
        </ul>
    </nav>
    
    <main>
        <?php
        // Determinar qué vista mostrar
        $vista = isset($_GET['vista']) ? $_GET['vista'] : 'vacaciones';
        
        switch ($vista) {
            case 'solicitud':
                mostrarSolicitud();
                break;
            case 'rh_periodos':
                mostrarRHPeriodos();
                break;
            case 'disponibilidad':
                mostrarDisponibilidad();
                break;
            default:
                mostrarVacaciones();
        }
        
        function mostrarVacaciones() {
            global $empleado, $vacaciones, $anios_laborales, $a_disfrutar;
            ?>
            <div class="container">
                <h1><?php echo $empleado['nombre']; ?></h1>
                
                <div class="user-info">
                    <div class="info-item">
                        <span class="label">PUESTO</span>
                        <span class="value"><?php echo $empleado['puesto']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">FECHA DE INGRESO</span>
                        <span class="value"><?php echo date('d/m/Y', strtotime($empleado['fecha_ingreso'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">AÑOS LABORALES</span>
                        <span class="value"><?php echo $anios_laborales; ?></span>
                    </div>
                </div>
                
                <div class="stats-container">
                    <div class="stat-box">
                        <span class="stat-number"><?php echo $vacaciones['dias_totales']; ?></span>
                        <span class="stat-label">DIAS DE VACACIONES</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-number"><?php echo $vacaciones['dias_asignados']; ?></span>
                        <span class="stat-label">ASIGNADOS</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-number"><?php echo $vacaciones['dias_disfrutados']; ?></span>
                        <span class="stat-label">DISFRUTO</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-number"><?php echo $a_disfrutar; ?></span>
                        <span class="stat-label">A DISFRUTAR</span>
                    </div>
                </div>
                
                <a href="?vista=solicitud" class="solicitar-btn">Solicitar Vacaciones</a>
                <a href="?vista=disponibilidad" class="solicitar-btn" style="background: linear-gradient(to right, #8e2de2, #4a00e0);">Ver Disponibilidad</a>
                <a href="?vista=rh_periodos" class="solicitar-btn" style="background: #ff0000ff;">RH</a>
            </div>
            <?php
        }
        
        function mostrarSolicitud() {
    global $a_disfrutar;
    ?>
    <div class="container">
        <h2>SOLICITUD DE VACACIONES</h2>
        
        <form id="solicitudForm">
            <div class="form-group">
                <label for="inicio">SOLICITO INICIO DE VACACIONES</label>
                <input type="date" id="inicio" onchange="calcularDias()">
            </div>
            <div class="form-group">
                <label for="fin">SOLICITO FIN DE VACACIONES</label>
                <input type="date" id="fin" onchange="calcularDias()">
            </div>
            <div class="form-group">
                <label for="dias">DIAS A PEDIR</label>
                <input type="number" id="dias" readonly>
            </div>
            <div class="form-group">
                <p style="text-align: center; font-weight: bold;">
                    Días disponibles: <span id="dias-disponibles"><?php echo $a_disfrutar; ?></span>
                </p>
            </div>
        
            <button type="button" class="submit-btn" onclick="enviarSolicitud()">Enviar Solicitud</button>
        </form>
    </div>
            <?php
        }
        
        function mostrarRHPeriodos() {
            global $solicitudes;
            ?>
            <div class="container">
                <h1>PERIODOS DE VACACIONES</h1>
                
                <?php if ($solicitudes->num_rows > 0): ?>
                    <?php while($solicitud = $solicitudes->fetch_assoc()): ?>
                        <div class="employee-periods">
                            <div class="employee-header">
                                <span><?php echo $solicitud['nombre']; ?></span>
                            </div>
                            <div class="period-info">
                                <div class="info-item">
                                    <span class="label">Periodo</span>
                                    <span class="value"><?php echo date('d M', strtotime($solicitud['fecha_inicio'])); ?> al <?php echo date('d M', strtotime($solicitud['fecha_fin'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="label">Días</span>
                                    <span class="value"><?php echo $solicitud['dias_solicitados']; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="label">Cubre</span>
                                    <span class="value">Rocio Perez</span>
                                </div>
                                <div class="info-item">
                                    <span class="label">Status</span>
                                    <span class="status-badge status-pendiente">PENDIENTE</span>
                                </div>
                            </div>
                            
                            <div class="actions">
                                <button class="action-btn accept-btn" onclick="aprobarSolicitud(<?php echo $solicitud['id']; ?>)">
                                    <i>✓</i> Aceptar
                                </button>
                                <button class="action-btn cancel-btn" onclick="rechazarSolicitud(<?php echo $solicitud['id']; ?>)">
                                    <i>✕</i> Cancelar
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="info-item" style="text-align: center; padding: 40px;">
                        <p style="font-size: 18px;">No hay solicitudes pendientes</p>
                    </div>
                <?php endif; ?>
                
                <a href="?vista=disponibilidad" class="next-btn">Siguiente</a>
            </div>
            <?php
        }
        
    function mostrarDisponibilidad() {
    global $empleado, $vacaciones, $a_disfrutar, $conn;
    
    // Obtener días en espera (solicitudes pendientes)
    $dias_en_espera = 0;
    $result = $conn->query("SELECT SUM(dias_solicitados) as total FROM solicitudes 
                          WHERE id_empleado = {$empleado['id']} AND estado = 'pendiente'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $dias_en_espera = $row['total'] ?? 0;
    }
     // Obtener el estado más reciente de las solicitudes
    $estado_vacaciones = "SOLICITADAS"; // Valor por defecto
    $tiene_aceptadas = false;
    
    // Modifica esta consulta para usar la columna correcta (fecha_creacion o la que tengas)
    $result_estado = $conn->query("SELECT estado FROM solicitudes 
                                 WHERE id_empleado = {$empleado['id']} 
                                 ORDER BY id DESC LIMIT 1"); // Ordenamos por ID en lugar de fecha_solicitud
    if ($result_estado->num_rows > 0) {
        $row_estado = $result_estado->fetch_assoc();
        $estado_vacaciones = strtoupper($row_estado['estado']);
        
        if ($row_estado['estado'] == 'aprobada') { // Asegúrate que coincide con tu DB
            $tiene_aceptadas = true;
        }
    }
    // Calcular días realmente disponibles (restando los en espera)
    $dias_reales_disponibles = max(0, $a_disfrutar - $dias_en_espera);
    ?>
    <div class="container">
        <h2>VACACIONES DISPONIBLES</h2>
        
        <div class="proposition-list">
            <div class="proposition-item">
                <span class="year">2020</span>
                <span class="days">PROPOSICIONADAS</span>
            </div>
            <div class="proposition-item">
                <span class="year">2021</span>
                <span class="days">PROPOSICIONADAS</span>
            </div>
            <div class="proposition-item">
                <span class="year">2022</span>
                <span class="days">PROPOSICIONADAS</span>
            </div>
            <div class="proposition-item highlight">
                <span class="year">DIAS DE VACACIONES</span>
                <span class="days"><?php echo $vacaciones['dias_totales']; ?></span>
            </div>
            <div class="proposition-item highlight">
                <span class="year">ASIGNADOS</span>
                <span class="days"><?php echo $vacaciones['dias_asignados']; ?></span>
            </div>
            <div class="proposition-item highlight">
                <span class="year">DISFRUTO</span>
                <span class="days"><?php echo $vacaciones['dias_disfrutados']; ?></span>
            </div>
            <div class="proposition-item used">
                <span class="year">A DISFRUTAR</span>
                <span class="days"><?php echo $dias_reales_disponibles; ?></span>
            </div>
            <div class="proposition-item highlight" style="background: linear-gradient(135deg, #ff9966, #ff5e62);">
                <span class="year">EN ESPERA</span>
                <span class="days"><?php echo $dias_en_espera; ?></span>
            </div>
            <div class="proposition-item highlight">
                <span class="days status-badge <?php 
    echo ($estado_vacaciones == 'APROBADA') ? 'status-aprobado' : 
         (($estado_vacaciones == 'RECHAZADA') ? 'status-rechazado' : 'status-pendiente'); 
?>"><?php echo $estado_vacaciones; ?></span>
            </div>
        </div>
        
        
        <!-- Mostrar solicitudes pendientes -->
        <div class="pending-requests">
            <h3>Solicitudes Pendientes</h3>
            <?php
            $solicitudes_pendientes = $conn->query("SELECT * FROM solicitudes 
                                                   WHERE id_empleado = {$empleado['id']} 
                                                   AND estado = 'pendiente'");
            if ($solicitudes_pendientes->num_rows > 0): ?>
                <div class="requests-list">
                    <?php while($solicitud = $solicitudes_pendientes->fetch_assoc()): ?>
                        <div class="request-item">
                            <span><?php echo date('d/m/Y', strtotime($solicitud['fecha_inicio'])); ?> - <?php echo date('d/m/Y', strtotime($solicitud['fecha_fin'])); ?></span>
                            <span><?php echo $solicitud['dias_solicitados']; ?> días</span>
                            <span class="status-badge status-pendiente">PENDIENTE</span>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No tienes solicitudes pendientes</p>
                <p>Pero <b>recuerda</b> Si tu status es <b style="color:#6f0;">APROBADA</b> 
                PORFAVOR acude a RecursosHumanos para que firmes la solicitud de vacaciones. 
                SI NO FIRMAS TUS VACACIONES EN PAPEL SERAN CANCELADAS EN UNA SEMANA</p>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <a href="?vista=vacaciones">Regresar</a>
            <span>a Inicio</span><br>
            <a href="#" target="_blank">Descargar Solicitud</a>
        </div>
    </div>
    <?php
}
        ?>
    </main>
    
    <script>
         const diasDisponibles = <?php echo $a_disfrutar; ?>;
        // Función para calcular días entre dos fechas
        function calcularDias() {
            const inicio = document.getElementById('inicio').value;
            const fin = document.getElementById('fin').value;
            const disponibles = parseInt(document.getElementById('dias-disponibles').textContent);
            
            if (inicio && fin) {
                const fechaInicio = new Date(inicio);
                const fechaFin = new Date(fin);
                
                // Validar que la fecha fin sea mayor que la inicio
                if (fechaFin < fechaInicio) {
                    alert("La fecha de fin debe ser posterior a la de inicio");
                    document.getElementById('dias').value = 0;
                    return;
                }
                
                // Calcula la diferencia en milisegundos
                const diferencia = fechaFin - fechaInicio;
                
                // Convierte a días (1000 ms * 60 s * 60 min * 24 h)
                const dias = Math.floor(diferencia / (1000 * 60 * 60 * 24)) + 1;
                
                document.getElementById('dias').value = dias;
                
                // Validación visual
                if (dias > disponibles) {
                    document.getElementById('dias').style.backgroundColor = '#ffcccc';
                } else if (dias > 6) {
                    document.getElementById('dias').style.backgroundColor = '#ffcccc';
                } else {
                    document.getElementById('dias').style.backgroundColor = '';
                }
            }
        }
        
        // Establecer fechas mínimas para los inputs de fecha
        window.onload = function() {
            const today = new Date();
            const minDate = today.toISOString().split('T')[0];
            document.getElementById('inicio').min = minDate;
            document.getElementById('fin').min = minDate;
        };
        
        // Función para enviar solicitud
    function enviarSolicitud() {
    const inicio = document.getElementById('inicio').value;
    const fin = document.getElementById('fin').value;
    const dias = parseInt(document.getElementById('dias').value);
    const disponibles = parseInt(document.getElementById('dias-disponibles').textContent);
    
    if (!inicio || !fin || !dias || dias <= 0) {
        alert("Por favor completa todos los campos correctamente");
        return;
    }
    
    if (dias > disponibles) {
        alert(`No tienes suficientes días disponibles.\nDisponibles: ${disponibles}\nSolicitados: ${dias}`);
        return;
    }
    
    fetch('guardar_solicitud.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `inicio=${inicio}&fin=${fin}&dias=${dias}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert("Error: " + data.error);
        } else {
            alert("Solicitud guardada con ID: " + data.id);
            window.location.href = "?vista=rh_periodos";
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Error al guardar la solicitud");
    });
}
        
// Función para aprobar solicitud
function aprobarSolicitud(id) {
    if (confirm("¿Estás seguro de aprobar esta solicitud de vacaciones?")) {
        fetch('aprobar_solicitud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&accion=aprobar`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la red');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                alert("Error al procesar: " + data.error);
            } else {
                alert(data.success);
                window.location.href = "?vista=disponibilidad";
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Error al procesar la solicitud: " + error.message);
        });
    }
}

// Función para rechazar solicitud
function rechazarSolicitud(id) {
    if (confirm("¿Estás seguro de rechazar esta solicitud de vacaciones?")) {
        fetch('aprobar_solicitud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&accion=rechazar`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la red');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                alert("Error al procesar: " + data.error);
            } else {
                alert(data.success);
                window.location.href = "?vista=disponibilidad";
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Error al procesar la solicitud: " + error.message);
        });
    }
}
        
        // Función para confirmar salida
        function confirmarSalida() {
            const currentPage = new URLSearchParams(window.location.search).get('vista') || 'vacaciones';
            let mensaje = "¿Estás seguro de que quieres salir?";
            
            if (currentPage === 'vacaciones' || currentPage === 'disponibilidad') {
                mensaje = "¿Estás seguro de que quieres salir de Vacaciones?";
            }
            
            if (confirm(mensaje)) {
                // Redirigir a la página de inicio
                window.location.href = "Inicio.html";
            }
        }
        
        // Establecer fechas mínimas para los inputs de fecha
        window.onload = function() {
            const today = new Date();
            const minDate = today.toISOString().split('T')[0];
            document.getElementById('inicio').min = minDate;
            document.getElementById('fin').min = minDate;
        };
    </script>
</body>
</html>
<?php
// Cerrar conexión
$conn->close();
?>