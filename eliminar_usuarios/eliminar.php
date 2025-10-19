<?php
session_start();
if (!isset($_SESSION['empleado_id']) || ($_SESSION['empleado_rol'] !== 'admin' && $_SESSION['empleado_rol'] !== 'editor')) {
    header("Location: ../login.php");
    exit();
}

require_once '../db_config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_usuario'])) {
    $usuario_id = intval($_POST['usuario_id']);
    
    // Verificar que no sea auto-eliminación
    if ($usuario_id == $_SESSION['empleado_id']) {
        $mensaje = "No puedes eliminarte a ti mismo.";
        $tipo_mensaje = "error";
    } else {
        // Iniciar transacción para asegurar la integridad de los datos
        $conn->begin_transaction();
        
        try {
            // 1. Eliminar registros en vacaciones_disfrutadas relacionados con las solicitudes
            $stmt1 = $conn->prepare("
                DELETE vd FROM vacaciones_disfrutadas vd 
                INNER JOIN solicitudes s ON vd.id_solicitud = s.id 
                WHERE s.id_empleado = ?
            ");
            $stmt1->bind_param("i", $usuario_id);
            $stmt1->execute();
            $stmt1->close();
            
            // 2. Eliminar registros en rechazos_vacaciones relacionados con las solicitudes
            $stmt2 = $conn->prepare("
                DELETE rv FROM rechazos_vacaciones rv 
                INNER JOIN solicitudes s ON rv.id_solicitud = s.id 
                WHERE s.id_empleado = ?
            ");
            $stmt2->bind_param("i", $usuario_id);
            $stmt2->execute();
            $stmt2->close();
            
            // 3. Eliminar solicitudes del usuario
            $stmt3 = $conn->prepare("DELETE FROM solicitudes WHERE id_empleado = ?");
            $stmt3->bind_param("i", $usuario_id);
            $stmt3->execute();
            $stmt3->close();
            
            // 4. Eliminar registro de vacaciones del usuario
            $stmt4 = $conn->prepare("DELETE FROM vacaciones WHERE id_empleado = ?");
            $stmt4->bind_param("i", $usuario_id);
            $stmt4->execute();
            $stmt4->close();
            
            // 5. Finalmente eliminar el usuario
            $stmt5 = $conn->prepare("DELETE FROM empleados WHERE id = ?");
            $stmt5->bind_param("i", $usuario_id);
            $stmt5->execute();
            
            if ($stmt5->affected_rows > 0) {
                $conn->commit();
                $mensaje = "Usuario eliminado correctamente.";
                $tipo_mensaje = "exito";
            } else {
                $conn->rollback();
                $mensaje = "No se encontró el usuario o ya fue eliminado.";
                $tipo_mensaje = "error";
            }
            $stmt5->close();
            
        } catch (Exception $e) {
            $conn->rollback();
            $mensaje = "Error al eliminar el usuario: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}
// Obtener lista de usuarios
$sql = "SELECT id, nombre, puesto, fecha_ingreso, rol FROM empleados ORDER BY nombre";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Usuarios - Sistema de Vacaciones</title>
    <link rel="stylesheet" href="../css/actualizar_vacaciones.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #1e1e1e;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #333;
        }

        nav {
            background-color: #30303000;
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
        }

        nav img {
            height: 50px;
            border-radius: 25%;
            border: 1px solid #fff;
        }

        .back-btn {
            color: white;
            text-decoration: none;
            font-size: 24px;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .back-btn:hover {
            transform: scale(1.1);
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
            max-width: 1000px;
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

        .search-container {
            display: flex;
            margin-bottom: 20px;
            gap: 10px;
        }

        .search-container input {
            flex: 1;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            font-size: 16px;
        }

        .search-container button {
            padding: 12px 20px;
            background: #6dd5ed;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .users-table th, .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .users-table th {
            background: #1a2aca;;
            color: white;
        }

        .users-table tr:hover {
            background-color: #f8f9fa;
        }

        .delete-btn {
            background: linear-gradient(to right, #ed213a, #93291e);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .delete-btn:hover {
            opacity: 0.9;
        }

        .no-results {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .close {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
        }

        .confirmation-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .confirmation-buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .confirm-delete {
            background: linear-gradient(to right, #ed213a, #93291e);
            color: white;
        }

        .cancel-delete {
            background: linear-gradient(to right, #6c757d, #495057);
            color: white;
        }

        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
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

        @media (max-width: 768px) {
            .users-table {
                display: block;
                overflow-x: auto;
            }
            
            .search-container {
                flex-direction: column;
            }
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
            <h1>Eliminar Usuarios</h1>
            
            <?php if (isset($mensaje)): ?>
                <div class="message <?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Buscar por nombre, puesto o rol..." onkeyup="filtrarUsuarios()">
                <button onclick="filtrarUsuarios()"><i class="fas fa-search"></i> Buscar</button>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Puesto</th>
                            <th>Fecha de Ingreso</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <?php while($usuario = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $usuario['id']; ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['puesto']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($usuario['fecha_ingreso'])); ?></td>
                                <td><?php echo ucfirst($usuario['rol']); ?></td>
                                <td>
                                    <?php if ($usuario['id'] != $_SESSION['empleado_id']): ?>
                                        <button class="delete-btn" onclick="confirmarEliminacion(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nombre']); ?>')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    <?php else: ?>
                                        <span style="color: #6c757d;">No disponible</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-results">
                    <p>No hay usuarios registrados en el sistema.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Modal de confirmación -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2>Confirmar Eliminación</h2>
            <p>¿Estás seguro de que deseas eliminar al usuario: <span id="userToDelete"></span>?</p>
            <p>Esta acción no se puede deshacer.</p>
            <form id="deleteForm" method="POST" action="">
                <input type="hidden" name="usuario_id" id="usuarioId">
                <input type="hidden" name="eliminar_usuario" value="1">
                <div class="confirmation-buttons">
                    <button type="button" class="confirm-delete" onclick="document.getElementById('deleteForm').submit()">Eliminar</button>
                    <button type="button" class="cancel-delete" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    <div class="nav-links">
            <a href="../sistema/index.php">Volver al Inicio</a>
        </div>

    <script>
        // Función para filtrar usuarios en la tabla
        function filtrarUsuarios() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('usersTableBody');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let mostrarFila = false;
                
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j]) {
                        const txtValue = cells[j].textContent || cells[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            mostrarFila = true;
                            break;
                        }
                    }
                }
                
                rows[i].style.display = mostrarFila ? "" : "none";
            }
        }
        
        // Función para confirmar eliminación
        function confirmarEliminacion(usuarioId, usuarioNombre) {
            document.getElementById('userToDelete').textContent = usuarioNombre;
            document.getElementById('usuarioId').value = usuarioId;
            document.getElementById('confirmationModal').style.display = 'block';
        }
        
        // Función para cerrar el modal
        function cerrarModal() {
            document.getElementById('confirmationModal').style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera del contenido
        window.onclick = function(event) {
            const modal = document.getElementById('confirmationModal');
            if (event.target == modal) {
                cerrarModal();
            }
        }
    </script>
</body>
</html>

<?php
// Cerrar conexión
$conn->close();
?>