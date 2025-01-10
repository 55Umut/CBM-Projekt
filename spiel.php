<?php
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Wenn nicht eingeloggt, zur Login-Seite weiterleiten
    exit();
}

// Benutzerinformationen aus der Session
$benutzername = $_SESSION['benutzername'];
$charakter = isset($_SESSION['charakter']) ? $_SESSION['charakter'] : '';

// Verbindung zur Datenbank herstellen
$servername = "localhost";
$username = "root"; // Dein Datenbank-Benutzername
$password = ""; // Dein Datenbank-Passwort
$dbname = "kartenspiel1_db"; // Dein Datenbankname
$conn = new mysqli($servername, $username, $password, $dbname);

// Überprüfen, ob die Verbindung erfolgreich war
if ($conn->connect_error) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error);
}

// Abfrage der Punkte des eingeloggten Benutzers
$sql_punkte = "SELECT punkte FROM nutzer WHERE benutzername = '$benutzername'";
$result_punkte = $conn->query($sql_punkte);
$punkte = 0;

if ($result_punkte->num_rows > 0) {
    $row = $result_punkte->fetch_assoc();
    $punkte = $row['punkte'];
} else {
    echo "Keine Punkte gefunden für den Benutzer.";
}

// Liste der Level aus der Datenbank abrufen (mit Punkten, die benötigt werden)
$sql_levels = "SELECT level, punkte_bis FROM level_system ORDER BY level";
$result_levels = $conn->query($sql_levels);
$level_status = [];

// Überprüfen, welche Level freigeschaltet sind basierend auf den Punkten
if ($result_levels->num_rows > 0) {
    while ($row = $result_levels->fetch_assoc()) {
        if ($punkte >= $row['punkte_bis']) {
            $level_status[$row['level']] = true;
        } else {
            $level_status[$row['level']] = false;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dein Abenteuer</title>
    <link rel="stylesheet" href="styles2.css">
    <style>
        /* Visuelle Darstellung der deaktivierten Buttons */
        .disabled {
            background-color: #ccc;
            cursor: not-allowed;
            pointer-events: none; /* Verhindert Klicks */
        }
    </style>
</head>
<body>
    <div class="clouds">
        <div class="clouds-1"></div>
        <div class="clouds-2"></div>
        <div class="clouds-3"></div>
    </div>
    <header>
        <h1>Willkommen zurück, <?php echo htmlspecialchars($benutzername); ?>!</h1>
        <p>Du spielst als: <?php echo htmlspecialchars($charakter); ?></p>
        <p>Deine aktuellen Punkte: <?php echo $punkte; ?></p>
    </header>
    <main>
        <section class="section1">
            <!-- LEVEL 1 immer aktiv, auch wenn der Benutzer keine Punkte hat -->
            <button class="my-button1" <?php if ($punkte <= 0) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 1</button>
            <button class="my-button2" <?php if (!isset($level_status[2]) || !$level_status[2]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 2</button>
            <button class="my-button3" <?php if (!isset($level_status[3]) || !$level_status[3]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 3</button>
            <button class="my-button4" <?php if (!isset($level_status[4]) || !$level_status[4]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 4</button>
            <button class="my-button5" <?php if (!isset($level_status[5]) || !$level_status[5]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 5</button>
            <button class="my-button6" <?php if (!isset($level_status[6]) || !$level_status[6]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 6</button>
        </section>
        <section class="section2">
            <button class="my-button7" <?php if (!isset($level_status[7]) || !$level_status[7]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 7</button>
            <button class="my-button8" <?php if (!isset($level_status[8]) || !$level_status[8]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 8</button>
            <button class="my-button9" <?php if (!isset($level_status[9]) || !$level_status[9]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 9</button>
            <button class="my-button10" <?php if (!isset($level_status[10]) || !$level_status[10]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 10</button>
            <button class="my-button11" <?php if (!isset($level_status[11]) || !$level_status[11]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 11</button>
            <button class="my-button12" <?php if (!isset($level_status[12]) || !$level_status[12]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 12</button>
        </section>
        <section class="section3">
            <button class="my-button13" <?php if (!isset($level_status[13]) || !$level_status[13]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 13</button>
            <button class="my-button14" <?php if (!isset($level_status[14]) || !$level_status[14]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 14</button>
            <button class="my-button15" <?php if (!isset($level_status[15]) || !$level_status[15]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 15</button>
            <button class="my-button16" <?php if (!isset($level_status[16]) || !$level_status[16]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 16</button>
            <button class="my-button17" <?php if (!isset($level_status[17]) || !$level_status[17]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 17</button>
            <button class="my-button18" <?php if (!isset($level_status[18]) || !$level_status[18]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 18</button>
        </section>
        <section class="section4">
            <button class="my-button19" <?php if (!isset($level_status[19]) || !$level_status[19]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 19</button>
            <button class="my-button20" <?php if (!isset($level_status[20]) || !$level_status[20]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 20</button>
            <button class="my-button21" <?php if (!isset($level_status[21]) || !$level_status[21]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 21</button>
            <button class="my-button22" <?php if (!isset($level_status[22]) || !$level_status[22]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 22</button>
            <button class="my-button23" <?php if (!isset($level_status[23]) || !$level_status[23]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 23</button>
            <button class="my-button24" <?php if (!isset($level_status[24]) || !$level_status[24]) echo 'class="disabled"'; ?> onclick="window.location.href='intro1.php';">LEVEL 24</button>
        </section>
    </main>
    <footer>
        <p>&copy; 2025 Trading Card Game - Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>
