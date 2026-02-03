<?php
session_start();
if (!isset($_SESSION['empleado_id'])) {
    header("Location: ../login.php");
    exit();
}
// Configuración de la base de datos

require_once '../db_config.php'; // Archivo con configuración de BDciones";

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

// --- NUEVA LÓGICA DE DÍAS BLOQUEADOS ---
$dias_bloqueados = 0;
// 1. Primero obtenemos los datos base (MUEVE ESTO HACIA ARRIBA)
$vacaciones = getVacaciones($conn, $empleado_id);
$a_disfrutar = $vacaciones['dias_totales'] - $vacaciones['dias_asignados'] - $vacaciones['dias_disfrutados'];

// 2. Luego ejecutamos la lógica de bloqueados que ya tenías
// --- LÓGICA DE DÍAS BLOQUEADOS ACTUALIZADA ---
$dias_bloqueados = 0;
// Ahora sumamos aprobadas Y pendientes que no han sido procesadas
$sql_bloqueados = "SELECT SUM(dias_solicitados) as bloqueados
                   FROM solicitudes
                   WHERE id_empleado = $empleado_id
                   AND estado IN ('aprobada', 'pendiente')
                   AND id NOT IN (SELECT id_solicitud FROM vacaciones_disfrutadas)";

$res_bloqueados = $conn->query($sql_bloqueados);
if ($res_bloqueados) {
    $row_b = $res_bloqueados->fetch_assoc();
    $dias_bloqueados = $row_b['bloqueados'] ?? 0;
}

$saldo_real_para_pedir = $a_disfrutar - $dias_bloqueados;

//validacion de dias bloqueados termina aqui

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
    <link rel="stylesheet" href="../css/vacaciones.css">
    <!-- SOLICITUDES PENDIENTES -->
    <link rel="stylesheet" href="../css/pending.css">
    <!-- ver_dias_ocupados  CALENDARIO-->
    <link rel="stylesheet" href="../css/ver_dias_ocupados.css">
    <!-- cancelacion_motivo -->
    <link rel="stylesheet" href="../css/cancelacion_motivo.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>
    <nav>
        <ul>
            <li>
                <img src="../Assets/icons/Uranus.ico" alt="Logo Preisa">
            <li>
                <span style="color: white; padding-left: 0.8rem;"><?php echo $_SESSION['empleado_nombre']; ?> <br>
                <b style="padding-left: 0.8rem;"><a style="color: white; text-decoration:none; cursor:pointer;" href="?vista=mostrarVacaciones">Regresar a inicio</a></b></span>
            </li>
            </li>
            <li style="margin-left: auto;">
                <a href="../logout.php" style="margin-left:15px; text-decoration:none; title:cerrar sesion;"><img
                        src="../Assets/icons/power-icon.webp" style="border-radius: 50%; border:solid 3px red;"></a>
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
            global $empleado, $vacaciones, $anios_laborales, $a_disfrutar, $dias_bloqueados;
            ?>
        <div class="container">
            <h1 style="color:white;">Bienvenido, <b style="color:white;"><?php echo $empleado['nombre']; ?></b></h1>

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
                    <a href="../table_vacaciones/tabla.php"><span class="stat-label asg">ASIGNADOS</span></a>
                </div>
                <div class="stat-box">
                    <span class="stat-number"><?php echo $vacaciones['dias_disfrutados']; ?></span>
                    <span class="stat-label">DISFRUTO</span>
                </div>
                <div class="stat-box">
                    <span class="stat-number"><?php echo $a_disfrutar; ?></span>
                    <span class="stat-label">A DISFRUTAR</span>
                </div>
                <!-- <div class="stat-box" style="background: #ffc107; color: black;">
                    <span class="stat-number"><?php echo $dias_bloqueados; ?></span>
                    <span class="stat-label">BLOQUEADOS</span>
                    <small style="font-size: 0.7em;">(Ya aprobados)</small>
                    </div> -->
            </div>

            <div class="roles-buttons">
                <a href="?vista=solicitud" class="role-btn" style="background: #00ff00ff;">
                    <i class="fas fa-umbrella-beach fa-bounce" style="--fa-animation-duration: 2s;"></i> Solicitar
                    Vacaciones
                </a>
                <a href="?vista=disponibilidad" class="role-btn"
                    style="background: linear-gradient(to right, #00f7ffff, #4a00e0);">
                    <i class="fas fa-calendar-check fa-shake" style="--fa-animation-duration: 5s;"></i> Ver
                    Disponibilidad
                </a>

                <!-- Solo mostrar RH para admin y editor -->
                <?php if ($_SESSION['empleado_rol'] === 'admin' || $_SESSION['empleado_rol'] === 'editor'): ?>
                <a href="?vista=rh_periodos" class="role-btn" style="background: #ff0000ff;">
                    <i class="fas fa-users-cog fa-shake" style="--fa-animation-duration: 8s;"></i> RH
                </a>
                <?php endif; ?>

                <!-- Solo mostrar Historial para admin y editor -->
                <?php if ($_SESSION['empleado_rol'] === 'admin' || $_SESSION['empleado_rol'] === 'editor'): ?>
                <a href="../historial/historial.php" class="role-btn" style="background: #ffbb00ff;">
                    <i class="fas fa-history fa-spin" style="--fa-animation-duration: 5s;"></i> Historial
                </a>
                <?php endif; ?>

                <!-- Solo mostrar Días Especiales para admin y editor -->
                <?php if ($_SESSION['empleado_rol'] === 'admin' || $_SESSION['empleado_rol'] === 'editor'): ?>
                <a href="../actualizar_datos/actualizar.php" class="role-btn" style="background: #826e6c97;">
                    <i class="fas fa-calendar fa-beat" style="--fa-animation-duration: 2s;"></i> Dias Especiales
                </a>
                <?php endif; ?>

                <?php if ($_SESSION['empleado_rol'] === 'admin' || $_SESSION['empleado_rol'] === 'editor'): ?>
                <a href="../eliminar_usuarios/eliminar.php" class="role-btn" style="background: #8000ff97;">
                    <i class="fas fa-user-times fa-fade" style="--fa-animation-duration: 2s;"></i> Eliminar Usuarios
                </a>
                <?php endif; ?>

                <?php if ($_SESSION['empleado_rol'] === 'admin' || $_SESSION['empleado_rol'] === 'editor'): ?>
                <a href="../roles/roles.php" class="role-btn" style="background: #000000;">
                    <i class="fas fa-user-shield fa-beat-fade" style="--fa-animation-duration: 3s;"></i> Log-in Roles
                </a>
                <?php endif; ?>

                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

            </div>
        </div>
        <?php
        }
