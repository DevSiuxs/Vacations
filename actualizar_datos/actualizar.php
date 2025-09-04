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
    <title>Actualizar Datos de Vacaciones - Preisa</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #1a2a6c, #a92222, #fdbb2d);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #333;
        }
        
        nav {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }
        
        h1, h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #1a2a6c;
        }
        
        .filtro {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            gap: 10px;
        }
        
        .filtro input, .filtro select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 250px;
        }
        
        .filtro button {
            padding: 10px 15px;
            background-color: #1a2a6c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .filtro button:hover {
            background-color: #2a3a9c;
        }
        
        .tabla-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f0f0f0;
            position: sticky;
            top: 0;
        }
        
        tr:hover {
            background-color: rgba(76, 175, 80, 0.1);
        }
        
        .acciones {
            display: flex;
            gap: 10px;
        }
        
        .btn-editar {
            padding: 5px 10px;
            background-color: #45a049;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .btn-guardar {
            padding: 5px 10px;
            background-color: #1a2a6c;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .btn-cancelar {
            padding: 5px 10px;
            background-color: #a92222;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        input[type="number"] {
            width: 60px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: rgba(76, 175, 80, 0.4);
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .nav-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            justify-content: center;
        }
        
        .nav-links a {
            padding: 10px 15px;
            background-color: #1a2a6c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        
        .nav-links a:hover {
            background-color: #2a3a9c;
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
    
    <div class="container">
        <h1>ACTUALIZAR DATOS DE VACACIONES</h1>
        
        <?php
        // Mostrar mensajes de éxito o error
        if (isset($_GET['success'])) {
            echo '<div class="alert">' . htmlspecialchars($_GET['success']) . '</div>';
        }
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-error">' . htmlspecialchars($_GET['error']) . '</div>';
        }
        ?>
        
        <div class="filtro">
            <input type="text" id="buscarNombre" placeholder="Buscar por nombre...">
            <select id="filtroPuesto">
                <option value="">Todos los puestos</option>
                <option value="OPERADOR FUNERARIO">OPERADOR FUNERARIO</option>
                <!-- Agrega más opciones según los puestos existentes -->
            </select>
            <button onclick="filtrarEmpleados()">Buscar</button>
        </div>
        
        <div class="tabla-container">
            <table id="tablaVacaciones">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Puesto</th>
                        <th>Días Totales</th>
                        <th>Días Asignados</th>
                        <th>Días Disfrutados</th>
                        <th>A Disfrutar</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Conexión a la base de datos
                    require_once '../db_config.php';
                    $conn = new mysqli($servername, $username, $password, $dbname);
                    
                    if ($conn->connect_error) {
                        die("Conexión fallida: " . $conn->connect_error);
                    }
                    
                    // Consulta para obtener los datos
                    $sql = "SELECT e.id, e.nombre, e.puesto, v.dias_totales, v.dias_asignados, v.dias_disfrutados, 
                            (v.dias_totales - v.dias_asignados - v.dias_disfrutados) as a_disfrutar
                            FROM empleados e
                            JOIN vacaciones v ON e.id = v.id_empleado
                            ORDER BY e.nombre";
                    
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr data-id='{$row['id']}'>
                                <td>{$row['id']}</td>
                                <td>{$row['nombre']}</td>
                                <td>{$row['puesto']}</td>
                                <td class='editable' data-field='dias_totales'>{$row['dias_totales']}</td>
                                <td class='editable' data-field='dias_asignados'>{$row['dias_asignados']}</td>
                                <td class='editable' data-field='dias_disfrutados'>{$row['dias_disfrutados']}</td>
                                <td>{$row['a_disfrutar']}</td>
                                <td class='acciones'>
                                    <button class='btn-editar' onclick='habilitarEdicion(this)'>Editar</button>
                                    <button class='btn-guardar' onclick='guardarCambios({$row['id']})' style='display:none;'>Guardar</button>
                                    <button class='btn-cancelar' onclick='cancelarEdicion(this)' style='display:none;'>Cancelar</button>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>No se encontraron registros</td></tr>";
                    }
                    
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
        
        <div class="nav-links">
            <a href="../sistema/index.php">Volver al Inicio</a>
            <a href="#" onclick="actualizarDisfrutados()">Actualizar Días Disfrutados</a>
        </div>
    </div>
    
    <script>
        // Función para filtrar empleados
        function filtrarEmpleados() {
            const nombre = document.getElementById('buscarNombre').value.toLowerCase();
            const puesto = document.getElementById('filtroPuesto').value;
            const filas = document.querySelectorAll('#tablaVacaciones tbody tr');
            
            filas.forEach(fila => {
                const nombreEmpleado = fila.cells[1].textContent.toLowerCase();
                const puestoEmpleado = fila.cells[2].textContent;
                
                const coincideNombre = nombre === '' || nombreEmpleado.includes(nombre);
                const coincidePuesto = puesto === '' || puestoEmpleado === puesto;
                
                fila.style.display = (coincideNombre && coincidePuesto) ? '' : 'none';
            });
        }
        
        // Función para habilitar la edición
        function habilitarEdicion(boton) {
            const fila = boton.parentNode.parentNode;
            const celdasEditables = fila.querySelectorAll('.editable');
            const btnGuardar = fila.querySelector('.btn-guardar');
            const btnCancelar = fila.querySelector('.btn-cancelar');
            
            // Guardar valores originales
            celdasEditables.forEach(celda => {
                const valorOriginal = celda.textContent;
                celda.setAttribute('data-original', valorOriginal);
                celda.innerHTML = `<input type="number" value="${valorOriginal}" min="0">`;
            });
            
            // Mostrar/ocultar botones
            boton.style.display = 'none';
            btnGuardar.style.display = 'inline-block';
            btnCancelar.style.display = 'inline-block';
        }
        
        // Función para cancelar la edición
        function cancelarEdicion(boton) {
            const fila = boton.parentNode.parentNode;
            const celdasEditables = fila.querySelectorAll('.editable');
            const btnEditar = fila.querySelector('.btn-editar');
            const btnGuardar = fila.querySelector('.btn-guardar');
            const btnCancelar = fila.querySelector('.btn-cancelar');
            
            // Restaurar valores originales
            celdasEditables.forEach(celda => {
                const valorOriginal = celda.getAttribute('data-original');
                celda.textContent = valorOriginal;
            });
            
            // Mostrar/ocultar botones
            btnEditar.style.display = 'inline-block';
            btnGuardar.style.display = 'none';
            btnCancelar.style.display = 'none';
        }
        
        // Función para guardar cambios
        function guardarCambios(id) {
            const fila = document.querySelector(`tr[data-id="${id}"]`);
            const celdasEditables = fila.querySelectorAll('.editable');
            const btnEditar = fila.querySelector('.btn-editar');
            const btnGuardar = fila.querySelector('.btn-guardar');
            const btnCancelar = fila.querySelector('.btn-cancelar');
            
            // Recopilar datos
            const datos = { id: id };
            celdasEditables.forEach(celda => {
                const campo = celda.getAttribute('data-field');
                const valor = celda.querySelector('input').value;
                datos[campo] = valor;
            });
            
            // Enviar datos al servidor
            fetch('actualizar_vacaciones.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(datos)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar valores en la tabla
                    celdasEditables.forEach(celda => {
                        const campo = celda.getAttribute('data-field');
                        const nuevoValor = datos[campo];
                        celda.textContent = nuevoValor;
                    });
                    
                    // Recalcular "A Disfrutar"
                    const diasTotales = parseInt(datos.dias_totales);
                    const diasAsignados = parseInt(datos.dias_asignados);
                    const diasDisfrutados = parseInt(datos.dias_disfrutados);
                    const aDisfrutar = diasTotales - diasAsignados - diasDisfrutados;
                    fila.cells[6].textContent = aDisfrutar;
                    
                    // Mostrar/ocultar botones
                    btnEditar.style.display = 'inline-block';
                    btnGuardar.style.display = 'none';
                    btnCancelar.style.display = 'none';
                    
                    alert('Datos actualizados correctamente');
                } else {
                    alert('Error: ' + data.error);
                    cancelarEdicion(btnCancelar);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al actualizar los datos');
                cancelarEdicion(btnCancelar);
            });
        }
        
        // Función para actualizar días disfrutados
        function actualizarDisfrutados() {
            if (confirm('¿Estás seguro de que deseas actualizar los días disfrutados? Esto procesará todas las solicitudes aprobadas cuya fecha de inicio ya haya pasado.')) {
                fetch('actualizar_disfrutados.php')
                .then(response => response.text())
                .then(data => {
                    alert('Días disfrutados actualizados correctamente');
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al actualizar los días disfrutados');
                });
            }
        }
        
        // Permitir búsqueda al presionar Enter
        document.getElementById('buscarNombre').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                filtrarEmpleados();
            }
        });
    </script>
</body>
</html>