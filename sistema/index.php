<?php
session_start();
if (!isset($_SESSION['empleado_id'])) {
    header("Location: ../login.php");
    exit();
}
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

// Obtener el empleado desde la sesión
$empleado_id = $_SESSION['empleado_id']; 

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

// Función para obtener solicitudes pendientes (según el rol) - CORREGIDA
function getSolicitudesPendientes($conn, $empleado_id, $rol) {
    // Si es admin o editor, ver todas las solicitudes pendientes
    if ($rol === 'admin' || $rol === 'editor') {
        $sql = "SELECT s.*, e.nombre 
                FROM solicitudes s 
                JOIN empleados e ON s.id_empleado = e.id 
                WHERE s.estado = 'pendiente'";
    } else {
        // Si es usuario normal, solo ver sus propias solicitudes
        $sql = "SELECT s.*, e.nombre 
                FROM solicitudes s 
                JOIN empleados e ON s.id_empleado = e.id 
                WHERE s.estado = 'pendiente' AND s.id_empleado = $empleado_id";
    }
    return $conn->query($sql);
}

// Obtener datos
$empleado = getEmpleado($conn, $empleado_id);
$vacaciones = getVacaciones($conn, $empleado_id);
$solicitudes = getSolicitudesPendientes($conn, $empleado_id, $_SESSION['empleado_rol']);

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
    <link rel="stylesheet" href="vacaciones.css">
