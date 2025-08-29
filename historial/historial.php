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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Solicitudes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/historial.css">
    <link rel="stylesheet" href="../css/vacaciones.css">
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Consulta inicial para mostrar los datos
                        $limite = isset($_GET['limite']) ? intval($_GET['limite']) : 10;
                        $orden = isset($_GET['orden']) ? $_GET['orden'] : 'fecha_inicio DESC';
                        $empleado_id = isset($_GET['empleado_id']) ? $_GET['empleado_id'] : '';
                        $estado = isset($_GET['estado']) ? $_GET['estado'] : '';
                        
                        // Construir la consulta con los filtros
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
                                
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['empleado_nombre']) . "</td>
                                        <td>" . date('d/m/Y', strtotime($row['fecha_inicio'])) . "</td>
                                        <td>" . date('d/m/Y', strtotime($row['fecha_fin'])) . "</td>
                                        <td>" . $row['dias_solicitados'] . "</td>
                                        <td><span class='estado $clase_estado'>" . ucfirst($row['estado']) . "</span></td>
                                        <td>" . $fecha_aprobacion . "</td>
                                        <td>" . htmlspecialchars($motivo_rechazo) . "</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='no-results'>No se encontraron resultados con los filtros aplicados</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <a href="../sistema/index.php" class="solicitar-btn" style="background: #ff0000ff;">Regresar</a>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
</body>
</html>