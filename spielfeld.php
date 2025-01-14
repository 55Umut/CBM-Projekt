<?php
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Wenn nicht eingeloggt, zur Login-Seite weiterleiten
    exit();
}

// Benutzerinformationen aus der Session
$benutzername = $_SESSION['benutzername'];

// Abrufen der Charakter-ID des Spielers aus der nutzer_log Tabelle
$nutzer_id = $_SESSION['user_id']; // Benutzer-ID aus der Session

// Verbindung zur Datenbank
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kartenspiel1_db";

// Funktion zum Erstellen einer Datenbankverbindung
function createDbConnection($servername, $username, $password, $dbname) {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Verbindung fehlgeschlagen: " . $conn->connect_error);
    }
    return $conn;
}

$conn = createDbConnection($servername, $username, $password, $dbname);

// Abrufen des Charakters aus der nutzer_log Tabelle anhand der Benutzer-ID
$sql = "SELECT charakter_id FROM nutzer_log WHERE nutzer_id = ? AND aktion = 'Character selection' ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $nutzer_id);
$stmt->execute();
$result = $stmt->get_result();

$charakter_id = null;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $charakter_id = $row['charakter_id'];
} else {
    die("Kein Charakter ausgewählt.");
}

// Abrufen der Charakterdaten aus der charaktere Tabelle
$sql_charakter = "SELECT * FROM charaktere WHERE id = ?";
$stmt_charakter = $conn->prepare($sql_charakter);
$stmt_charakter->bind_param("i", $charakter_id);
$stmt_charakter->execute();
$result_charakter = $stmt_charakter->get_result();

$charakter = null;

if ($result_charakter->num_rows > 0) {
    $charakter = $result_charakter->fetch_assoc();
} else {
    die("Charakterdaten nicht gefunden.");
}

$charakter_name = $charakter['name'];
$charakter_angriff1 = $charakter['standardangriff1'];
$charakter_schaden1 = $charakter['schaden1'];
$charakter_angriff2 = $charakter['standardangriff2'];
$charakter_schaden2 = $charakter['schaden2'];
$charakter_angriff3 = $charakter['standardangriff3'];
$charakter_schaden3 = $charakter['schaden3'];
$charakter_spezialangriff = $charakter['spezialangriff'];
$charakter_schaden_spezial = $charakter['schaden_spezial'];
$charakter_bild_url = $charakter['bild_url'];
$charakter_lebenspunkte = $charakter['leben'];

// Sicherstellen, dass die Lebenspunkte des Charakters in der Session gesetzt werden
if (!isset($_SESSION['spieler_lebenspunkte']) && isset($charakter_lebenspunkte)) {
    $_SESSION['spieler_lebenspunkte'] = $charakter_lebenspunkte;
}

// Beispielwerte für Punkte
if (!isset($_SESSION['spieler_punkte'])) {
    $_SESSION['spieler_punkte'] = 0; // Initialisieren der Spieler-Punkte, falls sie nicht gesetzt sind
}

$spieler_punkte = $_SESSION['spieler_punkte'];  // Spielerpunkte
$gegner_punkte = 30; // Beispielwert für Gegnerpunkte

// Initialisiere den Boss nur einmal zu Beginn des Spiels
if (!isset($_SESSION['boss_name'])) {
    include_once 'bosse.php';

    // Einen zufälligen Boss abrufen
    $randomBoss = Boss::getRandomBoss();

    // Speichern des Bosses in der Session
    $_SESSION['boss_name'] = $randomBoss->getName();
    $_SESSION['boss_spezialattacke'] = $randomBoss->getSpezialattacke();
    $_SESSION['boss_schaden_spezial'] = $randomBoss->getSchadenSpezial();
    $_SESSION['boss_lebenspunkte'] = $randomBoss->getLeben();
    $_SESSION['boss_bild_url'] = $randomBoss->getBildUrl();
    $_SESSION['boss_angriff1'] = $randomBoss->getStandardangriff1();
    $_SESSION['boss_schaden1'] = $randomBoss->getSchaden1();
    $_SESSION['boss_angriff2'] = $randomBoss->getStandardangriff2();
    $_SESSION['boss_schaden2'] = $randomBoss->getSchaden2();
    $_SESSION['boss_angriff3'] = $randomBoss->getStandardangriff3();
    $_SESSION['boss_schaden3'] = $randomBoss->getSchaden3();
}

