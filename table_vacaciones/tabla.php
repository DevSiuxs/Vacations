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
                
                <!-- 1 año -->
                <div class="card-container">
                    <div class="card" id="card-1">
                        <div class="card-front">
                            <div>
                                <h3>1 año</h3>
                                <p>Días Totales: 12</p>
                                <p>Días Asignados: 12</p>
                                <p>A Disfrutar: 0</p>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-1')">Ver Informacion</button>
                        </div>
                        <div class="card-back">
                            <div>
                                <h3>Beneficios</h3>
                                <ul>
                                    <li>Aniversario: 2 días</li>
                                    <li>Cumpleaños: 2 días</li>
                                    <li>Semana Santa: 3 días</li>
                                    <li>Diciembre: 3 días</li>
                                    <li>Salidas Tempranas: 2 días</li>
                                </ul>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-1')">Volver</button>
                        </div>
                    </div>
                </div>
                
                <!-- 2 años -->
                <div class="card-container">
                    <div class="card" id="card-2">
                        <div class="card-front">
                            <div>
                                <h3>2 años</h3>
                                <p>Días Totales: 14</p>
                                <p>Días Asignados: 12</p>
                                <p>A Disfrutar: 2</p>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-2')">Ver Informacion</button>
                        </div>
                        <div class="card-back">
                            <div>
                                <h3>Beneficios</h3>
                                <ul>
                                    <li>Aniversario: 2 días</li>
                                    <li>Cumpleaños: 2 días</li>
                                    <li>Semana Santa: 3 días</li>
                                    <li>Diciembre: 3 días</li>
                                    <li>Salidas Tempranas: 2 días</li>
                                </ul>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-2')">Volver</button>
                        </div>
                    </div>
                </div>
                
                <!-- 3 años -->
                <div class="card-container">
                    <div class="card" id="card-3">
                        <div class="card-front">
                            <div>
                                <h3>3 años</h3>
                                <p>Días Totales: 16</p>
                                <p>Días Asignados: 12</p>
                                <p>A Disfrutar: 4</p>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-3')">Ver Informacion</button>
                        </div>
                        <div class="card-back">
                            <div>
                                <h3>Beneficios</h3>
                                <ul>
                                    <li>Aniversario: 2 días</li>
                                    <li>Cumpleaños: 2 días</li>
                                    <li>Semana Santa: 3 días</li>
                                    <li>Diciembre: 3 días</li>
                                    <li>Salidas Tempranas: 2 días</li>
                                </ul>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-3')">Volver</button>
                        </div>
                    </div>
                </div>
                
                <!-- 4 años -->
                <div class="card-container">
                    <div class="card" id="card-4">
                        <div class="card-front">
                            <div>
                                <h3>4 años</h3>
                                <p>Días Totales: 18</p>
                                <p>Días Asignados: 16</p>
                                <p>A Disfrutar: 2</p>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-4')">Ver Informacion</button>
                        </div>
                        <div class="card-back">
                            <div>
                                <h3>Beneficios</h3>
                                <ul>
                                    <li>Aniversario: 3 días</li>
                                    <li>Cumpleaños: 3 días</li>
                                    <li>Semana Santa: 4 días</li>
                                    <li>Diciembre: 4 días</li>
                                    <li>Salidas Tempranas: 2 días</li>
                                </ul>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-4')">Volver</button>
                        </div>
                    </div>
                </div>

                <!-- 5 años -->
                <div class="card-container">
                    <div class="card" id="card-5">
                        <div class="card-front">
                            <div>
                                <h3>5 años</h3>
                                <p>Días Totales: 20</p>
                                <p>Días Asignados: 15</p>
                                <p>A Disfrutar: 5</p>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-5')">Ver Informacion</button>
                        </div>
                        <div class="card-back">
                            <div>
                                <h3>Beneficios</h3>
                                <ul>
                                    <li>Aniversario: 3 días</li>
                                    <li>Cumpleaños: 3 días</li>
                                    <li>Semana Santa: 3 días</li>
                                    <li>Diciembre: 4 días</li>
                                    <li>Salidas Tempranas: 2 días</li>
                                </ul>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-5')">Volver</button>
                        </div>
                    </div>
                </div>

                <!-- 6-10 años -->
                <div class="card-container">
                    <div class="card" id="card-6">
                        <div class="card-front">
                            <div>
                                <h3>6-10 años</h3>
                                <p>Días Totales: 22</p>
                                <p>Días Asignados: 17</p>
                                <p>A Disfrutar: 5</p>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-6')">Ver Informacion</button>
                        </div>
                        <div class="card-back">
                            <div>
                                <h3>Beneficios</h3>
                                <ul>
                                    <li>Aniversario: 4 días</li>
                                    <li>Cumpleaños: 4 días</li>
                                    <li>Semana Santa: 3 días</li>
                                    <li>Diciembre: 4 días</li>
                                    <li>Salidas Tempranas: 2 días</li>
                                </ul>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-6')">Volver</button>
                        </div>
                    </div>
                </div>

                <!-- 11-15 años -->
                <div class="card-container">
                    <div class="card" id="card-7">
                        <div class="card-front">
                            <div>
                                <h3>11-15 años</h3>
                                <p>Días Totales: 24</p>
                                <p>Días Asignados: 19</p>
                                <p>A Disfrutar: 5</p>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-7')">Ver Informacion</button>
                        </div>
                        <div class="card-back">
                            <div>
                                <h3>Beneficios</h3>
                                <ul>
                                    <li>Aniversario: 4 días</li>
                                    <li>Cumpleaños: 4 días</li>
                                    <li>Semana Santa: 4 días</li>
                                    <li>Diciembre: 4 días</li>
                                    <li>Salidas Tempranas: 4 días</li>
                                </ul>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-7')">Volver</button>
                        </div>
                    </div>
                </div>

                <!-- 16-20 años -->
                <div class="card-container">
                    <div class="card" id="card-8">
                        <div class="card-front">
                            <div>
                                <h3>16-20 años</h3>
                                <p>Días Totales: 26</p>
                                <p>Días Asignados: 21</p>
                                <p>A Disfrutar: 5</p>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-8')">Ver Informacion</button>
                        </div>
                        <div class="card-back">
                            <div>
                                <h3>Beneficios</h3>
                                <ul>
                                    <li>Aniversario: 4 días</li>
                                    <li>Cumpleaños: 5 días</li>
                                    <li>Semana Santa: 5 días</li>
                                    <li>Diciembre: 5 días</li>
                                    <li>Salidas Tempranas: 2 días</li>
                                </ul>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-8')">Volver</button>
                        </div>
                    </div>
                </div>

                <!-- 21-25 años -->
                <div class="card-container">
                    <div class="card" id="card-9">
                        <div class="card-front">
                            <div>
                                <h3>21-25 años</h3>
                                <p>Días Totales: 28</p>
                                <p>Días Asignados: 22</p>
                                <p>A Disfrutar: 6</p>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-9')">Ver Informacion</button>
                        </div>
                        <div class="card-back">
                            <div>
                                <h3>Beneficios</h3>
                                <ul>
                                    <li>Aniversario: 5 días</li>
                                    <li>Cumpleaños: 5 días</li>
                                    <li>Semana Santa: 5 días</li>
                                    <li>Diciembre: 5 días</li>
                                    <li>Salidas Tempranas: 2 días</li>
                                </ul>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-9')">Volver</button>
                        </div>
                    </div>
                </div>

                <!-- 26-30 años -->
                <div class="card-container">
                    <div class="card" id="card-10">
                        <div class="card-front">
                            <div>
                                <h3>26-30 años</h3>
                                <p>Días Totales: 30</p>
                                <p>Días Asignados: 22</p>
                                <p>A Disfrutar: 8</p>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-10')">Ver Beneficios</button>
                        </div>
                        <div class="card-back">
                            <div>
                                <h3>Beneficios</h3>
                                <ul>
                                    <li>Aniversario: 5 días</li>
                                    <li>Cumpleaños: 5 días</li>
                                    <li>Semana Santa: 5 días</li>
                                    <li>Diciembre: 5 días</li>
                                    <li>Salidas Tempranas: 2 días</li>
                                </ul>
                            </div>
                            <button class="flip-btn" onclick="flipCard('card-10')">Volver</button>
                        </div>
                    </div>
                </div>
                
            </div>
        </section>

  <!-- Operativo -->
  <section>
    <h2>Operativo</h2>
    <div class="cards">
      <!-- 1 año -->
      <div class="card-container">
        <div class="card" id="card-11">
          <div class="card-front">
            <div>
              <h3>1 año</h3>
              <p>Días Totales: 12</p>
              <p>Días Asignados: 10</p>
              <p>A Disfrutar: 2</p>
            </div>
            <button class="flip-btn" onclick="flipCard('card-11')">Ver informacion</button>
          </div>
          <div class="card-back">
            <div>
              <h3>Beneficios</h3>
              <ul>
                <li>Aniversario: 5 día</li>
                <li>Cumpleaños: 5 día</li>
              </ul>
            </div>
            <button class="flip-btn" onclick="flipCard('card-11')">Volver</button>
          </div>
        </div>
      </div>
      
      <!-- 2 años -->
      <div class="card-container">
        <div class="card" id="card-12">
          <div class="card-front">
            <div>
              <h3>2 años</h3>
              <p>Días Totales: 15</p>
              <p>Días Asignados: 10</p>
              <p>A Disfrutar: 5</p>
            </div>
            <button class="flip-btn" onclick="flipCard('card-12')">Ver Informacion</button>
          </div>
          <div class="card-back">
            <div>
              <h3>Beneficios</h3>
              <ul>
                <li>Aniversario: 5 día</li>
                <li>Cumpleaños: 5 día</li>
              </ul>
            </div>
            <button class="flip-btn" onclick="flipCard('card-12')">Volver</button>
          </div>
        </div>
      </div>
      
      <!-- 3 años -->
      <div class="card-container">
        <div class="card" id="card-13">
          <div class="card-front">
            <div>
              <h3>3 años</h3>
              <p>Días Totales: 15</p>
              <p>Días Asignados: 10</p>
              <p>A Disfrutar: 5</p>
            </div>
            <button class="flip-btn" onclick="flipCard('card-13')">Ver Informacion</button>
          </div>
          <div class="card-back">
            <div>
              <h3>Beneficios</h3>
              <ul>
                <li>Aniversario: 5 día</li>
                <li>Cumpleaños: 5 día</li>
              </ul>
            </div>
            <button class="flip-btn" onclick="flipCard('card-13')">Volver</button>
          </div>
        </div>
      </div>
      
      <!-- 4 años -->
      <div class="card-container">
        <div class="card" id="card-14">
          <div class="card-front">
            <div>
              <h3>4 años</h3>
              <p>Días Totales: 15</p>
              <p>Días Asignados: 10</p>
              <p>A Disfrutar: 5</p>
            </div>
            <button class="flip-btn" onclick="flipCard('card-14')">Ver Informacion</button>
          </div>
          <div class="card-back">
            <div>
              <h3>Beneficios</h3>
              <ul>
                <li>Aniversario: 5 día</li>
                <li>Cumpleaños: 5 día</li>
              </ul>
            </div>
            <button class="flip-btn" onclick="flipCard('card-14')">Volver</button>
          </div>
        </div>
      </div>
      
      <!-- 5 años -->
      <div class="card-container">
        <div class="card" id="card-15">
          <div class="card-front">
            <div>
              <h3>5 años</h3>
              <p>Días Totales: 15</p>
              <p>Días Asignados: 10</p>
              <p>A Disfrutar: 5</p>
            </div>
            <button class="flip-btn" onclick="flipCard('card-15')">Ver informacion</button>
          </div>
          <div class="card-back">
            <div>
              <h3>Beneficios</h3>
              <ul>
                <li>Aniversario: 5 día</li>
                <li>Cumpleaños: 5 día</li>
              </ul>
            </div>
            <button class="flip-btn" onclick="flipCard('card-15')">Volver</button>
          </div>
        </div>
      </div>
      
      <!-- 6-10 años -->
      <div class="card-container">
        <div class="card" id="card-16">
          <div class="card-front">
            <div>
              <h3>6-10 años</h3>
              <p>Días Totales: 15</p>
              <p>Días Asignados: 10</p>
              <p>A Disfrutar: 5</p>
            </div>
            <button class="flip-btn" onclick="flipCard('card-16')">Ver Informacion</button>
          </div>
          <div class="card-back">
            <div>
              <h3>Beneficios</h3>
              <ul>
                <li>Aniversario: 5 día</li>
                <li>Cumpleaños: 5 día</li>
              </ul>
            </div>
            <button class="flip-btn" onclick="flipCard('card-16')">Volver</button>
          </div>
        </div>
      </div>
      
      <!-- 11-15 años -->
      <div class="card-container">
        <div class="card" id="card-17">
          <div class="card-front">
            <div>
              <h3>11-15 años</h3>
              <p>Días Totales: 18</p>
              <p>Días Asignados: 10</p>
              <p>A Disfrutar: 8</p>
            </div>
            <button class="flip-btn" onclick="flipCard('card-17')">Ver Informacion</button>
          </div>
          <div class="card-back">
            <div>
              <h3>Beneficios</h3>
              <ul>
                <li>Aniversario: 5 día</li>
                <li>Cumpleaños: 5 día</li>
              </ul>
            </div>
            <button class="flip-btn" onclick="flipCard('card-17')">Volver</button>
          </div>
        </div>
      </div>
      
      <!-- 16-20 años -->
      <div class="card-container">
        <div class="card" id="card-18">
          <div class="card-front">
            <div>
              <h3>16-20 años</h3>
              <p>Días Totales: 18</p>
              <p>Días Asignados: 10</p>
              <p>A Disfrutar: 8</p>
            </div>
            <button class="flip-btn" onclick="flipCard('card-18')">Ver Informacion</button>
          </div>
          <div class="card-back">
            <div>
              <h3>Beneficios</h3>
              <ul>
                <li>Aniversario: 5 día</li>
                <li>Cumpleaños: 5 día</li>
              </ul>
            </div>
            <button class="flip-btn" onclick="flipCard('card-18')">Volver</button>
          </div>
        </div>
      </div>
      
      <!-- 21-25 años -->
      <div class="card-container">
        <div class="card" id="card-19">
          <div class="card-front">
            <div>
              <h3>21-25 años</h3>
              <p>Días Totales: 20</p>
              <p>Días Asignados: 10</p>
              <p>A Disfrutar: 10</p>
            </div>
            <button class="flip-btn" onclick="flipCard('card-19')">Ver Informacion</button>
          </div>
          <div class="card-back">
            <div>
              <h3>Beneficios</h3>
              <ul>
                <li>Aniversario: 5 día</li>
                <li>Cumpleaños: 5 día</li>
              </ul>
            </div>
            <button class="flip-btn" onclick="flipCard('card-19')">Volver</button>
          </div>
        </div>
      </div>
      
      <!-- 26-30 años -->
      <div class="card-container">
        <div class="card" id="card-20">
          <div class="card-front">
            <div>
              <h3>26-30 años</h3>
              <p>Días Totales: 20</p>
              <p>Días Asignados: 10</p>
              <p>A Disfrutar: 10</p>
            </div>
            <button class="flip-btn" onclick="flipCard('card-20')">Ver Informacion</button>
          </div>
          <div class="card-back">
            <div>
              <h3>Beneficios</h3>
              <ul>
                <li>Aniversario: 5 día</li>
                <li>Cumpleaños: 5 día</li>
              </ul>
            </div>
            <button class="flip-btn" onclick="flipCard('card-20')">Volver</button>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
 <!-- Botón regresar -->
  <div class="btn-container">
    <a href="../index.php" class="btn">Regresar</a>
  </div>

     <script>
        function flipCard(cardId) {
            const card = document.getElementById(cardId);
            card.classList.toggle('flipped');
        }
    </script>
</body>
</html>