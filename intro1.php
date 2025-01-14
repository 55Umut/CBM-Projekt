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
$sql = "SELECT id, name, bild_url FROM charaktere"; 
$result = $conn->query($sql);
$charaktere = [];
if ($result->num_rows > 0) {
   
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
        <source src="Story.mp3" type="audio/mp3">
        Dein Browser unterstützt das Abspielen von Audio nicht.
    </audio>
    <header>
        <nav>
            <ul>
                <li><a href="start.php">Startseite</a></li>
            </ul>
        </nav>
        <form action="login.php" method="POST">
            <button type="submit" name="logout" class="btn">Abmelden</button>
        </form>
    </header>
    <main>
        <!-- Karussell 1 -->
        <div class="container" id="upper-carousel">
            <div class="carousel">
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
            </div>
        </div>  

        <!-- Karussell 2 -->
        <div class="container" id="lower-carousel" style="display: none;">
            <div class="carousel">
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
                <div class="carousel__face"><span></span></div>
            </div>
        </div> 
                 <form action="spielfeld.php" method="POST">
            <button type="submit" name="spielfeld" class="btn">Weiter</button>
        </form> 
    </main>
    <footer>
        <p>&copy; 2025 Codebreakers - Battle of Minds Trading Card Game - Alle Rechte vorbehalten.</p>
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

            var upperCarousel = document.getElementById('upper-carousel');
            var lowerCarousel = document.getElementById('lower-carousel');

            // Funktion, um das Karussell zu wechseln
            function switchCarousels() {
                upperCarousel.style.display = 'none';
                lowerCarousel.style.display = 'block';

                // Nach 30 Sekunden das obere Karussell wieder anzeigen und das untere verstecken
                setTimeout(function() {
                    lowerCarousel.style.display = 'none';
                    upperCarousel.style.display = 'block';
                }, 30000); // 30 Sekunden
            }

            // Zu Beginn wird nur das obere Karussell gezeigt
            upperCarousel.style.display = 'block';
            lowerCarousel.style.display = 'none';

            // Wechselt nach 30 Sekunden das Karussell
            setTimeout(switchCarousels, 30000); // Wechsel nach 30 Sekunden
        };
    </script>
</body>
</html>