// Abrufen des gespeicherten Bosses aus der Session
$boss_name = $_SESSION['boss_name'];
$boss_spezialattacke = $_SESSION['boss_spezialattacke'];
$boss_schaden_spezial = $_SESSION['boss_schaden_spezial'];
$boss_lebenspunkte = $_SESSION['boss_lebenspunkte'];
$boss_bild_url = $_SESSION['boss_bild_url'];
$boss_angriff1 = $_SESSION['boss_angriff1'];
$boss_schaden1 = $_SESSION['boss_schaden1'];
$boss_angriff2 = $_SESSION['boss_angriff2'];
$boss_schaden2 = $_SESSION['boss_schaden2'];
$boss_angriff3 = $_SESSION['boss_angriff3'];
$boss_schaden3 = $_SESSION['boss_schaden3'];

// Wenn der Spieler einen Angriff wählt
if (isset($_POST['angriff'])) {
    $spieler_angriff = $_POST['angriff'];
    
    // Schaden basierend auf der Auswahl des Spielers
    switch ($spieler_angriff) {
        case 'angriff1':
            $spieler_schaden = $charakter_schaden1;
            break;
        case 'angriff2':
            $spieler_schaden = $charakter_schaden2;
            break;
        case 'angriff3':
            $spieler_schaden = $charakter_schaden3;
            break;
        case 'spezial':
            $spieler_schaden = $charakter_schaden_spezial;
            break;
        default:
            $spieler_schaden = 0;
    }

    // Boss verliert Lebenspunkte
    $_SESSION['boss_lebenspunkte'] -= $spieler_schaden;

    // Zufälliger Boss-Angriff
    $boss_angriff = rand(1, 3); // Zufällig zwischen den Angriffen des Bosses wählen
    switch ($boss_angriff) {
        case 1:
            $boss_schaden = $boss_schaden1;
            break;
        case 2:
            $boss_schaden = $boss_schaden2;
            break;
        case 3:
            $boss_schaden = $boss_schaden3;
            break;
    }

    // Spieler verliert Lebenspunkte
    $_SESSION['spieler_lebenspunkte'] -= $boss_schaden;

    // Spielrunde beenden, wenn einer besiegt wurde
    if ($_SESSION['boss_lebenspunkte'] <= 0) {
        $_SESSION['boss_lebenspunkte'] = 0;
        $_SESSION['spieler_punkte'] += 10; // Spieler erhält Punkte
    }
    
    if ($_SESSION['spieler_lebenspunkte'] <= 0) {
        $_SESSION['spieler_lebenspunkte'] = 0;
        $_SESSION['gegner_punkte'] += 10; // Boss erhält Punkte
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codebreakers - Battle of Minds</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>
    <!-- Clouds als Hintergrund -->
    <div class="clouds">
        <div class="clouds-1"></div>
        <div class="clouds-2"></div>
        <div class="clouds-3"></div>
    </div>

    <header>
        <h1>Willkommen zurück, <?php echo htmlspecialchars($benutzername); ?>!</h1>
        <p>Du spielst als: <?php echo htmlspecialchars($charakter_name); ?></p>
        <p>Deine aktuellen Punkte: <?php echo $spieler_punkte; ?></p>
    </header>

    <main>
        <div class="game-container">
            <!-- Gegnerbereich (fixiert oben) -->
            <section class="opponent">
                <div class="player-info">
                    <div class="boss-layout">
                        <!-- Links: 2 Standardangriffe -->
                        <div class="attacks-left">
                            <p class="attack">
                                <?php echo htmlspecialchars($boss_angriff1) . " - Schaden: " . $boss_schaden1; ?>
                            </p>
                            <p class="attack">
                                <?php echo htmlspecialchars($boss_angriff2) . " - Schaden: " . $boss_schaden2; ?>
                            </p>
                        </div>

                        <!-- Bild des Bosses -->
                        <div class="boss-image-container">
                            <?php if (!empty($boss_bild_url)): ?>
                                <img src="<?php echo $boss_bild_url; ?>" alt="Bild des Bosses" class="boss-image">
                            <?php else: ?>
                                <p>Kein Bild verfügbar.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Rechts: 1 Standardangriff und Spezialattacke -->
                        <div class="attacks-right">
                            <p class="attack">
                                <?php echo htmlspecialchars($boss_angriff3) . " - Schaden: " . $boss_schaden3; ?>
                            </p>
                            <p class="attack">
                                <?php echo htmlspecialchars($boss_spezialattacke) . " - Schaden: " . $boss_schaden_spezial; ?>
                            </p>
                        </div>
                    </div>

                    <div class="health-bar-container">
                        <div class="hs-wrapper gold">
                            <p class="hs-text gold">
                                <?php echo htmlspecialchars($boss_name); ?>
                            </p>
                        </div>
                        <div class="health-bar--outline">
                            <div class="health-bar--border">
                                <div class="health-bar--background">
                                    <div class="health-bar--health" style="--health: <?php echo $_SESSION['boss_lebenspunkte']; ?>; --max-health: 150;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="health-indicator">
                            <p>Lebenspunkte: <?php echo $_SESSION['boss_lebenspunkte']; ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Spielerbereich (fixiert unten) -->
            <section class="player">
                <div class="player-info">
                    <div class="player-layout">
                        <!-- Links: 2 Standardangriffe -->
                        <form method="POST">
                            <div class="attack-buttons">
                                <div class="attacks-left">
                                    <button type="submit" name="angriff" value="angriff1"><?php echo htmlspecialchars($charakter_angriff1) . " - Schaden: " . $charakter_schaden1; ?></button>
                                    <button type="submit" name="angriff" value="angriff2"><?php echo htmlspecialchars($charakter_angriff2) . " - Schaden: " . $charakter_schaden2; ?></button>
                                </div>
                            </div>
                        </form>

                        <!-- Bild des Charakters -->
                        <div class="player-image-container">
                            <?php if (!empty($charakter_bild_url)): ?>
                                <img src="<?php echo $charakter_bild_url; ?>" alt="Bild des Charakters" class="player-image">
                            <?php else: ?>
                                <p>Kein Bild verfügbar.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Rechts: 1 Standardangriff und Spezialattacke -->
                        <form method="POST">
                            <div class="attacks-right">
                                <button type="submit" name="angriff" value="angriff3"><?php echo htmlspecialchars($charakter_angriff3) . " - Schaden: " . $charakter_schaden3; ?></button>
                                <button type="submit" name="angriff" value="spezial"><?php echo htmlspecialchars($charakter_spezialangriff) . " - Schaden: " . $charakter_schaden_spezial; ?></button>
                            </div>
                        </form>
                    </div>

                    <div class="health-bar-container">
                        <div class="hs-wrapper gold">
                            <p class="hs-text gold">
                                <?php echo htmlspecialchars($charakter_name); ?>
                            </p>
                        </div>
                        <div class="health-bar--outline">
                            <div class="health-bar--border">
                                <div class="health-bar--background">
                                    <div class="health-bar--health" style="--health: <?php echo $_SESSION['spieler_lebenspunkte']; ?>; --max-health: 150;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="health-indicator">
                            <p>Lebenspunkte: <?php echo $_SESSION['spieler_lebenspunkte']; ?></p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
<footer>
    <p>&copy; 2025 Codebreakers - Battle of Minds Trading Card Game - Alle Rechte vorbehalten.</p>
</footer>
</html>
