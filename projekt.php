<?php
// Starten der Session, um die Benutzerdaten zu verwenden
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist, falls nicht, Weiterleitung zur Login-Seite
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Falls nicht eingeloggt, zur Login-Seite weiterleiten
    exit();
}

// Verbindung zur Datenbank herstellen
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kartenspiel1_db";

// Verbindung zur MySQL-Datenbank herstellen
$conn = new mysqli($servername, $username, $password, $dbname);

// Überprüfen, ob die Verbindung zur Datenbank erfolgreich war
if ($conn->connect_error) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error);
}

// Benutzerdaten aus der Session holen
$user_id = $_SESSION['user_id'];

// Benutzerdaten aus der Datenbank abrufen
$sql = "SELECT benutzername, punkte, level FROM nutzer WHERE id = ?";
$stmt = $conn->prepare($sql);

// Überprüfen, ob die vorbereitete Anweisung erfolgreich war
if (!$stmt) {
    die("Fehler bei der SQL-Abfrage: " . $conn->error);
}

$stmt->bind_param("i", $user_id); // 'i' für Integer (user_id ist ein Integer)
$stmt->execute();
$result = $stmt->get_result();

// Überprüfen, ob Benutzerdaten vorhanden sind
if ($result->num_rows > 0) {
    // Benutzerdaten aus der Datenbank holen
    $row = $result->fetch_assoc();
    $benutzername = $row['benutzername'];
    $punkte = $row['punkte'];
    $level = $row['level'];
} else {
    // Falls kein Benutzer gefunden wurde
    die("Benutzer nicht gefunden.");
}

// Datenbankverbindung schließen
$conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Trading Card Game - Dein Profil</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <header>
        <h1>Willkommen im Trading Card Game</h1>
        <nav>
            <ul>
                <li><a href="#player">Spieler</a></li>
                <li><a href="#deck">Deck</a></li>
                <li><a href="#shop">Karten Shop</a></li>
                <li><a href="logout.php">Abmelden</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <!-- Spieler-Bereich -->
        <section id="player">
            <h2>Spieler Informationen</h2>
            <div class="player-info">
                <p><strong>Spielername:</strong> <?php echo $benutzername; ?></p>
                <p><strong>Punkte:</strong> <?php echo $punkte; ?></p>
                <p><strong>Level:</strong> <?php echo $level; ?></p>
            </div>
        </section>

        <!-- Deck-Bereich -->
        <section id="deck">
            <h2>Dein Deck</h2>
            <div class="deck-info">
                <p><strong>Deck Name:</strong> "Feuersturm"</p>
                <p><strong>Anzahl Karten:</strong> 40</p>
            </div>

            <!-- Beispiel für Karten im Deck -->
            <div class="deck-cards">
                <div class="card">
                    <h3>Feuerdrache</h3>
                    <p><strong>Typ:</strong> Kreatur</p>
                    <p><strong>Angriff:</strong> 2000</p>
                    <p><strong>Verteidigung:</strong> 1500</p>
                    <p><strong>Mana-Kosten:</strong> 5</p>
                    <button class="btn">Karte spielen</button>
                </div>

                <div class="card">
                    <h3>Magischer Schild</h3>
                    <p><strong>Typ:</strong> Zauber</p>
                    <p><strong>Effekt:</strong> Blockiert 1500 Schaden</p>
                    <p><strong>Mana-Kosten:</strong> 3</p>
                    <button class="btn">Karte spielen</button>
                </div>

                <!-- Weitere Karten können hier hinzugefügt werden -->
            </div>
        </section>

        <!-- Zufällige Charaktere Bereich -->
        <section id="random-characters">
            <h2>Wähle einen Charakter</h2>
            <p>Wähle einen Charakter aus, um deinen Level zu bearbeiten.</p>

            <div class="character-cards">
                <!-- Hier generieren wir 18 zufällige Charakterkarten -->
                <div class="card">
                    <h3>Charakter 1</h3>
                    <p><strong>Typ:</strong> Krieger</p>
                    <p><strong>Angriff:</strong> 1800</p>
                    <p><strong>Verteidigung:</strong> 1200</p>
                    <p><strong>Level:</strong> 1</p>
                    <button class="btn">Level bearbeiten</button>
                </div>

                <div class="card">
                    <h3>Charakter 2</h3>
                    <p><strong>Typ:</strong> Magier</p>
                    <p><strong>Angriff:</strong> 1500</p>
                    <p><strong>Verteidigung:</strong> 1300</p>
                    <p><strong>Level:</strong> 2</p>
                    <button class="btn">Level bearbeiten</button>
                </div>

                <!-- Weitere Charakterkarten -->
                <div class="card">
                    <h3>Charakter 3</h3>
                    <p><strong>Typ:</strong> Assassine</p>
                    <p><strong>Angriff:</strong> 2000</p>
                    <p><strong>Verteidigung:</strong> 1000</p>
                    <p><strong>Level:</strong> 3</p>
                    <button class="btn">Level bearbeiten</button>
                </div>
            </div>
        </section>

        <!-- Karten Shop -->
        <section id="shop">
            <h2>Karten Shop</h2>
            <p>Im Shop kannst du neue Karten kaufen und dein Deck erweitern!</p>
            <div class="shop-cards">
                <div class="shop-card">
                    <h3>Kartenpack 1</h3>
                    <p>Preis: 500 Punkte</p>
                    <button class="btn">Kaufen</button>
                </div>

                <div class="shop-card">
                    <h3>Kartenpack 2</h3>
                    <p>Preis: 1000 Punkte</p>
                    <button class="btn">Kaufen</button>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Trading Card Game - Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>
