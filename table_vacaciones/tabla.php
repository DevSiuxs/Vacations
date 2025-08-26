<?php
session_start();
if (!isset($_SESSION['empleado_id'])) {
    header("Location: ../login.php");
    exit();
}
// Configuración de la base de datos
require_once '../db_config.php'; // Archivo con configuración de BD

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIAS DE VACACIONES ASIGNADOS</title>
    <link rel="stylesheet" href="../css/vacaciones.css">
     <link rel="stylesheet" href="../css/tabla.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
  <!-- Administrativo -->
  <section>
    <h2>Administrativo</h2>
    <div class="cards">
      <div class="card"><h3>1 año</h3><p>Días Totales: 12</p><p>Días Asignados: 12</p><p>A Disfrutar: 0</p></div>
      <div class="card"><h3>2 años</h3><p>Días Totales: 14</p><p>Días Asignados: 12</p><p>A Disfrutar: 2</p></div>
      <div class="card"><h3>3 años</h3><p>Días Totales: 16</p><p>Días Asignados: 12</p><p>A Disfrutar: 4</p></div>
      <div class="card"><h3>4 años</h3><p>Días Totales: 18</p><p>Días Asignados: 16</p><p>A Disfrutar: 2</p></div>
      <div class="card"><h3>5 años</h3><p>Días Totales: 20</p><p>Días Asignados: 15</p><p>A Disfrutar: 5</p></div>
      <div class="card"><h3>6-10 años</h3><p>Días Totales: 22</p><p>Días Asignados: 17</p><p>A Disfrutar: 5</p></div>
      <div class="card"><h3>11-15 años</h3><p>Días Totales: 24</p><p>Días Asignados: 19</p><p>A Disfrutar: 5</p></div>
      <div class="card"><h3>16-20 años</h3><p>Días Totales: 26</p><p>Días Asignados: 21</p><p>A Disfrutar: 5</p></div>
      <div class="card"><h3>21-25 años</h3><p>Días Totales: 28</p><p>Días Asignados: 22</p><p>A Disfrutar: 6</p></div>
      <div class="card"><h3>26-30 años</h3><p>Días Totales: 30</p><p>Días Asignados: 22</p><p>A Disfrutar: 8</p></div>
    </div>
  </section>

  <!-- Operativo -->
  <section>
    <h2>Operativo</h2>
    <div class="cards">
      <div class="card"><h3>1 año</h3><p>Días Totales: 12</p><p>Días Asignados: 10</p><p>A Disfrutar: 2</p></div>
      <div class="card"><h3>2 años</h3><p>Días Totales: 15</p><p>Días Asignados: 10</p><p>A Disfrutar: 5</p></div>
      <div class="card"><h3>3 años</h3><p>Días Totales: 15</p><p>Días Asignados: 10</p><p>A Disfrutar: 5</p></div>
      <div class="card"><h3>4 años</h3><p>Días Totales: 15</p><p>Días Asignados: 10</p><p>A Disfrutar: 5</p></div>
      <div class="card"><h3>5 años</h3><p>Días Totales: 15</p><p>Días Asignados: 10</p><p>A Disfrutar: 5</p></div>
      <div class="card"><h3>6-10 años</h3><p>Días Totales: 15</p><p>Días Asignados: 10</p><p>A Disfrutar: 5</p></div>
      <div class="card"><h3>11-15 años</h3><p>Días Totales: 18</p><p>Días Asignados: 10</p><p>A Disfrutar: 8</p></div>
      <div class="card"><h3>16-20 años</h3><p>Días Totales: 18</p><p>Días Asignados: 10</p><p>A Disfrutar: 8</p></div>
      <div class="card"><h3>21-25 años</h3><p>Días Totales: 20</p><p>Días Asignados: 10</p><p>A Disfrutar: 10</p></div>
      <div class="card"><h3>26-30 años</h3><p>Días Totales: 20</p><p>Días Asignados: 10</p><p>A Disfrutar: 10</p></div>
    </div>
  </section>
</main>
 <!-- Botón regresar -->
  <div class="btn-container">
    <a href="../index.php" class="btn">Regresar</a>
  </div>
</body>
</html>