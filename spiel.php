<?php
session_start();

// Beispiel: Punktzahl und Level aus Session oder einer Datenbank
// (Anpassung je nach deinem System erforderlich)
if (!isset($_SESSION['charakter'])) {
    header('Location: abenteuer.php'); // Umleitung, falls kein Charakter ausgewählt wurde
    exit();
}

$charakter = $_SESSION['charakter']; // Ausgewählter Charakter
$punkte = isset($_SESSION['punkte']) ? $_SESSION['punkte'] : 0; // Punkte des Spielers
$level = isset($_SESSION['level']) ? $_SESSION['level'] : 1; // Aktuelles Level des Spielers
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Abenteuer starten</title>
  <link rel="stylesheet" href="styles1.css">
</head>
<body>
  <div class="clouds">
    <div class="clouds-1"></div>
    <div class="clouds-2"></div>
    <div class="clouds-3"></div>
  </div>
  <header>
    <h1>Dein Abenteuer beginnt, <?php echo htmlspecialchars($charakter); ?>!</h1>
  </header>
  <audio id="background-audio" preload="auto">
        <source src="Story.mp3" type="audio/mp3">
        Dein Browser unterstützt das Abspielen von Audio nicht.
    </audio>
  <main>
     <section class="section1">
    <button class="my-button1">LEVEL 1</button>
    <button class="my-button2">LEVEL 2</button>
    <button class="my-button3">LEVEL 3</button>
    <button class="my-button4">LEVEL 4</button>
    <button class="my-button5">LEVEL 5</button>
    <button class="my-button6">LEVEL 6</button>
  </section>
  <section class="section2">
    <button class="my-button7">LEVEL 7</button>
    <button class="my-button8">LEVEL 8</button>
    <button class="my-button9">LEVEL 9</button>
    <button class="my-button10">LEVEL 10</button>
    <button class="my-button11">LEVEL 11</button>
    <button class="my-button12">LEVEL 12</button>
  </section>
  <section class="section3">
    <button class="my-button13">LEVEL 13</button>
    <button class="my-button14">LEVEL 14</button>
    <button class="my-button15">LEVEL 15</button>
    <button class="my-button16">LEVEL 16</button>
    <button class="my-button17">LEVEL 17</button>
    <button class="my-button18">LEVEL 18</button>
  </section>
  <section class="section4">
    <button class="my-button19">LEVEL 19</button>
    <button class="my-button20">LEVEL 20</button>
    <button class="my-button21">LEVEL 21</button>
    <button class="my-button22">LEVEL 22</button>
    <button class="my-button23">LEVEL 23</button>
    <button class="my-button24">LEVEL 24</button>
  </section>
  </main>
  <footer>
    <p>&copy; 2025 Trading Card Game - Alle Rechte vorbehalten.</p>
  </footer>
  <script>
        // Funktion zum Abspielen des Audios beim Laden der Seite
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