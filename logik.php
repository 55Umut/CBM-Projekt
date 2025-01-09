<?php
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Wenn nicht eingeloggt, zur Login-Seite weiterleiten
    exit();
}

// Benutzerinformationen aus der Session
$benutzername = $_SESSION['benutzername'];

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

// Liste der Charaktere aus der Datenbank abrufen (mit Bild-URL)
$sql = "SELECT id, name, bild_url FROM charaktere"; 
$result = $conn->query($sql);

// Array für die Charaktere
$charaktere = [];

if ($result->num_rows > 0) {
    // Ergebnisse in das Array laden
    while ($row = $result->fetch_assoc()) {
        $charaktere[] = $row;
    }
} else {
    echo "Keine Charaktere gefunden.";
}

$conn->close();

// Charakterauswahl-Funktion
if (isset($_POST['charakter'])) {
    $_SESSION['charakter'] = $_POST['charakter']; // Den ausgewählten Charakter speichern
    header("Location: spiel.php"); // Weiterleitung zur nächsten Seite nach der Auswahl
    exit();
}

// DB-Verbindung und Klassen laden
class DB {
    private $host = 'localhost';
    private $dbname = 'kartenspiel1_db'; // Hier den richtigen DB-Namen verwenden
    private $username = 'root'; // Dein Datenbank-Benutzername
    private $password = ''; // Dein Datenbank-Passwort
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Verbindungsfehler: ' . $e->getMessage();
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}

class Charakter {
    public $name;
    public $leben;
    public $angriffe;

    public function __construct($name, $leben, $angriffe) {
        $this->name = $name;
        $this->leben = $leben;
        $this->angriffe = $angriffe;
    }

    public function greifeAn($ziel) {
        $angriff = $this->angriffe[array_rand($this->angriffe)];
        $ziel->leben -= $angriff;
        return $angriff;
    }

    public static function ladeCharakter($charakter_id, $db) {
        // Hier wird nach der richtigen Spalte gesucht, z.B. 'id' statt 'charakter_id'
        $stmt = $db->prepare("SELECT * FROM charaktere WHERE id = :id");
        $stmt->bindParam(':id', $charakter_id);
        $stmt->execute();
        $charakter = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt_angriffe = $db->prepare("SELECT * FROM angriffe WHERE charakter_id = :charakter_id");
        $stmt_angriffe->bindParam(':charakter_id', $charakter_id);
        $stmt_angriffe->execute();
        $angriffe = $stmt_angriffe->fetchAll(PDO::FETCH_ASSOC);
        $angriffs_werte = [];
        foreach ($angriffe as $angriff) {
            $angriffs_werte[] = $angriff['schaden'];
        }

        return new self($charakter['name'], $charakter['leben'], $angriffs_werte);
    }
}

class Boss {
    public $name;
    public $leben;
    public $angriffe;

    public function __construct($name, $leben, $angriffe) {
        $this->name = $name;
        $this->leben = $leben;
        $this->angriffe = $angriffe;
    }

    public function greifeAn($ziel) {
        $angriff = $this->angriffe[array_rand($this->angriffe)];
        $ziel->leben -= $angriff;
        return $angriff;
    }

    public static function ladeBoss($boss_id, $db) {
        $stmt = $db->prepare("SELECT * FROM bosse WHERE id = :id");
        $stmt->bindParam(':id', $boss_id);
        $stmt->execute();
        $boss = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt_angriffe = $db->prepare("SELECT * FROM angriffe WHERE boss_id = :boss_id");
        $stmt_angriffe->bindParam(':boss_id', $boss_id);
        $stmt_angriffe->execute();
        $angriffe = $stmt_angriffe->fetchAll(PDO::FETCH_ASSOC);
        $angriffs_werte = [];
        foreach ($angriffe as $angriff) {
            $angriffs_werte[] = $angriff['schaden'];
        }

        return new self($boss['name'], $boss['leben'], $angriffs_werte);
    }
}

class Kampf {
    public $charakter;
    public $boss;
    public $runde = 1;
    public $status = 'laufend';
    public $db;
    public $user_id;
    public $charakter_id;
    public $punkte;

