<?php
session_start();
if (!isset($_SESSION['empleado_id'])) {
    header("Location: ../login.php");
    exit();
}

// Verificar permisos de usuario
$tiene_permisos = ($_SESSION['empleado_rol'] === 'admin' || $_SESSION['empleado_rol'] === 'editor');
if (!$tiene_permisos) {
    header("Location: ../sistema/index.php");
    exit();
}

// Configuración de la base de datos
require_once '../db_config.php';

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        
        nav {
            background-color: #0c21e2;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        nav ul li {
            margin-right: 15px;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        h1 {
            text-align: center;
            color: #0c21e2;
            margin-bottom: 20px;
        }
        
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .filtro {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filtro input, .filtro select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .filtro button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            padding: 12px 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #0c21e2;
            color: white;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .editable input {
            width: 60px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        
        .btn-editar, .btn-guardar, .btn-cancelar {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 2px;
        }
        
        .btn-editar {
            background-color: #2196F3;
            color: white;
        }
        
        .btn-guardar {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-cancelar {
            background-color: #f44336;
            color: white;
        }
        
        .nav-links {
            margin-top: 20px;
        }
        
        .nav-links a {
            display: inline-block;
            padding: 10px 15px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        
        @media (max-width: 768px) {
            .filtro {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
   <nav>
        <ul>
            <li>
                <img src="../Assets/icons/Uranus.ico" alt="Logo Preisa" height="40">
            </li>
            <li>
                <span style="color: white;"><?php echo $_SESSION['empleado_nombre']; ?> <br><b style="color: Green;"><?php echo $_SESSION['empleado_rol']; ?></b></span>
            </li>
            <li style="margin-left: auto;">
                <a href="../logout.php" style="text-decoration:none; font-size:24px; color:red;" title="Cerrar sesión"><i class="fas fa-power-off"></i></a>
            </li>
        </ul>
    </nav>
    
    <div class="container">
        <h1>ACTUALIZAR DATOS DE VACACIONES</h1>
        
        <?php
        if (isset($_GET['success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
        }
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-error">' . htmlspecialchars($_GET['error']) . '</div>';
        }
        ?>
        
        <div class="filtro">
            <input type="text" id="buscarNombre" placeholder="Buscar por nombre">
            <select id="filtroPuesto">
                <option value="">Todos los puestos</option>
                <option value="administrativo">Administrativo</option>
                <option value="operativo">Operativo</option>
            </select>
            <button onclick="filtrarEmpleados()">Buscar</button>
        </div>
        
        <div style="overflow-x: auto;">
            <table id="tablaVacaciones">
                <thead>
                    <tr>
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
                                <td>{$row['nombre']}</td>
                                <td>{$row['puesto']}</td>
                                <td class='editable' data-field='dias_totales'>{$row['dias_totales']}</td>
                                <td class='editable' data-field='dias_asignados'>{$row['dias_asignados']}</td>
                                <td class='editable' data-field='dias_disfrutados'>{$row['dias_disfrutados']}</td>
                                <td>{$row['a_disfrutar']}</td>
                                <td>
                                    <button class='btn-editar' onclick='habilitarEdicion(this)'>Editar</button>
                                    <button class='btn-guardar' onclick='confirmarGuardar({$row['id']})' style='display:none;'>Guardar</button>
                                    <button class='btn-cancelar' onclick='cancelarEdicion(this)' style='display:none;'>Cancelar</button>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No se encontraron registros</td></tr>";
                    }
                    
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
        
        <div class="nav-links">
            <a href="../sistema/index.php">Volver al Inicio</a>
        </div>
    </div>
    
    <script>
        function filtrarEmpleados() {
            const nombre = document.getElementById('buscarNombre').value.toLowerCase();
            const puesto = document.getElementById('filtroPuesto').value;
            const filas = document.querySelectorAll('#tablaVacaciones tbody tr');
            
            filas.forEach(fila => {
                const nombreEmpleado = fila.cells[0].textContent.toLowerCase();
                const puestoEmpleado = fila.cells[1].textContent;
                
                const coincideNombre = nombre === '' || nombreEmpleado.includes(nombre);
                const coincidePuesto = puesto === '' || puestoEmpleado === puesto;
                
                fila.style.display = (coincideNombre && coincidePuesto) ? '' : 'none';
            });
        }
        
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
        
        function confirmarGuardar(id) {
            if (confirm('¿Estás seguro de que deseas guardar los cambios?')) {
                guardarCambios(id);
            }
        }
        
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
            
            // Validar que los días a disfrutar no sean negativos
            const diasTotales = parseInt(datos.dias_totales);
            const diasAsignados = parseInt(datos.dias_asignados);
            const diasDisfrutados = parseInt(datos.dias_disfrutados);
            const aDisfrutar = diasTotales - diasAsignados - diasDisfrutados;
            
            if (aDisfrutar < 0) {
                alert('Error: Los días a disfrutar no pueden ser negativos. Revise los valores.');
                return;
            }
            
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
                    fila.cells[5].textContent = aDisfrutar;
                    
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
        
        document.getElementById('buscarNombre').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                filtrarEmpleados();
            }
        });
    </script>
</body>
</html>