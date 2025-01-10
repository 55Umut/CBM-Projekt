
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Wenn nicht eingeloggt, zur Login-Seite weiterleiten
    exit();
}
$benutzername = $_SESSION['benutzername'];
$servername = "localhost";
$username = "root"; 
$password = "";
$dbname = "kartenspiel1_db";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error);
}
$sql = "SELECT id, name, bild_url FROM charaktere"; // Angenommen, die Tabelle heißt "charaktere"
$result = $conn->query($sql);
$charaktere = [];
if ($result->num_rows > 0) {
    // Ergebnisse in das Array laden
    while($row = $result->fetch_assoc()) {
        $charaktere[] = $row;
    }
} else {
    echo "Keine Charaktere gefunden.";
}
$conn->close();
if (isset($_POST['charakter'])) {
    $_SESSION['charakter'] = $_POST['charakter']; // Den ausgewählten Charakter speichern
    header("Location: spiel.php"); // Weiterleitung zur nächsten Seite nach der Auswahl
    exit();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codebreakers - Battle of Minds</title>
    <link rel="stylesheet" href="stylesK.css">
</head>
<body>
    <div class="clouds">
        <div class="clouds-1"></div>
        <div class="clouds-2"></div>
        <div class="clouds-3"></div>
    </div>
    <audio id="background-audio" preload="auto">
        <source src="Story.mp3".mp3" type="audio/mp3">
        Dein Browser unterstützt das Abspielen von Audio nicht.
    </audio>
    <header>
        <nav>
            <ul>
                <li><a href="start.php">Startseite</a></li>
                <li><a href="karten.php">Mein Deck</a></li>
                <li><a href="shop.php">Karten Shop</a></li>
            </ul>
        </nav>
        <form action="login.php" method="POST">
            <button type="submit" name="logout" class="btn">Abmelden</button>
        </form>
    </header>
    <main>
    </main>
    <footer>
        <p>&copy; 2025 Trading Card Game - Alle Rechte vorbehalten.</p>
    </footer>
    <script>
        window.onload = function() {
            var audio = document.getElementById('background-audio');
            audio.play().catch(function(error) {
                var div = document.createElement('div');
                div.style.display = 'none';
                document.body.appendChild(div);
                div.click();
            });
        };
    </script>
</body>
</html>

<div class="container">
  <div class="carousel">
    <div class="carousel__face"><span>This is something</span></div>
    <div class="carousel__face"><span>Very special</span></div>
    <div class="carousel__face"><span>Special is the key</span></div>
    <div class="carousel__face"><span>For you</span></div>
    <div class="carousel__face"><span>Just give it</span></div>
    <div class="carousel__face"><span>A try</span></div>
    <div class="carousel__face"><span>And see</span></div>
    <div class="carousel__face"><span>How IT Works</span></div>
    <div class="carousel__face"><span>Woow</span></div>
  </div>
</div>  

body {
  margin: 0;
  background: lightgray;
  text-align: center;
  font-family: sans-serif;
  color: #fefefe;
}
.container {
  position: relative;
  width: 320px;
  margin: 100px auto 0 auto;
  perspective: 1000px;
}

.carousel {
  position: absolute;
  width: 100%;
  height: 100%;
  transform-style: preserve-3d; 
  animation: rotate360 60s infinite forwards linear;
}
.carousel__face { 
  position: absolute;
  width: 300px;
  height: 187px;
  top: 20px;
  left: 10px;
  right: 10px;
  background-size: cover;
  box-shadow:inset 0 0 0 2000px rgba(0,0,0,0.5);
  display: flex;
}

span {
  margin: auto;
  font-size: 2rem;
}


.carousel__face:nth-child(1) {
  background-image: url("https://images.pexels.com/photos/1141853/pexels-photo-1141853.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260");
  transform: rotateY(  0deg) translateZ(430px); }
.carousel__face:nth-child(2) { 
  background-image: url("https://images.pexels.com/photos/1258865/pexels-photo-1258865.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260");
    transform: rotateY( 40deg) translateZ(430px); }
.carousel__face:nth-child(3) {
  background-image: url("https://images.pexels.com/photos/808466/pexels-photo-808466.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260");
  transform: rotateY( 80deg) translateZ(430px); }
.carousel__face:nth-child(4) {
  background-image: url("https://images.pexels.com/photos/1394841/pexels-photo-1394841.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260");
  transform: rotateY(120deg) translateZ(430px); }
.carousel__face:nth-child(5) { 
  background-image: url("https://images.pexels.com/photos/969679/pexels-photo-969679.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260");
 transform: rotateY(160deg) translateZ(430px); }
.carousel__face:nth-child(6) { 
  background-image: url("https://images.pexels.com/photos/1834400/pexels-photo-1834400.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260");
 transform: rotateY(200deg) translateZ(430px); }
.carousel__face:nth-child(7) { 
  background-image: url("https://images.pexels.com/photos/1415268/pexels-photo-1415268.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260");
 transform: rotateY(240deg) translateZ(430px); }
.carousel__face:nth-child(8) {
  background-image: url("https://images.pexels.com/photos/135018/pexels-photo-135018.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260");
  transform: rotateY(280deg) translateZ(430px); }
.carousel__face:nth-child(9) {
  background-image: url("https://images.pexels.com/photos/1175135/pexels-photo-1175135.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260");
  transform: rotateY(320deg) translateZ(430px); }



@keyframes rotate360 {
  from {
    transform: rotateY(0deg);
  }
  to {
    transform: rotateY(-360deg);
  }
}