</head>
<body>
     <nav>
        <ul>
            <li>
                <img src="../Assets/icons/Uranus.ico" alt="Logo Preisa">
                <li>
                    <span style="color: white; padding-left: 0.8rem;"><?php echo $_SESSION['empleado_nombre']; ?> <br><b style="color: Green; padding-left: 0.8rem;"><?php echo $_SESSION['empleado_rol']; ?></b></span>
                </li>
            </li>
            <li style="margin-left: auto;">
                <a href="../logout.php" style="color: white; margin-left: 15px; text-decoration:none;"><img src="../Assets/icons/power-icon.webp" alt="Cerrar Sesion"></a>
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
                // Solo admin y editor pueden acceder a RH
                if ($_SESSION['empleado_rol'] === 'admin' || $_SESSION['empleado_rol'] === 'editor') {
                    mostrarRHPeriodos();
                } else {
                    echo "<div class='container'><h2>Acceso restringido</h2><p>No tienes permisos para acceder a esta sección.</p></div>";
                }
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
                        <!-- <span class="stat-label">ASIGNADOS</span> -->
                         <a href="../table_vacaciones/tabla.html"><span class="stat-label">ASIGNADOS</span></a>
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
                
                <!-- Solo mostrar RH para admin y editor -->
                <?php if ($_SESSION['empleado_rol'] === 'admin' || $_SESSION['empleado_rol'] === 'editor'): ?>
                    <a href="?vista=rh_periodos" class="solicitar-btn" style="background: #ff0000ff;">RH</a>
                <?php endif; ?>
            </div>
            <?php
        }
        
        function mostrarSolicitud() {
            global $a_disfrutar, $conn;
            $id_empleado = $_SESSION['empleado_id']; // Usar el ID de la sesión
            
            // Calcular fecha mínima (hoy + 15 días)
            $fechaMinima = date('Y-m-d', strtotime('+15 days'));
            
            // Obtener fechas bloqueadas (aprobadas) para este empleado
            $result = $conn->query("SELECT fecha_inicio, fecha_fin 
                               FROM solicitudes 
                               WHERE id_empleado = $id_empleado 
                               AND (estado = 'aprobada' OR estado = 'pendiente')");
            
            $fechasBloqueadas = [];
            while ($row = $result->fetch_assoc()) {
                $fechasBloqueadas[] = [
                    'start' => $row['fecha_inicio'],
                    'end' => $row['fecha_fin']
                ];
            }
            
            // Convertir a JSON para usar en JavaScript
            $fechasBloqueadasJson = json_encode($fechasBloqueadas);
            ?>
            <div class="container">
                <h2>SOLICITUD DE VACACIONES</h2>
                
                <form id="solicitudForm">
                    <div class="form-group">
                        <label for="inicio">SOLICITO INICIO DE VACACIONES</label>
                        <input type="date" id="inicio" onchange="calcularDias()" min="<?php echo $fechaMinima; ?>">
                    </div>
                    <div class="form-group">
                        <label for="fin">SOLICITO FIN DE VACACIONES</label>
                        <input type="date" id="fin" onchange="calcularDias()" min="<?php echo $fechaMinima; ?>">
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
                    <a href="?vista=vacaciones" class="solicitar-btn" style="background: #ff0000ff;">Cancelar</a>
                </form>
            </div>
            
            <script>
            // Fechas bloqueadas (aprobadas)
            const fechasBloqueadas = <?php echo $fechasBloqueadasJson; ?>;
            
            function validarFechasNoBloqueadas(inicio, fin) {
                const inicioDate = new Date(inicio);
                const finDate = new Date(fin);
                
                for (const bloqueo of fechasBloqueadas) {
                    const bloqueoInicio = new Date(bloqueo.start);
                    const bloqueoFin = new Date(bloqueo.end);
                    
                    if (
                        (inicioDate >= bloqueoInicio && inicioDate <= bloqueoFin) ||
                        (finDate >= bloqueoInicio && finDate <= bloqueoFin) ||
                        (inicioDate <= bloqueoInicio && finDate >= bloqueoFin)
                    ) {
                        return {valido: false, mensaje: 'Estas fechas se solapan con tus propias solicitudes'};
                    }
                }
                
                // Luego verificar con el servidor para todos los usuarios
                return fetch('verificar_disponibilidad.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `inicio=${inicio}&fin=${fin}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.disponible) {
                        return {valido: true};
                    } else {
                        return {valido: false, mensaje: data.mensaje || 'Estas fechas ya están ocupadas por otro usuario'};
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    return {valido: true}; // En caso de error, permitir continuar
                });
            }
            
            async function calcularDias() {
                const inicio = document.getElementById('inicio').value;
                const fin = document.getElementById('fin').value;
                const disponibles = parseInt(document.getElementById('dias-disponibles').textContent);
                
                if (inicio && fin) {
                    // Validación de 15 días mínimo
                    const hoy = new Date();
                    const fechaInicio = new Date(inicio);
                    const diffTime = fechaInicio - hoy;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    
                    if (diffDays < 15) {
                        alert('La fecha de inicio debe ser al menos 15 días después de hoy');
                        document.getElementById('inicio').value = '';
                        document.getElementById('dias').value = '';
                        return;
                    }
                    
                    const fechaFin = new Date(fin);
                    
                    // Validar que la fecha fin sea mayor que la inicio
                    if (fechaFin < fechaInicio) {
                        alert("La fecha de fin debe ser posterior a la de inicio");
                        document.getElementById('dias').value = 0;
                        return;
                    }
                    
                    // Validar que no se solape con fechas bloqueadas
                    const validacion = await validarFechasNoBloqueadas(inicio, fin);
                    if (!validacion.valido) {
                        alert(validacion.mensaje);
                        document.getElementById('dias').value = '';
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
                    } else {
                        document.getElementById('dias').style.backgroundColor = '';
                    }
                }
            }
            </script>
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
            global $empleado, $vacaciones, $conn;
            $id_empleado = $_SESSION['empleado_id']; // Usar el ID de la sesión
            
            // Calcular días teóricos disponibles (sin considerar disfrutados)
            $dias_teoricos = $vacaciones['dias_totales'] - $vacaciones['dias_asignados'];
            
            // Obtener días en espera (solicitudes pendientes)
            $dias_en_espera = 0;
            $result = $conn->query("SELECT SUM(dias_solicitados) as total FROM solicitudes 
                                  WHERE id_empleado = $id_empleado AND estado = 'pendiente'");
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $dias_en_espera = $row['total'] ?? 0;
            }
            
            // Obtener última solicitud (la más reciente sin importar estado)
            $ultima_solicitud = null;
            $result_ultima = $conn->query("SELECT * FROM solicitudes 
                                         WHERE id_empleado = $id_empleado
                                         ORDER BY id DESC LIMIT 1");
            if ($result_ultima->num_rows > 0) {
                $ultima_solicitud = $result_ultima->fetch_assoc();
            }
            
            // Obtener el estado más reciente - MODIFICADO
            $estado_vacaciones = "AÚN NO SOLICITADAS"; // Valor por defecto
            $result_estado = $conn->query("SELECT estado FROM solicitudes 
                                         WHERE id_empleado = $id_empleado 
                                         ORDER BY id DESC LIMIT 1");
            if ($result_estado->num_rows > 0) {
                $row_estado = $result_estado->fetch_assoc();
                $estado_vacaciones = strtoupper($row_estado['estado']);
            }
            
            // Calcular días realmente disponibles (sin restar aprobadas hasta que llegue la fecha)
            $dias_reales_disponibles = $dias_teoricos - $dias_en_espera;
            
            // Verificar si hay vacaciones aprobadas que ya deberían estar en disfrutados
            $hoy = new DateTime();
            $result_por_disfrutar = $conn->query("SELECT SUM(dias_solicitados) as total FROM solicitudes 
                                                WHERE id_empleado = $id_empleado 
                                                AND estado = 'aprobada' 
                                                AND fecha_inicio <= '".$hoy->format('Y-m-d')."'");
            if ($result_por_disfrutar->num_rows > 0) {
                $row = $result_por_disfrutar->fetch_assoc();
                $dias_reales_disponibles -= $row['total'] ?? 0;
            }
            
            $dias_reales_disponibles = max(0, $dias_reales_disponibles);
            ?>
        
            <div class="container">
                <h2>VACACIONES DISPONIBLES</h2>
                
                <div class="proposition-list">
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
            <span class="year">STATUS</span>
            <span class="days status-badge <?php 
                if ($estado_vacaciones == 'AÚN NO SOLICITADAS') {
                    echo 'status-pendiente'; // Usar el mismo estilo que pendiente
                } else {
                    echo ($estado_vacaciones == 'APROBADA') ? 'status-aprobado' : 
                         (($estado_vacaciones == 'RECHAZADA') ? 'status-rechazado' : 'status-pendiente'); 
                }
            ?>">
                <?php 
                echo $estado_vacaciones; 
                if ($ultima_solicitud && $estado_vacaciones != 'AÚN NO SOLICITADAS') {
                    echo " ({$ultima_solicitud['dias_solicitados']} días)";
                }
                ?>
            </span>
        </div>
                </div>
                
                <!-- Mostrar solicitudes pendientes -->
                <div class="pending-requests">
                    <h3>Solicitudes Pendientes</h3>
                    <?php
                    $solicitudes_pendientes = $conn->query("SELECT * FROM solicitudes 
                                                           WHERE id_empleado = $id_empleado 
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
                        <?php if ($estado_vacaciones != 'AÚN NO SOLICITADAS'): ?>
                            <p>Pero <b>recuerda</b> Si tu status es <b style="color:#6f0;">APROBADA</b> 
                            PORFAVOR acude a RecursosHumanos para que firmes la solicitud de vacaciones. 
                            SI NO FIRMAS TUS VACACIONES EN PAPEL SERAN CANCELADAS EN UNA SEMANA</p>
                        <?php else: ?>
                            <p>Puedes solicitar tus vacaciones desde la sección principal.</p>
                        <?php endif; ?>
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
        
        // Reemplazar el código window.onload actual con:
        window.onload = function() {
            // Solo establecer min en elementos que existen
            const inicioInput = document.getElementById('inicio');
            const finInput = document.getElementById('fin');
            
            if (inicioInput) {
                const today = new Date();
                const minDate = today.toISOString().split('T')[0];
                inicioInput.min = minDate;
            }
            
            if (finInput) {
                const today = new Date();
                const minDate = today.toISOString().split('T')[0];
                finInput.min = minDate;
            }
        };
        
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
            
            // Mostrar loading o deshabilitar botón para evitar múltiples clics
            const submitBtn = document.querySelector('.submit-btn');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Enviando...';
            submitBtn.disabled = true;
            
            fetch('guardar_solicitud.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `inicio=${inicio}&fin=${fin}&dias=${dias}`
            })
            .then(response => {
                // Restaurar botón
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                
                // Verificar si la respuesta es JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Respuesta no JSON:', text);
                        throw new Error('Error del servidor: ' + text);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    alert("Error: " + data.error);
                } else {
                    alert("Solicitud enviada correctamente");
                    window.location.href = "?vista=disponibilidad";
                }
            })
            .catch(error => {
                // Restaurar botón en caso de error también
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                
                console.error('Error completo:', error);
                alert("Error al guardar la solicitud: " + error.message);
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
        
    </script>
</body>
</html>
<?php
// Cerrar conexión
$conn->close();
?>