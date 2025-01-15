<?php
session_start(); // Stellt sicher, dass die Session gestartet wird

// Stellen Sie sicher, dass eine gültige Benutzersitzung besteht
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Wenn der Benutzer nicht eingeloggt ist, auf die Login-Seite weiterleiten
    exit();
}

// Benutzer-ID und andere Session-Daten abrufen
$nutzer_id = $_SESSION['user_id'];
$benutzername = $_SESSION['benutzername'];
$charakter = isset($_SESSION['charakter']) ? (int) $_SESSION['charakter'] : ''; // Defaultwert, falls nicht gesetzt
$level = isset($_SESSION['level']) ? (int) $_SESSION['level'] : ''; // Defaultwert, falls nicht gesetzt
$punkte = isset($_SESSION['punkte']) ? (int) $_SESSION['punkte'] :''; 

// Verbindung zur Datenbank herstellen
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kartenspiel1_db";
$conn = new mysqli($servername, $username, $password, $dbname);

// Überprüfen, ob die Verbindung erfolgreich war
if ($conn->connect_error) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error);
}

// Nutzer-ID aus der Session
$nutzer_id = $_SESSION['user_id']; // Beispiel: Nutzer-ID wird aus der Session gezogen

// 1. Level abfragen
if (isset($_SESSION['level'])) {
    $level = $_SESSION['level'];  // Aus der Session verwenden, falls vorhanden
} else {
    // Level aus der Datenbank holen, wenn nicht in der Session
    $sql_level = "SELECT level FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1";
    $stmt_level = $conn->prepare($sql_level);
    $stmt_level->bind_param("i", $nutzer_id);
    $stmt_level->execute();
    $stmt_level->bind_result($level);
    $stmt_level->fetch();
    $stmt_level->close();
    $_SESSION['level'] = $level; // Speichern des Levels in der Session für zukünftige Anfragen
}

// 2. Punkte abfragen
if (isset($_SESSION['punkte'])) {
    $punkte = $_SESSION['punkte'];  // Aus der Session verwenden, falls vorhanden
} else {
    // Punkte aus der Datenbank holen, wenn nicht in der Session
    $sql_punkte = "SELECT punkte FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1";
    $stmt_punkte = $conn->prepare($sql_punkte);
    $stmt_punkte->bind_param("i", $nutzer_id);
    $stmt_punkte->execute();
    $stmt_punkte->bind_result($punkte);
    $stmt_punkte->fetch();
    $stmt_punkte->close();
    $_SESSION['punkte'] = $punkte; // Speichern der Punkte in der Session für zukünftige Abfragen
}

// 3. Charakter-ID abfragen
if (isset($_SESSION['charakter_id'])) {
    $charakter_id = $_SESSION['charakter_id'];  // Aus der Session verwenden, falls vorhanden
} else {
    // Charakter-ID aus der Datenbank holen, wenn nicht in der Session
    $sql_charakter = "SELECT charakter_id FROM nutzer_log WHERE nutzer_id = ? AND aktion = 'Character selection' ORDER BY id DESC LIMIT 1";
    $stmt_charakter = $conn->prepare($sql_charakter);
    $stmt_charakter->bind_param("i", $nutzer_id);
    $stmt_charakter->execute();
    $stmt_charakter->bind_result($charakter_id);
    $stmt_charakter->fetch();
    $stmt_charakter->close();
    $_SESSION['charakter_id'] = $charakter_id; // Speichern der Charakter-ID in der Session für zukünftige Abfragen
}

// Array mit den relevanten Feldern
$fields = [
    'aktion' => 'Kein Eintrag',
    'level' => $level,  // Abgefragtes Level
    'punkte' => $punkte,  // Abgefragte Punkte
    'charakter_id' => $charakter_id,  // Abgefragte Charakter-ID
    'login' => 'Aktiv',  // Wird jetzt durch NOW() in SQL ersetzt
    'status' => 'Aktiv',
    'details' => 'Kein Detail'
];

// Schritt 1: Überprüfen der Session und ggf. Datenbank
foreach ($fields as $field => $default) {
    // Wenn der Wert bereits in der Session existiert, übernehme ihn
    if (!isset($_SESSION[$field])) {
        // Wenn der Wert nicht in der Session vorhanden ist, schaue in der Datenbank nach
        $stmt = $conn->prepare("SELECT $field FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1");

        if (!$stmt) {
            // Fehler beim Vorbereiten der SQL-Abfrage
            die("Fehler bei der SQL-Abfrage: " . $conn->error);
        }

        $stmt->bind_param("i", $nutzer_id);
        $stmt->execute();
        $stmt->bind_result($value);
        $stmt->fetch();
        
        // Setze den Wert in die Session, wenn er aus der Datenbank kommt
        $_SESSION[$field] = $value ? $value : $default;
        $stmt->close();
    }
}