function mostrarSolicitud() {
    global $a_disfrutar, $conn;
    $id_empleado = $_SESSION['empleado_id'];

    // Calcular fecha mínima (hoy + 14 días)
    // $fechaMinima = date('Y-m-d', strtotime('+14 days'));

    $resultBloqueadas = $conn->query("SELECT fecha_inicio, fecha_fin
                           FROM solicitudes
                           WHERE id_empleado = $id_empleado
                           AND (estado = 'aprobada' OR estado = 'pendiente')");

    $fechasBloqueadas = [];
    while ($row = $resultBloqueadas->fetch_assoc()) {
        $fechasBloqueadas[] = [
            'start' => $row['fecha_inicio'],
            'end' => $row['fecha_fin']
        ];
    }

    // Obtener TODAS las fechas ocupadas de TODOS los usuarios (aprobadas y pendientes) CON SU PUESTO
    $resultOcupadas = $conn->query("SELECT s.fecha_inicio, s.fecha_fin, e.puesto
                               FROM solicitudes s
                               JOIN empleados e ON s.id_empleado = e.id
                               WHERE (s.estado = 'aprobada' OR s.estado = 'pendiente')");

    $fechasOcupadas = [];
    while ($row = $resultOcupadas->fetch_assoc()) {
        $fechasOcupadas[] = [
            'start' => $row['fecha_inicio'],
            'end' => $row['fecha_fin'],
            'puesto' => $row['puesto'] // ← Agregar puesto aquí
        ];
    }

    // Convertir a JSON para usar en JavaScript
    $fechasBloqueadasJson = json_encode($fechasBloqueadas);
    $fechasOcupadasJson = json_encode($fechasOcupadas);
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


                <div class="form-group" style="text-align: center; margin-top: 20px;">
                    <button type="button" class="solicitar-btn" onclick="mostrarCalendarioOcupados()"
                        style="background: #6c757d; padding: 10px 20px;">
                        <i class="fas fa-calendar-alt"></i> Ver Días Ocupados
                    </button>
                </div>

                <button type="button" class="submit-btn" onclick="enviarSolicitud()">Enviar Solicitud</button>
                <a href="?vista=vacaciones" class="solicitar-btn" style="background: #ff0000ff;">Cancelar</a>
            </form>

            <!-- Modal para mostrar calendario con días ocupados -->
            <div id="modalCalendario" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="cerrarModal()">&times;</span>
                    <h3>Calendario de Días Ocupados</h3>
                    <div id="calendarioOcupados"></div>
                    <div style="margin-top: 15px;">
                        <span
                            style="display: inline-block; width: 15px; height: 15px; background-color: #ffcccc; margin-right: 5px;"></span>
                        <span>Días ocupados (aprobados o pendientes)</span>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // Fechas bloqueadas (aprobadas y pendientes del usuario actual)
        const fechasBloqueadas = <?php echo $fechasBloqueadasJson; ?>;

        // Todas las fechas ocupadas de todos los usuarios
        const fechasOcupadas = <?php echo $fechasOcupadasJson; ?>;

        function validarFechasNoBloqueadas(inicio, fin) {
            const inicioDate = new Date(inicio);
            const finDate = new Date(fin);

            // Primero validar contra las propias solicitudes del usuario
            for (const bloqueo of fechasBloqueadas) {
                const bloqueoInicio = new Date(bloqueo.start);
                const bloqueoFin = new Date(bloqueo.end);

                if (
                    (inicioDate >= bloqueoInicio && inicioDate <= bloqueoFin) ||
                    (finDate >= bloqueoInicio && finDate <= bloqueoFin) ||
                    (inicioDate <= bloqueoInicio && finDate >= bloqueoFin)
                ) {
                    return {
                        valido: false,
                        mensaje: 'Estas fechas se solapan con tus propias solicitudes'
                    };
                }
            }

            // Luego verificar con el servidor para empleados del MISMO PUESTO
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
                        return {
                            valido: true
                        };
                    } else {
                        return {
                            valido: false,
                            mensaje: data.mensaje ||
                                'Estas fechas ya están ocupadas por otro empleado del mismo puesto'
                        };
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    return {
                        valido: true
                    }; // En caso de error, permitir continuar
                });
        }

        // Modal functions
        function mostrarCalendarioOcupados() {
            document.getElementById('modalCalendario').style.display = 'block';
            generarCalendario();
        }

        function cerrarModal() {
            document.getElementById('modalCalendario').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera del contenido
        window.onclick = function(event) {
            const modal = document.getElementById('modalCalendario');
            if (event.target == modal) {
                cerrarModal();
            }
        }

        // Función para generar el calendario con días ocupados
        function generarCalendario() {
            const calendarioDiv = document.getElementById('calendarioOcupados');
            calendarioDiv.innerHTML = '';

            // Obtener el mes y año actual
            const hoy = new Date();
            let mesActual = hoy.getMonth();
            let añoActual = hoy.getFullYear();

            // Calcular la fecha mínima para vacaciones (15 días después de hoy)
            const fechaMinimaVacaciones = new Date(hoy);
            fechaMinimaVacaciones.setDate(hoy.getDate() + 15);
            const fechaMinimaStr = fechaMinimaVacaciones.toISOString().split('T')[0];

            // Crear calendario para los próximos 12 meses
            for (let i = 0; i < 12; i++) {
                const mes = (mesActual + i) % 12;
                const año = añoActual + Math.floor((mesActual + i) / 12);

                // Crear tabla de calendario para este mes
                const tabla = document.createElement('table');
                tabla.className = 'calendario-mes';

                // Encabezado con nombre del mes y año
                const nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ];
                const thead = document.createElement('thead');
                const trHeader = document.createElement('tr');
                const thHeader = document.createElement('th');
                thHeader.colSpan = 7;
                thHeader.textContent = `${nombresMeses[mes]} ${año}`;
                thHeader.style.textAlign = 'center';
                thHeader.style.padding = '10px';
                thHeader.style.backgroundColor = '#f0f0f0';
                trHeader.appendChild(thHeader);
                thead.appendChild(trHeader);
                tabla.appendChild(thead);

                // Días de la semana
                const trDias = document.createElement('tr');
                const diasSemana = ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá', 'Do'];
                diasSemana.forEach(dia => {
                    const th = document.createElement('th');
                    th.textContent = dia;
                    th.style.padding = '5px';
                    trDias.appendChild(th);
                });
                thead.appendChild(trDias);

                // Cuerpo del calendario
                const tbody = document.createElement('tbody');

                // Primer día del mes
                const primerDia = new Date(año, mes, 1);
                // Último día del mes
                const ultimoDia = new Date(año, mes + 1, 0);

                // Día de la semana del primer día (0 = Domingo, 1 = Lunes, ...)
                let diaSemana = primerDia.getDay();
                // Ajustar para que la semana comience en Lunes (1)
                diaSemana = diaSemana === 0 ? 6 : diaSemana - 1;

                let tr = document.createElement('tr');

                // Celdas vacías antes del primer día
                for (let j = 0; j < diaSemana; j++) {
                    const td = document.createElement('td');
                    tr.appendChild(td);
                }

                // Días del mes
                for (let dia = 1; dia <= ultimoDia.getDate(); dia++) {
                    const fechaActual = new Date(año, mes, dia);
                    const fechaStr = fechaActual.toISOString().split('T')[0];

                    const td = document.createElement('td');
                    td.textContent = dia;
                    td.style.padding = '5px';
                    td.style.textAlign = 'center';

                    const estaOcupada = fechasOcupadas.some(periodo => {
                        const inicioStr = periodo.start;
                        const finStr = periodo.end;
                        const puestoOcupante = periodo.puesto;

                        // Si quieres diferenciar colores por puesto:
                        if (fechaStr >= inicioStr && fechaStr <= finStr) {
                            // Puedes asignar diferentes colores según el puesto
                            if (puestoOcupante === 'operativo') {
                                td.style.backgroundColor = '#ff0000ff'; // Rojo claro para operativos
                            } else {
                                td.style.backgroundColor = '#0000ffff'; // Azul claro para administrativos
                            }
                            td.title = `Día ocupado por ${puestoOcupante}`;
                            return true;
                        }
                        return false;
                    });

                    if (estaOcupada) {
                        td.style.backgroundColor = '#ffcccc';
                        td.title = 'Día ocupado (aprobado o pendiente)';
                    }

                    // Verificar si la fecha es 15 días después de hoy (disponible para vacaciones)
                    if (fechaStr === fechaMinimaStr) {
                        if (!estaOcupada) {
                            td.style.backgroundColor = '#ccffcc';
                            td.title = 'Día disponible para vacaciones';
                        }
                    }

                    // Marcar el día actual
                    if (fechaActual.toDateString() === hoy.toDateString()) {
                        td.style.border = '2px solid #00ff2aff';
                    }

                    tr.appendChild(td);

                    // Si es domingo (último día de la semana), crear nueva fila
                    if ((diaSemana + dia) % 7 === 0) {
                        tbody.appendChild(tr);
                        tr = document.createElement('tr');
                    }
                }

                // Añadir la última fila si tiene contenido
                if (tr.cells.length > 0) {
                    tbody.appendChild(tr);
                }

                tabla.appendChild(tbody);
                calendarioDiv.appendChild(tabla);
            }
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

                if (fechaFin < fechaInicio) {
                    alert("La fecha de fin debe ser posterior a la de inicio");
                    document.getElementById('dias').value = 0;
                    return;
                }

                // IMPORTANTE: Agregar esta validación que falta
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
            <a href="?vista=mostrarVacaciones" class="next-btn">Regresar</a>
            <h1>PERIODOS DE VACACIONES</h1>

            <?php if ($solicitudes->num_rows > 0): ?>
            <div class="solicitudes-grid">

                <?php while($solicitud = $solicitudes->fetch_assoc()): ?>
                <div class="employee-periods">
                    <div class="employee-header">
                        <span><?php echo $solicitud['nombre']; ?></span>
                    </div>

                    <div class="period-info">
                        <div class="info-item">
                            <span class="label">Periodo</span>
                            <span class="value">
                                <?php echo date('d M', strtotime($solicitud['fecha_inicio'])); ?> al
                                <?php echo date('d M', strtotime($solicitud['fecha_fin'])); ?>
                            </span>
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
                        <button class="action-btn accept-btn"
                            onclick="aprobarSolicitud(<?php echo $solicitud['id']; ?>)">
                            <i>✓</i> Aceptar
                        </button>
                        <button class="action-btn cancel-btn"
                            onclick="mostrarModalRechazo(<?php echo $solicitud['id']; ?>)">
                            <i>✕</i> Cancelar
                        </button>
                    </div>

                </div>
                <?php endwhile; ?>

            </div> <?php else: ?>
            <p style="text-align: center; color: white;">No hay solicitudes pendientes.</p>
            <?php endif; ?>
        </div>

        <!-- <a href="?vista=disponibilidad" class="next-btn">Siguiente</a> -->

        <!-- Modal para motivo de rechazo -->
        <div id="modalRechazo" class="modal">
            <div class="modal-content">
                <span class="close" onclick="cerrarModalRechazo()">&times;</span>
                <h3>Motivo del Rechazo</h3>
                <form id="formRechazo">
                    <input type="hidden" id="idSolicitudRechazo">
                    <div class="form-group">
                        <label for="motivoRechazo">Por favor, indica el motivo del rechazo:</label>
                        <textarea id="motivoRechazo" rows="4" required></textarea>
                    </div>
                    <button type="button" class="solicitar-btn" onclick="rechazarSolicitudConMotivo()">Enviar
                        Rechazo</button>
                </form>
            </div>
        </div>
        </div>
        <?php
}

        function mostrarDisponibilidad() {
    global $empleado, $vacaciones, $conn;
    $id_empleado = $_SESSION['empleado_id']; // Usar el ID de la sesión

    // Calcular días teóricos disponibles
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

    // Obtener el estado más reciente
    $estado_vacaciones = "AÚN NO SOLICITADAS"; // Valor por defecto
    $result_estado = $conn->query("SELECT estado FROM solicitudes
                                 WHERE id_empleado = $id_empleado
                                 ORDER BY id DESC LIMIT 1");
    if ($result_estado->num_rows > 0) {
        $row_estado = $result_estado->fetch_assoc();
        $estado_vacaciones = strtoupper($row_estado['estado']);
    }

    // Obtener motivo de rechazo si la última solicitud fue rechazada
    $motivo_rechazo = "";
    if ($estado_vacaciones == 'RECHAZADA' && $ultima_solicitud) {
        $result_motivo = $conn->query("SELECT motivo FROM rechazos_vacaciones
                                     WHERE id_solicitud = {$ultima_solicitud['id']}
                                     ORDER BY id DESC LIMIT 1");
        if ($result_motivo->num_rows > 0) {
            $row_motivo = $result_motivo->fetch_assoc();
            $motivo_rechazo = $row_motivo['motivo'];
        }
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

            <!-- Mostrar motivo de rechazo si aplica -->
            <?php if ($estado_vacaciones == 'RECHAZADA' && !empty($motivo_rechazo)): ?>
            <div class="rechazo-info">
                <h3>Motivo del rechazo:</h3>
                <p><?php echo htmlspecialchars($motivo_rechazo); ?></p>
            </div>
            <?php endif; ?>

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
                        <span><?php echo date('d/m/Y', strtotime($solicitud['fecha_inicio'])); ?> -
                            <?php echo date('d/m/Y', strtotime($solicitud['fecha_fin'])); ?></span>
                        <span><?php echo $solicitud['dias_solicitados']; ?> días</span>
                        <span class="status-badge status-pendiente">PENDIENTE</span>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <?php if ($estado_vacaciones == 'APROBADA'): ?>
                <p>Pero <b>recuerda</b> Si tu status es <b style="color:#6f0;">APROBADA</b>
                    PORFAVOR acude a RecursosHumanos para que firmes la solicitud de vacaciones.
                    SI NO FIRMAS TUS VACACIONES EN PAPEL SERAN CANCELADAS EN UNA SEMANA</p>
                <?php elseif ($estado_vacaciones == 'RECHAZADA'): ?>
                <p>Tu última solicitud de vacaciones fue <b style="color:red;">RECHAZADA</b>.
                    Puedes ver el motivo arriba y solicitar un nuevo período si lo deseas.</p>
                <?php else: ?>
                <p>Puedes solicitar tus vacaciones desde la sección principal.</p>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="footer">
                <a href="?vista=vacaciones">Regresar a inicio</a>
            </div>
        </div>
        <?php
}
        ?>
    </main>

    <script>
    // Pasamos las variables de PHP a JavaScript de forma segura
    const diasDisponibles = <?php echo (int)$a_disfrutar; ?>;
    const SALDO_DISPONIBLE_REAL = <?php echo (int)$saldo_real_para_pedir; ?>;

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

        // Obtenemos el texto del elemento visual (por si acaso)
        const disponiblesElement = document.getElementById('dias-disponibles');
        const disponiblesVisual = disponiblesElement ? parseInt(disponiblesElement.textContent) : 0;

        console.log('Validando solicitud:', {
            inicio,
            fin,
            dias,
            saldoReal: SALDO_DISPONIBLE_REAL
        });

        // 1. Validación de campos vacíos
        if (!inicio || !fin || !dias || dias <= 0) {
            alert("Por favor completa todos los campos correctamente");
            return;
        }

        // 2. CANDADO VISUAL: No dejar pedir más de lo que muestra el saldo "A disfrutar"
        if (dias > disponiblesVisual) {
            alert(`No tienes suficientes días disponibles.\nEn saldo: ${disponiblesVisual}\nSolicitados: ${dias}`);
            return;
        }

        // 3. CANDADO LÓGICO: Validar contra los días BLOQUEADOS (pendientes/aprobados)
        if (dias > SALDO_DISPONIBLE_REAL) {
            alert("¡BLOQUEO DE SEGURIDAD!\n\nNo puedes pedir " + dias +
                " días porque ya tienes solicitudes pendientes o aprobadas que agotan tu saldo.\n\nSaldo libre real: " +
                SALDO_DISPONIBLE_REAL + " días.");
            return;
        }

        // Bloquear botón para evitar doble envío
        const submitBtn = document.querySelector('.submit-btn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Enviando...';
        submitBtn.disabled = true;

        const formData = new URLSearchParams();
        formData.append('inicio', inicio);
        formData.append('fin', fin);
        formData.append('dias', dias);

        fetch('guardar_solicitud.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            })
            .then(response => {
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Respuesta no válida del servidor: ' + text);
                    }
                });
            })
            .then(data => {
                if (data.error) {
                    // Aquí se mostrará el error que viene del PHP (el candado del servidor)
                    alert("Error del sistema: " + data.error);
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                } else {
                    alert("Solicitud enviada correctamente");
                    window.location.href = "?vista=disponibilidad";
                }
            })
            .catch(error => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                console.error('Error:', error);
                alert("Error: " + error.message);
            });
    }

    // --- Funciones de Administración (Aprobar/Rechazar) ---

    function aprobarSolicitud(id) {
        if (confirm("¿Estás seguro de aprobar esta solicitud de vacaciones?")) {
            fetch('aprobar_solicitud.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}&accion=aprobar`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert("Error: " + data.error);
                    } else {
                        alert(data.success);
                        location.reload();
                    }
                })
                .catch(error => alert("Error en la comunicación: " + error.message));
        }
    }

    function mostrarModalRechazo(idSolicitud) {
        document.getElementById('idSolicitudRechazo').value = idSolicitud;
        document.getElementById('modalRechazo').style.display = 'block';
    }

    function cerrarModalRechazo() {
        document.getElementById('modalRechazo').style.display = 'none';
        document.getElementById('motivoRechazo').value = '';
    }

    function rechazarSolicitudConMotivo() {
        const idSolicitud = document.getElementById('idSolicitudRechazo').value;
        const motivo = document.getElementById('motivoRechazo').value;

        if (!motivo.trim()) {
            alert('Por favor, ingresa el motivo del rechazo');
            return;
        }

        if (confirm("¿Estás seguro de rechazar esta solicitud?")) {
            fetch('aprobar_solicitud.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${idSolicitud}&accion=rechazar&motivo=${encodeURIComponent(motivo)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert("Error: " + data.error);
                    } else {
                        alert(data.success);
                        cerrarModalRechazo();
                        location.reload();
                    }
                })
                .catch(error => alert("Error: " + error.message));
        }
    }
    </script>
</body>

</html>
<?php
$conn->close();
?>
