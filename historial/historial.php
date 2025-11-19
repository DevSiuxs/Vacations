<?php
session_start();
if (!isset($_SESSION['empleado_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../db_config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesar actualización de fechas si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_fechas'])) {
    $solicitud_id = intval($_POST['solicitud_id']);
    $nueva_fecha_inicio = $conn->real_escape_string($_POST['nueva_fecha_inicio']);
    $nueva_fecha_fin = $conn->real_escape_string($_POST['nueva_fecha_fin']);
    
    // Validar que la fecha fin sea mayor o igual a la fecha inicio
    if (strtotime($nueva_fecha_fin) >= strtotime($nueva_fecha_inicio)) {
        // Calcular nuevos días solicitados
        $dias_solicitados = floor((strtotime($nueva_fecha_fin) - strtotime($nueva_fecha_inicio)) / (60 * 60 * 24)) + 1;
        
        $update_query = "UPDATE solicitudes 
                        SET fecha_inicio = '$nueva_fecha_inicio', 
                            fecha_fin = '$nueva_fecha_fin', 
                            dias_solicitados = $dias_solicitados 
                        WHERE id = $solicitud_id";
        
        if ($conn->query($update_query)) {
            $_SESSION['mensaje'] = "Fechas actualizadas correctamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al actualizar las fechas: " . $conn->error;
            $_SESSION['tipo_mensaje'] = "error";
        }
    } else {
        $_SESSION['mensaje'] = "La fecha de fin debe ser igual o posterior a la fecha de inicio";
        $_SESSION['tipo_mensaje'] = "error";
    }
    
    // Redirigir para evitar reenvío del formulario
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Solicitudes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/historial.css">
    <link rel="stylesheet" href="../css/actualizar_vacaciones.css">
    <style>
        /* Estilos para el modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        /* Estilos para el formulario */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input[type="date"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .form-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        #btnCancelar {
            background-color: #6c757d;
            color: white;
        }

        #btnGuardar {
            background-color: #007bff;
            color: white;
        }

        #btnGuardar:hover {
            background-color: #0056b3;
        }

        #btnCancelar:hover {
            background-color: #545b62;
        }

        /* Estilos para mensajes */
        .mensaje {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-weight: bold;
        }

        .mensaje.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Estilos para botón editar */
        .btn-editar {
            background-color: #ffc107;
            color: #212529;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-editar:hover {
            background-color: #e0a800;
        }

        /* Celdas editables */
        .fecha-editable {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .fecha-editable:hover {
            background-color: #f8f9fa;
        }
    </style>
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
                <a href="../logout.php" style="margin-left:15px; text-decoration:none; font-size:24px; color:red; title:cerrar sesion;"><i class="fas fa-power-off"></i></a>
            </li>
        </ul>
    </nav>

    <main>
        <div class="container">
            <h1>Historial de Solicitudes de Vacaciones</h1>
            
            <!-- Mostrar mensajes -->
            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="mensaje <?php echo $_SESSION['tipo_mensaje']; ?>">
                    <?php 
                    echo $_SESSION['mensaje']; 
                    unset($_SESSION['mensaje']);
                    unset($_SESSION['tipo_mensaje']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="filtros">
                <div class="filtro-group">
                    <label for="empleado">Empleado:</label>
                    <select id="empleado">
                        <option value="">Todos los empleados</option>
                        <?php
                        // Obtener lista de empleados
                        $query_empleados = "SELECT id, nombre FROM empleados ORDER BY nombre";
                        $result_empleados = $conn->query($query_empleados);
                        
                        if ($result_empleados && $result_empleados->num_rows > 0) {
                            while ($row = $result_empleados->fetch_assoc()) {
                                echo "<option value='" . $row['id'] . "'>" . $row['nombre'] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="filtro-group">
                    <label for="estado">Estado:</label>
                    <select id="estado">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="aprobada">Aprobada</option>
                        <option value="rechazada">Rechazada</option>
                    </select>
                </div>
                
                <div class="filtro-group">
                    <label for="orden">Ordenar por:</label>
                    <select id="orden">
                        <option value="fecha_inicio DESC">Fecha inicio (más reciente)</option>
                        <option value="fecha_inicio ASC">Fecha inicio (más antigua)</option>
                        <option value="dias_solicitados DESC">Días solicitados (mayor a menor)</option>
                        <option value="dias_solicitados ASC">Días solicitados (menor a mayor)</option>
                    </select>
                </div>
                
                <div class="filtro-group">
                    <label for="limite">Registros a mostrar:</label>
                    <input type="number" id="limite" min="1" value="10">
                </div>
                
                <div class="filtro-group" style="justify-content: flex-end;">
                    <button id="btnFiltrar">Aplicar Filtros</button>
                </div>
            </div>
            
            <div id="resultados">
                <table>
                    <thead>
                        <tr>
                            <th data-order="empleados.nombre">Empleado</th>
                            <th data-order="fecha_inicio">Fecha Inicio</th>
                            <th data-order="fecha_fin">Fecha Fin</th>
                            <th data-order="dias_solicitados">Días Solicitados</th>
                            <th data-order="estado">Estado</th>
                            <th data-order="fecha_aprobacion">Fecha Aprobación/Rechazo</th>
                            <th>Motivo Rechazo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $limite = isset($_GET['limite']) ? intval($_GET['limite']) : 10;
                        $orden = isset($_GET['orden']) ? $_GET['orden'] : 'fecha_inicio DESC';
                        $empleado_id = isset($_GET['empleado_id']) ? $_GET['empleado_id'] : '';
                        $estado = isset($_GET['estado']) ? $_GET['estado'] : '';
                        
                        $query = "SELECT s.*, e.nombre as empleado_nombre, r.motivo as motivo_rechazo 
                                  FROM solicitudes s 
                                  INNER JOIN empleados e ON s.id_empleado = e.id 
                                  LEFT JOIN rechazos_vacaciones r ON s.id = r.id_solicitud 
                                  WHERE 1=1";
                        
                        if (!empty($empleado_id)) {
                            $query .= " AND s.id_empleado = " . intval($empleado_id);
                        }
                        
                        if (!empty($estado)) {
                            $query .= " AND s.estado = '" . $conn->real_escape_string($estado) . "'";
                        }
                        
                        $query .= " ORDER BY " . $conn->real_escape_string($orden) . " LIMIT " . $limite;
                        
                        $result = $conn->query($query);
                        
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $clase_estado = $row['estado'];
                                $fecha_aprobacion = $row['fecha_aprobacion'] ? date('d/m/Y H:i', strtotime($row['fecha_aprobacion'])) : 'N/A';
                                $motivo_rechazo = $row['motivo_rechazo'] ? $row['motivo_rechazo'] : 'N/A';
                                
                                // Solo permitir editar si el usuario es admin o editor, o si es su propia solicitud pendiente
                                $puede_editar = ($_SESSION['empleado_rol'] == 'admin' || 
                                               $_SESSION['empleado_rol'] == 'editor' || 
                                               ($_SESSION['empleado_id'] == $row['id_empleado'] && $row['estado'] == 'pendiente'));
                                
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['empleado_nombre']) . "</td>
                                        <td class='fecha-editable' data-solicitud='" . $row['id'] . "' data-tipo='inicio'>" . date('d/m/Y', strtotime($row['fecha_inicio'])) . "</td>
                                        <td class='fecha-editable' data-solicitud='" . $row['id'] . "' data-tipo='fin'>" . date('d/m/Y', strtotime($row['fecha_fin'])) . "</td>
                                        <td>" . $row['dias_solicitados'] . "</td>
                                        <td><span class='estado $clase_estado'>" . ucfirst($row['estado']) . "</span></td>
                                        <td>" . $fecha_aprobacion . "</td>
                                        <td>" . htmlspecialchars($motivo_rechazo) . "</td>
                                        <td>";
                                
                                if ($puede_editar) {
                                    echo "<button class='btn-editar' data-solicitud='" . $row['id'] . "' title='Editar fechas'>
                                            <i class='fas fa-edit'></i>
                                          </button>";
                                } else {
                                    echo "<button class='btn-editar' disabled title='No tiene permisos para editar'>
                                            <i class='fas fa-edit'></i>
                                          </button>";
                                }
                                
                                echo "</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='no-results'>No se encontraron resultados con los filtros aplicados</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="nav-links">
            <a href="../sistema/index.php">Volver al Inicio</a>
        </div>
    </main>

    <!-- Modal para editar fechas -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Editar Fechas de Solicitud</h2>
            <form id="formEditarFechas" method="POST">
                <input type="hidden" name="solicitud_id" id="solicitud_id">
                <input type="hidden" name="actualizar_fechas" value="1">
                
                <div class="form-group">
                    <label for="nueva_fecha_inicio">Nueva Fecha Inicio:</label>
                    <input type="date" id="nueva_fecha_inicio" name="nueva_fecha_inicio" required>
                </div>
                
                <div class="form-group">
                    <label for="nueva_fecha_fin">Nueva Fecha Fin:</label>
                    <input type="date" id="nueva_fecha_fin" name="nueva_fecha_fin" required>
                </div>
                
                <div class="form-group">
                    <label>Nuevos días solicitados:</label>
                    <span id="nuevos_dias">0</span> días
                </div>
                
                <div class="form-actions">
                    <button type="button" id="btnCancelar">Cancelar</button>
                    <button type="submit" id="btnGuardar">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ========== FILTROS ==========
            // Establecer valores de los filtros desde la URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('empleado_id')) {
                document.getElementById('empleado').value = urlParams.get('empleado_id');
            }
            if (urlParams.has('estado')) {
                document.getElementById('estado').value = urlParams.get('estado');
            }
            if (urlParams.has('orden')) {
                document.getElementById('orden').value = urlParams.get('orden');
            }
            if (urlParams.has('limite')) {
                document.getElementById('limite').value = urlParams.get('limite');
            }
            
            // Aplicar filtros al hacer clic en el botón
            document.getElementById('btnFiltrar').addEventListener('click', function() {
                aplicarFiltros();
            });
            
            // Ordenar al hacer clic en los encabezados de la tabla
            document.querySelectorAll('th[data-order]').forEach(function(th) {
                th.addEventListener('click', function() {
                    const orderBy = this.getAttribute('data-order');
                    const currentOrder = document.getElementById('orden').value;
                    
                    // Determinar si ya está ordenado por este campo y cambiar la dirección
                    if (currentOrder.startsWith(orderBy)) {
                        const parts = currentOrder.split(' ');
                        const newDirection = parts[1] === 'ASC' ? 'DESC' : 'ASC';
                        document.getElementById('orden').value = orderBy + ' ' + newDirection;
                    } else {
                        document.getElementById('orden').value = orderBy + ' DESC';
                    }
                    
                    aplicarFiltros();
                });
            });
            
            function aplicarFiltros() {
                const empleadoId = document.getElementById('empleado').value;
                const estado = document.getElementById('estado').value;
                const orden = document.getElementById('orden').value;
                const limite = document.getElementById('limite').value;
                
                // Construir URL con parámetros
                let url = 'historial.php?';
                if (empleadoId) url += 'empleado_id=' + empleadoId + '&';
                if (estado) url += 'estado=' + estado + '&';
                url += 'orden=' + encodeURIComponent(orden) + '&';
                url += 'limite=' + limite;
                
                // Recargar la página con los nuevos filtros
                window.location.href = url;
            }

            // ========== MODAL EDITAR FECHAS ==========
            const modal = document.getElementById('modalEditar');
            const form = document.getElementById('formEditarFechas');
            const btnCancelar = document.getElementById('btnCancelar');
            const spanClose = document.querySelector('.close');
            const fechaInicioInput = document.getElementById('nueva_fecha_inicio');
            const fechaFinInput = document.getElementById('nueva_fecha_fin');
            const nuevosDiasSpan = document.getElementById('nuevos_dias');

            // Función para calcular días entre dos fechas
            function calcularDias() {
                if (fechaInicioInput.value && fechaFinInput.value) {
                    const inicio = new Date(fechaInicioInput.value);
                    const fin = new Date(fechaFinInput.value);
                    const diferencia = fin.getTime() - inicio.getTime();
                    const dias = Math.floor(diferencia / (1000 * 60 * 60 * 24)) + 1;
                    nuevosDiasSpan.textContent = dias > 0 ? dias : 0;
                }
            }

            // Event listeners para calcular días automáticamente
            fechaInicioInput.addEventListener('change', calcularDias);
            fechaFinInput.addEventListener('change', calcularDias);

            // Abrir modal al hacer clic en botón editar
            document.querySelectorAll('.btn-editar:not(:disabled)').forEach(btn => {
                btn.addEventListener('click', function() {
                    const solicitudId = this.getAttribute('data-solicitud');
                    const fila = this.closest('tr');
                    const fechaInicio = fila.querySelector('td[data-tipo="inicio"]').textContent;
                    const fechaFin = fila.querySelector('td[data-tipo="fin"]').textContent;
                    
                    // Convertir formato de fecha (DD/MM/YYYY to YYYY-MM-DD)
                    function convertirFecha(fechaDDMMYYYY) {
                        const partes = fechaDDMMYYYY.split('/');
                        if (partes.length === 3) {
                            return `${partes[2]}-${partes[1].padStart(2, '0')}-${partes[0].padStart(2, '0')}`;
                        }
                        return fechaDDMMYYYY;
                    }
                    
                    document.getElementById('solicitud_id').value = solicitudId;
                    document.getElementById('nueva_fecha_inicio').value = convertirFecha(fechaInicio);
                    document.getElementById('nueva_fecha_fin').value = convertirFecha(fechaFin);
                    
                    calcularDias(); // Calcular días iniciales
                    modal.style.display = 'block';
                });
            });

            // Cerrar modal
            spanClose.addEventListener('click', () => modal.style.display = 'none');
            btnCancelar.addEventListener('click', () => modal.style.display = 'none');
            window.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });

            // Validar formulario antes de enviar
            form.addEventListener('submit', function(e) {
                const inicio = new Date(fechaInicioInput.value);
                const fin = new Date(fechaFinInput.value);
                
                if (fin < inicio) {
                    e.preventDefault();
                    alert('La fecha de fin debe ser igual o posterior a la fecha de inicio');
                    return false;
                }
                
                return true;
            });
        });
    </script>
</body>
</html>