// Wenn der Benutzer ein Level auswählt, protokollieren wir es in der nutzer_log-Tabelle
if (isset($_GET['level'])) {
    // Sicherstellen, dass das Level ausgewählt wurde
    if (!empty($_GET['level'])) {
        $selected_level = (int) $_GET['level'];

        // Sicherstellen, dass das Level in der Datenbank existiert
        $stmt_check = $conn->prepare("SELECT level FROM level_system WHERE level = ?");
        if (!$stmt_check) {
            // Fehler beim Vorbereiten der SQL-Abfrage
            die("Fehler bei der SQL-Abfrage: " . $conn->error);
        }

        $stmt_check->bind_param("i", $selected_level);
        $stmt_check->execute();
        $stmt_check->store_result();
         
        if ($stmt_check->num_rows > 0) {
            // Das Level in der Session speichern
            $_SESSION['level'] = $selected_level;

            // Log in die Datenbank einfügen
            $stmt_insert = $conn->prepare("INSERT INTO nutzer_log (nutzer_id, aktion, level, punkte, charakter_id, login, status, details) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)");
            if (!$stmt_insert) {
                // Fehler beim Vorbereiten der SQL-Abfrage
                die("Fehler bei der SQL-Abfrage: " . $conn->error);
            }

            $aktion = 'Level selection';
            $punkte = $_SESSION['punkte'];
            $charakter_id = $_SESSION['charakter'];
            $status = 'Aktiv';
            $details = "Benutzer {$_SESSION['user_id']} hat Level $selected_level ausgewählt.";

            // Die aktuelle Zeit wird nun durch `NOW()` in der SQL-Abfrage automatisch gesetzt
            $stmt_insert->bind_param("isiiiss", $nutzer_id, $aktion, $selected_level, $punkte, $charakter_id, $status, $details);
            $stmt_insert->execute();
            $stmt_insert->close();
        }

        $stmt_check->close();

        header('location: intro1.php');
        exit;
    }
}

// Abfrage des Charakters (wie gehabt, nur zur Information)
$stmt = $conn->prepare("SELECT `name` FROM charaktere WHERE id = ?");
$stmt->bind_param("i", $charakter);
$stmt->execute();
$stmt->bind_result($charakter_name);
$stmt->fetch();
$stmt->close();

// Hole die aktuellen Punkte aus der nutzer_log-Tabelle
$stmt = $conn->prepare("SELECT punkte FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $nutzer_id);  // "i" für Integer (nutzer_id ist eine Zahl)
$stmt->execute();
$stmt->bind_result($punkte);
$stmt->fetch();
$stmt->close();
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

        /* Visuelle Darstellung der aktiven Buttons */
        .enabled {
            background-color: #4CAF50;
            color: white;
        }

        .section {
            margin-bottom: 20px;
        }

        .button-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover:not(.disabled) {
            background-color: #45a049;
        }

        footer {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="start.php">Startseite</a></li>
                <li><a href="deck.php">Mein Deck</a></li>
                <li><a href="shop.php">Karten Shop</a></li>
            </ul>
        </nav>
        <!-- Logout-Formular -->
        <form action="start.php" method="POST">
            <button type="submit" name="logout" class="btn">Abmelden</button>
        </form>
        <h1>Willkommen zurück, <?php echo htmlspecialchars($benutzername); ?>!</h1>
        <p>Du spielst als: <?php echo htmlspecialchars($charakter_name); ?></p>
        <p>Deine aktuellen Punkte: <?php echo htmlspecialchars($punkte); ?></p>
    </header>

    <main>
        <section class="section">
            <div class="button-container">
                <!-- LEVEL 1 immer aktiv, auch wenn der Benutzer keine Punkte hat -->
                <button class="my-button1 <?php echo ($punkte >= 0) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='spiel.php?level=1';">LEVEL 1</button>
                <button class="my-button2 <?php echo ($punkte >= 100) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='spiel.php?level=2';">LEVEL 2</button>
                <button class="my-button3 <?php echo ($punkte >= 250) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='spiel.php?level=3';">LEVEL 3</button>
                <button class="my-button4 <?php echo ($punkte >= 500) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='spiel.php?level=4';">LEVEL 4</button>
                <button class="my-button5 <?php echo ($punkte >= 1000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro5.php';">LEVEL 5</button>
                <button class="my-button6 <?php echo ($punkte >= 2000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro6.php';">LEVEL 6</button>
            </div>
        </section>

        <section class="section">
            <div class="button-container">
                <button class="my-button7 <?php echo ($punkte >= 4000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro7.php';">LEVEL 7</button>
                <button class="my-button8 <?php echo ($punkte >= 8000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro8.php';">LEVEL 8</button>
                <button class="my-button9 <?php echo ($punkte >= 16000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro9.php';">LEVEL 9</button>
                <button class="my-button10 <?php echo ($punkte >= 32000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro10.php';">LEVEL 10</button>
                <button class="my-button11 <?php echo ($punkte >= 64000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro11.php';">LEVEL 11</button>
                <button class="my-button12 <?php echo ($punkte >= 128000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro12.php';">LEVEL 12</button>
            </div>
        </section>

        <section class="section">
            <div class="button-container">
                <button class="my-button13 <?php echo ($punkte >= 256000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro13.php';">LEVEL 13</button>
                <button class="my-button14 <?php echo ($punkte >= 512000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro14.php';">LEVEL 14</button>
                <button class="my-button15 <?php echo ($punkte >= 1024000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro15.php';">LEVEL 15</button>
                <button class="my-button16 <?php echo ($punkte >= 2048000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro16.php';">LEVEL 16</button>
                <button class="my-button17 <?php echo ($punkte >= 4096000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro17.php';">LEVEL 17</button>
                <button class="my-button18 <?php echo ($punkte >= 8192000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro18.php';">LEVEL 18</button>
            </div>
        </section>

        <section class="section">
            <div class="button-container">
                <button class="my-button19 <?php echo ($punkte >= 16384000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro19.php';">LEVEL 19</button>
                <button class="my-button20 <?php echo ($punkte >= 32768000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro20.php';">LEVEL 20</button>
                <button class="my-button21 <?php echo ($punkte >= 65536000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro21.php';">LEVEL 21</button>
                <button class="my-button22 <?php echo ($punkte >= 131072000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro22.php';">LEVEL 22</button>
                <button class="my-button23 <?php echo ($punkte >= 262144000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro23.php';">LEVEL 23</button>
                <button class="my-button24 <?php echo ($punkte >= 524288000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro24.php';">LEVEL 24</button>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Codebreakers - Battle of Minds Trading Card Game - Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>

<?php
// Schließe die Datenbankverbindung
$conn->close();
?>
