<?php
session_start();

// Datenbankverbindung
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kartenspiel1_db";

// Funktion zum Erstellen einer sicheren Datenbankverbindung
function createDbConnection($servername, $username, $password, $dbname) {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Verbindung fehlgeschlagen: " . $conn->connect_error);
    }
    return $conn;
}

$conn = createDbConnection($servername, $username, $password, $dbname);

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    die("Benutzer nicht eingeloggt.");
}

// Benutzerinformationen aus der Session
$nutzer_id = $_SESSION['user_id'];
$benutzername = $_SESSION['benutzername'];

// Array mit den relevanten Feldern
$fields = [
    'aktion' => 'Kein Eintrag',
    'level' => 1,
    'punkte' => 0,
    'charakter_id' => null,
    'login' => 'Aktiv',
    'status' => 'Aktiv',
    'details' => 'Kein Detail'
];

// Schritt 1: Überprüfen der Session und ggf. Datenbank
foreach ($fields as $field => $default) {
    // Wenn der Wert bereits in der Session existiert, übernehme ihn
    if (!isset($_SESSION[$field])) {
        // Wenn der Wert nicht in der Session vorhanden ist, schaue in der Datenbank nach
        $stmt = $conn->prepare("SELECT $field FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("i", $nutzer_id);
        $stmt->execute();
        $stmt->bind_result($value);
        $stmt->fetch();
        
        // Setze den Wert in die Session, wenn er aus der Datenbank kommt
        $_SESSION[$field] = $value ? $value : $default;
    }
}

// Schritt 2: Speichern der Daten in der nutzer_log Tabelle
$sql = "INSERT INTO nutzer_log (nutzer_id, aktion, level, punkte, charakter_id, login, status, details) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isiiisss", $nutzer_id, $_SESSION['aktion'], $_SESSION['level'], $_SESSION['punkte'], 
                                $_SESSION['charakter_id'], $_SESSION['login'], $_SESSION['status'], $_SESSION['details']);
$stmt->execute();

// Ausgabe der Session-Daten (optional)
echo "Benutzer-ID: " . htmlspecialchars($nutzer_id) . "<br>";
echo "Aktion: " . htmlspecialchars($_SESSION['aktion']) . "<br>";
echo "Level: " . htmlspecialchars($_SESSION['level']) . "<br>";
echo "Punkte: " . htmlspecialchars($_SESSION['punkte']) . "<br>";
echo "Charakter-ID: " . htmlspecialchars($_SESSION['charakter_id']) . "<br>";
echo "Login-Status: " . htmlspecialchars($_SESSION['login']) . "<br>";
echo "Status: " . htmlspecialchars($_SESSION['status']) . "<br>";
echo "Details: " . htmlspecialchars($_SESSION['details']) . "<br>";
?>








<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start - Trading Card Game</title>
    <link rel="stylesheet" href="styles1.css"> <!-- Verweis auf das Stylesheet -->
</head>
<body>
    <!-- Wolken-Background -->
    <div class="clouds">
        <div class="clouds-1"></div>
        <div class="clouds-2"></div>
        <div class="clouds-3"></div>
    </div>

    <header>
        <div class="container">
            <h1>Willkommen, <?php echo htmlspecialchars($benutzername); ?>!</h1>
            <!-- Navigation -->
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
        </div>
    </header>

    <main>
        <section>
            <h2>Dein Abenteuer beginnt!</h2>
            <p>Willkommen im Trading Card Game. Hier kannst du dein Abenteuer starten und gegen andere Spieler antreten.</p>
            <p>Wähle eine der Optionen im Menü, um zu deinem Deck zu gehen oder neue Karten zu kaufen.</p>
            <!-- Los-Button -->
            <form action="abenteuer.php" method="GET">
                <button type="submit" class="btn">Los!</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Codebreakers - Battle of Minds Trading Card Game - Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>