    public function __construct($charakter, $boss, $db, $user_id, $charakter_id) {
        $this->charakter = $charakter;
        $this->boss = $boss;
        $this->db = $db;
        $this->user_id = $user_id;
        $this->charakter_id = $charakter_id;
    }

    public function starteRunde() {
        // Wenn einer der beiden tot ist, wird der Kampf beendet
        if ($this->charakter->leben <= 0 || $this->boss->leben <= 0) {
            $this->status = 'beendet';
            $this->updatePunkte();
            return;
        }

        // Spieler greift an
        $angriff = $this->charakter->greifeAn($this->boss);
        echo "Charakter greift Boss mit Angriff ($angriff) an.\n";

        // Boss greift an
        $angriff = $this->boss->greifeAn($this->charakter);
        echo "Boss greift Charakter mit Angriff ($angriff) an.\n";

        $this->runde++;

        // Speichere die Runde und Punkte
        $this->updateRunde();
    }

    private function updateRunde() {
        // Update der Runde in der Datenbank
        $stmt = $this->db->prepare("UPDATE nutzer SET runde = :runde WHERE id = :id");
        $stmt->bindParam(':runde', $this->runde);
        $stmt->bindParam(':id', $this->user_id);
        $stmt->execute();
    }

    private function updatePunkte() {
        // Berechnung der Punkte basierend auf der Runde
        $punkte_gewonnen = 10 * ($this->runde); // Beispiel: Punkte pro Runde
        $this->punkte += $punkte_gewonnen;

        // Punkte in der Datenbank aktualisieren
        $stmt = $this->db->prepare("UPDATE nutzer SET punkte = :punkte WHERE id = :id");
        $stmt->bindParam(':punkte', $this->punkte);
        $stmt->bindParam(':id', $this->user_id);
        $stmt->execute();
    }
}

// DB-Verbindung herstellen
$db = new DB();
$connection = $db->getConnection();

// Nutzer und Charakter ID annehmen (könnte über Session oder GET übergeben werden)
$user_id = $_SESSION['user_id']; // Beispiel-Nutzer-ID
$charakter_id = $_SESSION['charakter']; // Ausgewählte Charakter-ID aus der Session

// Lade Charakter und Boss aus der DB
$charakter = Charakter::ladeCharakter($charakter_id, $connection);
$boss = Boss::ladeBoss(1, $connection); // Boss mit ID 1

// Starte den Kampf
$kampf = new Kampf($charakter, $boss, $connection, $user_id, $charakter_id);

// Solange der Kampf läuft, starten wir neue Runden
while ($kampf->status == 'laufend') {
    $kampf->starteRunde();
}

echo "Kampf beendet. Spieler hat " . $kampf->punkte . " Punkte.\n";

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charakter Auswahl</title>
    <link rel="stylesheet" href="style.css"> <!-- Dein Stylesheet -->
</head>
<body>
    <div class="container">
        <h1>Willkommen, <?php echo htmlspecialchars($benutzername); ?>!</h1>

        <h2>Wähle deinen Charakter:</h2>
        <form method="post">
            <div class="charakter-liste">
                <?php foreach ($charaktere as $char): ?>
                    <div class="charakter">
                        <input type="radio" name="charakter" value="<?php echo $char['id']; ?>" id="char_<?php echo $char['id']; ?>" required>
                        <label for="char_<?php echo $char['id']; ?>">
                            <img src="<?php echo $char['bild_url']; ?>" alt="Bild von <?php echo $char['name']; ?>" class="charakter-bild">
                            <p><?php echo $char['name']; ?></p>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="submit">Charakter auswählen</button>
        </form>

        <h2>Dein aktueller Fortschritt:</h2>
        <p>Punkte: <?php echo isset($_SESSION['punkte']) ? $_SESSION['punkte'] : 0; ?></p>
        <p>Level: <?php echo isset($_SESSION['level']) ? $_SESSION['level'] : 1; ?></p>

        <a href="logout.php">Abmelden</a> <!-- Abmeldemöglichkeit -->
    </div>
</body>
</html>
