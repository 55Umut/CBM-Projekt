<?php
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Wenn nicht eingeloggt, zur Login-Seite weiterleiten
    exit();
}

// Benutzerinformationen aus der Session
$benutzername = $_SESSION['benutzername'];
$charakter_name = isset($_SESSION['charakter']) ? $_SESSION['charakter'] : '';

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

// Lade die Charakterdaten des Spielers
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kartenspiel1_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

$charakterQuery = "SELECT * FROM charaktere WHERE name = '$charakter_name'";
$result_charakter = $conn->query($charakterQuery);

if ($result_charakter->num_rows > 0) {
    $charakter = $result_charakter->fetch_assoc();
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
    $charakter_lebenspunkte = $charakter['leben']; // Verwende "leben" statt "lebenspunkte" aus der DB

    // Sicherstellen, dass die Lebenspunkte des Charakters in der Session gesetzt werden
    if (!isset($_SESSION['spieler_lebenspunkte']) && isset($charakter_lebenspunkte)) {
        $_SESSION['spieler_lebenspunkte'] = $charakter_lebenspunkte;
    }
} else {
    echo "Charakter nicht gefunden!";
}

$conn->close();

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
                <!-- Attacken Buttons innerhalb des Spielerbereichs -->
                <div class="attack-buttons">
                    <div class="attacks-left">
                        <button type="submit" name="angriff" value="angriff1"><?php echo htmlspecialchars($charakter_angriff1) . " - Schaden: " . $charakter_schaden1; ?></button>
                        <button type="submit" name="angriff" value="angriff2"><?php echo htmlspecialchars($charakter_angriff2) . " - Schaden: " . $charakter_schaden2; ?></button>
                    </div></form>

                        <!-- Bild des Charakters -->
                        <div class="player-image-container">
                            <?php if (!empty($charakter_bild_url)): ?>
                                <img src="<?php echo $charakter_bild_url; ?>" alt="Bild des Charakters" class="player-image">
                            <?php else: ?>
                                <p>Kein Bild verfügbar.</p>
                            <?php endif; ?>
                        </div>

                         <form method="POST">
                    <div class="attacks-right">
                        <button type="submit" name="angriff" value="angriff3"><?php echo htmlspecialchars($charakter_angriff3) . " - Schaden: " . $charakter_schaden3; ?></button>
                        <button type="submit" name="angriff" value="spezial"><?php echo htmlspecialchars($charakter_spezialangriff) . " - Schaden: " . $charakter_schaden_spezial; ?></button>
                    </div>
                </div>
            </form>

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
        <p>Codebreakers - Battle of Minds Trading Card Game - Alle Rechte vorbehalten. &copy;2025</p>
    </footer>
</html>
###

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

// Abfrage der Levels und deren Punktgrenzen
$sql_levels = "SELECT * FROM level_system ORDER BY level";
$result_levels = $conn->query($sql_levels);

// Array zur Verknüpfung der Level mit den Freischaltbedingungen
$level_status = [];
if ($result_levels->num_rows > 0) {
    while ($row = $result_levels->fetch_assoc()) {
        // Wenn der Benutzer genug Punkte hat, wird das Level freigeschaltet
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
        <!-- Dynamische Level-Anzeige -->
        <?php for ($i = 1; $i <= 24; $i++): ?>
            <section class="section<?php echo ceil($i / 6); ?>">
                <!-- Button für jedes Level -->
                <button class="my-button<?php echo $i; ?>" 
                    <?php if (isset($level_status[$i]) && !$level_status[$i]) echo 'class="disabled"'; ?> 
                    onclick="window.location.href='intro<?php echo $i; ?>.php';">
                    LEVEL <?php echo $i; ?>
                </button>
            </section>
        <?php endfor; ?>
    </main>
    <footer>
        <p>&copy; 2025 Trading Card Game - Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>
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

    // Wenn der Boss besiegt wird
    if ($_SESSION['boss_lebenspunkte'] <= 0) {
        $_SESSION['boss_lebenspunkte'] = 0;
        $_SESSION['spieler_punkte'] += 101; // Spieler erhält 101 Punkte
        
        // Hier Details zur nutzer_log Tabelle hinzufügen (Boss besiegt)
        $sql_update_punkte = "INSERT INTO nutzer_log (nutzer_id, aktion, punkte, details) VALUES (?, 'Boss besiegt', ?, ?)";
        $details = "Benutzer: $benutzername, Level: 1, Charakter: $charakter_name"; // Details hier festlegen
        $stmt_update_punkte = $conn->prepare($sql_update_punkte);
        $stmt_update_punkte->bind_param("iis", $nutzer_id, $_SESSION['spieler_punkte'], $details);
        $stmt_update_punkte->execute();



        $sql_update_punkte = "INSERT INTO nutzer_log (nutzer_id, aktion, punkte, level, charakter_id, status, details) VALUES ($nutzer_id, 'Boss besiegt', $_SESSION['spieler_punkte'], $_SESSION['level'], $_SESSION['charakter_id'], 'Gewonnen', ?)";
        $details = "Benutzer: $benutzername, Level: {$_SESSION['level']}, Charakter-ID: {$_SESSION['charakter_id']}, Charakter: $charakter_name"; // Details hier festlegen
        $status = "Gewonnen";
        $stmt_update_punkte = $conn->prepare($sql_update_punkte);
        $stmt_update_punkte->bind_param("iisiss", $nutzer_id, $_SESSION['spieler_punkte'], $_SESSION['level'], $_SESSION['charakter_id'], $status, $details);
        $stmt_update_punkte->execute();




    }
    
    // Falls der Spieler besiegt wurde
    if ($_SESSION['spieler_lebenspunkte'] <= 0) {
        $_SESSION['spieler_lebenspunkte'] = 0;
    }
}

// Aktuelle Punkte aus der nutzer_log Tabelle abfragen
$sql_punkte = "SELECT punkte FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1";
$stmt_punkte = $conn->prepare($sql_punkte);
$stmt_punkte->bind_param("i", $nutzer_id);
$stmt_punkte->execute();
$result_punkte = $stmt_punkte->get_result();

if ($result_punkte->num_rows > 0) {
    $row_punkte = $result_punkte->fetch_assoc();
    $spieler_punkte = $row_punkte['punkte'];  // Aktuelle Punkte des Spielers
} else {
    $spieler_punkte = 0;  // Falls keine Punkte gefunden wurden
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
                        <div class="attacks-right">
                            <form method="POST">
                                <button type="submit" name="angriff" value="angriff3"><?php echo htmlspecialchars($charakter_angriff3) . " - Schaden: " . $charakter_schaden3; ?></button>
                                <button type="submit" name="angriff" value="spezial"><?php echo htmlspecialchars($charakter_spezialangriff) . " - Schaden: " . $charakter_schaden_spezial; ?></button>
                            </form>
                        </div>
                    </div>

                    <!-- Lebensbalken -->
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
</html>


$sql_update_punkte = "INSERT INTO nutzer_log (nutzer_id, aktion, punkte, details) VALUES (?, 'Boss besiegt', ?, ?)";
        $details = "Benutzer: $benutzername, Level: 1, Charakter: $charakter_name"; // Details hier festlegen
        $stmt_update_punkte = $conn->prepare($sql_update_punkte);
        $stmt_update_punkte->bind_param("iis", $nutzer_id, $_SESSION['spieler_punkte'], $details);
        $stmt_update_punkte->execute();

$sql_update_punkte = "INSERT INTO nutzer_log (nutzer_id, aktion, punkte, level, charakter_id, status, details) VALUES (?, 'Boss besiegt', ?, ?, ?, ?, ?)";
        $details = "Benutzer: $benutzername, Level: {$_SESSION['level']}, Charakter-ID: {$_SESSION['charakter_id']}, Charakter: $charakter_name"; // Details hier festlegen
        $status = "Gewonnen";
        $stmt_update_punkte = $conn->prepare($sql_update_punkte);
        $stmt_update_punkte->bind_param("iisiss", $nutzer_id, $_SESSION['spieler_punkte'], $_SESSION['level'], $_SESSION['charakter_id'], $status, $details);
        $stmt_update_punkte->execute();





-----------



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
        $_SESSION['spieler_punkte'] += 101; // Spieler erhält 101 Punkte
        
        // Level aus der Session holen
        $level = $_SESSION['level'];  // Angenommener Levelwert aus der Session

        // Details für die SQL-Abfrage
        $details = "Benutzer: $benutzername, Level: $level, Charakter: $charakter_name"; 

        // Punkte in der nutzer_log Tabelle speichern
        $sql_update_punkte = "INSERT INTO nutzer_log (nutzer_id, aktion, punkte, level, charakter_id, status, details) VALUES (?, 'Boss besiegt', ?, ?, ?, 'Gewonnen', ?)";
        $stmt_update_punkte = $conn->prepare($sql_update_punkte);
        $stmt_update_punkte->bind_param("iiiss", $nutzer_id, $_SESSION['spieler_punkte'], $level, $charakter_id, $details);
        $stmt_update_punkte->execute();
    }
    
    if ($_SESSION['spieler_lebenspunkte'] <= 0) {
        $_SESSION['spieler_lebenspunkte'] = 0;
    }
}

// Aktuelle Punkte aus der nutzer_log Tabelle abfragen
$sql_punkte = "SELECT punkte FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1";
$stmt_punkte = $conn->prepare($sql_punkte);
$stmt_punkte->bind_param("i", $nutzer_id);
$stmt_punkte->execute();
$result_punkte = $stmt_punkte->get_result();

if ($result_punkte->num_rows > 0) {
    $row_punkte = $result_punkte->fetch_assoc();
    $spieler_punkte = $row_punkte['punkte'];  // Aktuelle Punkte des Spielers
} else {
    $spieler_punkte = 0;  // Falls keine Punkte gefunden wurden
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





<p>Deine aktuellen Punkte: <?php echo $punkte; ?></p> <!-- Hier werden die Punkte angezeigt -->

// Abrufen des Charakters des Spielers aus der nutzer_log Tabelle
$sql = "SELECT charakter_id FROM nutzer_log WHERE nutzer_id = ? AND aktion = 'Character selection' ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $nutzer_id);
$stmt->execute();
$result = $stmt->get_result();


else {
$punkte = $row['punkte'];
"SELECT punkte FROM nutzer_log 
} 









session_start(); // Stellt sicher, dass die Session gestartet wird

// Stellen Sie sicher, dass eine gültige Benutzersitzung besteht
if (!isset($_SESSION['user_id'])) {
    die("Benutzer ist nicht eingeloggt.");
}

// Benutzer-ID aus der Session abrufen
$nutzer_id = $_SESSION['user_id'];

// Verbindung zur Datenbank herstellen (ersetze diese mit deinen tatsächlichen DB-Verbindungsdaten)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kartenspiel1_db";
$conn = new mysqli($servername, $username, $password, $dbname);

// Überprüfen, ob die Verbindung erfolgreich war
if ($conn->connect_error) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error);
}

// Prüfen, ob die Punkte in der Session vorhanden sind
if (isset($_SESSION['punkte'])) {
    // Wenn Punkte in der Session sind, diese verwenden
    $punkte = $_SESSION['punkte'];
} else {
    // Wenn keine Punkte in der Session vorhanden sind, aus der Datenbank holen
    // Abfrage der letzten Punkte aus der nutzer_log-Tabelle (die den Fortschritt speichern sollte)
    $stmt = $conn->prepare("SELECT punkte FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $nutzer_id);
    $stmt->execute();
    $stmt->bind_result($punkte);
    $stmt->fetch();
    $stmt->close();

    // Wenn keine Punkte gefunden werden, setze einen Standardwert oder eine Fehlermeldung
    if ($punkte === 101) {
        die("Fehler: Keine Punkte in der Datenbank für den Benutzer gefunden.");
    }

    // Speichern der Punkte in der Session für zukünftige Abfragen
    $_SESSION['punkte'] = $punkte;
}

// Gebe die Punkte aus (oder verwende sie für weitere Logik)
<header>
        <h1>Willkommen zurück, <?php echo htmlspecialchars($benutzername); ?>!</h1>
        <p>Du spielst als: <?php echo htmlspecialchars($charakter_name); ?></p>
        <p>Deine aktuellen Punkte: <?php echo $spieler_punkte; ?></p> oder sowas echo "Die Punkte des Benutzers sind: " . $punkte; damit die anzeige auch richtig funktioniert 
    </header>



// Schließe die Datenbankverbindung
$conn->close(); 




// Prüfen, ob die Level in der Session vorhanden sind
if (isset($_SESSION['level'])) {
    // Wenn level in der Session sind, diese verwenden
    $level = $_SESSION['level'];
} else {
    // Wenn keine level in der Session vorhanden sind, aus der Datenbank holen
    $stmt = $conn->prepare("SELECT level FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $nutzer_id);
    $stmt->execute();
    $stmt->bind_result($level);
    $stmt->fetch();
    $stmt->close(); 

  







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
$punkte = isset($_SESSION['punkte']) ? (int) $_SESSION['punkte'] : '';

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

// Array mit den relevanten Feldern
$fields = [
    'aktion' => 'Kein Eintrag',
    'level' => 1,
    'punkte' => 0,
    'charakter_id' => $charakter,
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
            $stmt_insert = $conn->prepare("INSERT INTO nutzer_log (nutzer_id, aktion, level, punkte, charakter_id, login, status, details) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt_insert) {
                // Fehler beim Vorbereiten der SQL-Abfrage
                die("Fehler bei der SQL-Abfrage: " . $conn->error);
            }

            $aktion = 'Level selection';
            $punkte = $_SESSION['punkte'];
            $charakter_id = $_SESSION['charakter'];
            $login = 'Aktiv';
            $status = 'Aktiv';
            $details = "Benutzer {$_SESSION['user_id']} hat Level $selected_level ausgewählt.";

            $stmt_insert->bind_param("isiiisss", $nutzer_id, $aktion, $selected_level, $punkte, $charakter_id, $login, $status, $details);
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
$punkte = isset($_SESSION['punkte']) ? (int) $_SESSION['punkte'] : '';

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

// Array mit den relevanten Feldern
$fields = [
    'aktion' => 'Kein Eintrag',
    'level' => 1,
    'punkte' => 0,
    'charakter_id' => $charakter,
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
            $stmt_insert = $conn->prepare("INSERT INTO nutzer_log (nutzer_id, aktion, level, punkte, charakter_id, login, status, details) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt_insert) {
                // Fehler beim Vorbereiten der SQL-Abfrage
                die("Fehler bei der SQL-Abfrage: " . $conn->error);
            }

            $aktion = 'Level selection';
            $punkte = $_SESSION['punkte'];
            $charakter_id = $_SESSION['charakter'];
            $login = 'Aktiv';
            $status = 'Aktiv';
            $details = "Benutzer {$_SESSION['user_id']} hat Level $selected_level ausgewählt.";

            $stmt_insert->bind_param("isiiisss", $nutzer_id, $aktion, $selected_level, $punkte, $charakter_id, $login, $status, $details);
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

        <!-- Weitere Button-Sektionen folgen hier -->
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
$punkte = isset($_SESSION['punkte']) ? (int) $_SESSION['punkte'] : '';

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

// Array mit den relevanten Feldern
$fields = [
    'aktion' => 'Kein Eintrag',
    'level' => 1,
    'punkte' => 0,
    'charakter_id' => $charakter,
    'login' => 'Aktiv', // Wird jetzt durch NOW() in SQL ersetzt
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

        <!-- Weitere Levels hier -->
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




<?php
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Wenn nicht eingeloggt, zur Login-Seite weiterleiten
    exit();
}

// Benutzerinformationen aus der Session
$benutzername = $_SESSION['benutzername'];
$nutzer_id = $_SESSION['user_id']; // Benutzer-ID aus der Session

// Verbindung zur Datenbank
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

// Abrufen des Charakters des Spielers aus der nutzer_log Tabelle
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

// Charakter-Daten
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

// Sicherstellen, dass die Lebenspunkte des Charakters gesetzt werden
if (!isset($_SESSION['spieler_lebenspunkte']) && isset($charakter_lebenspunkte)) {
    $_SESSION['spieler_lebenspunkte'] = $charakter_lebenspunkte;
}

// Prüfen, ob die Punkte in der Session vorhanden sind
if (isset($_SESSION['punkte'])) {
    // Wenn Punkte in der Session sind, diese verwenden
    $punkte = $_SESSION['punkte'];
} else {
    // Wenn keine Punkte in der Session vorhanden sind, aus der Datenbank holen
    $stmt = $conn->prepare("SELECT punkte FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $nutzer_id);
    $stmt->execute();
    $stmt->bind_result($punkte);
    $stmt->fetch();
    $stmt->close(); 
    // Wenn keine Punkte gefunden werden, eine Fehlermeldung anzeigen
    if ($punkte === null) {
        die("Fehler: Keine Punkte in der Datenbank für den Benutzer gefunden.");
    }
    // Speichern der Punkte in der Session für zukünftige Abfragen
    $_SESSION['punkte'] = $punkte;
}

// Prüfen, ob die Level in der Session vorhanden sind
if (isset($_SESSION['level'])) {
    // Wenn level in der Session sind, diese verwenden
    $level = $_SESSION['level'];
} else {
    // Wenn keine level in der Session vorhanden sind, aus der Datenbank holen
    $stmt = $conn->prepare("SELECT level FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $nutzer_id);
    $stmt->execute();
    $stmt->bind_result($level);
    $stmt->fetch();
    $stmt->close();  
    // Speichern des Levels in der Session
    $_SESSION['level'] = $level;
}

// Boss initialisieren, wenn noch nicht gesetzt
if (!isset($_SESSION['boss_name'])) {
    include_once 'bosse.php';
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

// Abrufen des gespeicherten Bosses
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
    $spieler_schaden = 0;
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
    }

    // Boss verliert Lebenspunkte
    $_SESSION['boss_lebenspunkte'] -= $spieler_schaden;

    // Zufälliger Boss-Angriff
    $boss_angriff = rand(1, 3);
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
        $_SESSION['spieler_punkte'] += 101; // Spieler erhält 101 Punkte
        
        // Level aus der Session holen
        $level = $_SESSION['level'];

        $details = "Benutzer: $benutzername, Level: $level, Charakter: $charakter_name"; 
        $sql_update_punkte = "INSERT INTO nutzer_log (nutzer_id, aktion, punkte, level, charakter_id, status, details) VALUES (?, 'Boss besiegt', ?, ?, ?, 'Gewonnen', ?)";
        $stmt_update_punkte = $conn->prepare($sql_update_punkte);
        $stmt_update_punkte->bind_param("iiiss", $nutzer_id, $_SESSION['spieler_punkte'], $level, $charakter_id, $details);
        $stmt_update_punkte->execute();
    }

    // Überprüfen, ob der Spieler besiegt wurde
    if ($_SESSION['spieler_lebenspunkte'] <= 0) {
        $_SESSION['spieler_lebenspunkte'] = 0;
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
    <div class="clouds">
        <div class="clouds-1"></div>
        <div class="clouds-2"></div>
        <div class="clouds-3"></div>
    </div>

    <header>
        <h1>Willkommen zurück, <?php echo htmlspecialchars($benutzername); ?>!</h1>
        <p>Du spielst als: <?php echo htmlspecialchars($charakter_name); ?></p>
        <p>Deine aktuellen Punkte: <?php echo $_SESSION['punkte']; ?></p>
    </header>

    <main>
        <div class="game-container">
            <section class="opponent">
                <div class="player-info">
                    <div class="boss-layout">
                        <div class="attacks-left">
                            <p class="attack"><?php echo htmlspecialchars($boss_angriff1) . " - Schaden: " . $boss_schaden1; ?></p>
                            <p class="attack"><?php echo htmlspecialchars($boss_angriff2) . " - Schaden: " . $boss_schaden2; ?></p>
                        </div>

                        <div class="boss-image-container">
                            <?php if (!empty($boss_bild_url)): ?>
                                <img src="<?php echo $boss_bild_url; ?>" alt="Bild des Bosses" class="boss-image">
                            <?php else: ?>
                                <p>Kein Bild verfügbar.</p>
                            <?php endif; ?>
                        </div>

                        <div class="attacks-right">
                            <p class="attack"><?php echo htmlspecialchars($boss_angriff3) . " - Schaden: " . $boss_schaden3; ?></p>
                            <p class="attack"><?php echo htmlspecialchars($boss_spezialattacke) . " - Schaden: " . $boss_schaden_spezial; ?></p>
                        </div>
                    </div>

                    <div class="health-bar-container">
                        <div class="hs-wrapper gold">
                            <p class="hs-text gold"><?php echo htmlspecialchars($boss_name); ?></p>
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

            <article class="<?php echo ($_SESSION['boss_lebenspunkte'] <= 0 || $_SESSION['spieler_lebenspunkte'] <= 0) ? 'show-message' : ''; ?>">
                <?php
                if ($_SESSION['boss_lebenspunkte'] <= 0) {
                    echo "<p>Herzlichen Glückwunsch! Du hast den Boss " . htmlspecialchars($boss_name) . " besiegt!</p>";
                } elseif ($_SESSION['spieler_lebenspunkte'] <= 0) {
                    echo "<p>Du wurdest mental gebrochen! " . htmlspecialchars($charakter_name) . " darf jetzt Hartz 4 beantragen und das sinnlose Leben ertragen.</p>";
                }
                ?>
            </article>

            <section class="player">
                <div class="player-info">
                    <div class="player-layout">
                        <form method="POST">
                            <div class="attack-buttons">
                                <div class="attacks-left">
                                    <button type="submit" name="angriff" value="angriff1"><?php echo htmlspecialchars($charakter_angriff1) . " - Schaden: " . $charakter_schaden1; ?></button>
                                    <button type="submit" name="angriff" value="angriff2"><?php echo htmlspecialchars($charakter_angriff2) . " - Schaden: " . $charakter_schaden2; ?></button>
                                </div>
                            </div>
                        </form>

                        <div class="player-image-container">
                            <?php if (!empty($charakter_bild_url)): ?>
                                <img src="<?php echo $charakter_bild_url; ?>" alt="Bild des Charakters" class="player-image">
                            <?php else: ?>
                                <p>Kein Bild verfügbar.</p>
                            <?php endif; ?>
                        </div>

                        <form method="POST">
                            <div class="attacks-right">
                                <button type="submit" name="angriff" value="angriff3"><?php echo htmlspecialchars($charakter_angriff3) . " - Schaden: " . $charakter_schaden3; ?></button>
                                <button type="submit" name="angriff" value="spezial"><?php echo htmlspecialchars($charakter_spezialangriff) . " - Schaden: " . $charakter_schaden_spezial; ?></button>
                            </div>
                        </form>
                    </div>

                    <div class="health-bar-container">
                        <div class="hs-wrapper gold">
                            <p class="hs-text gold"><?php echo htmlspecialchars($charakter_name); ?></p>
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

    <script>
        // Wenn entweder der Boss oder der Spieler besiegt ist, 3 Sekunden warten und dann auf 'spiel.php' weiterleiten
        if (<?php echo ($_SESSION['boss_lebenspunkte'] <= 0 || $_SESSION['spieler_lebenspunkte'] <= 0) ? 'true' : 'false'; ?>) {
            setTimeout(function() {
                window.location.href = 'spiel.php';
            }, 3000);
        }
    </script>
</body>
</html>





<?php
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Wenn nicht eingeloggt, zur Login-Seite weiterleiten
    exit();
}

// Benutzerinformationen aus der Session
$benutzername = $_SESSION['benutzername'];
$nutzer_id = $_SESSION['user_id']; // Benutzer-ID aus der Session

// Verbindung zur Datenbank
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

// Abrufen des Charakters des Spielers aus der nutzer_log Tabelle
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

// Charakter-Daten
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

// Sicherstellen, dass die Lebenspunkte des Charakters gesetzt werden
if (!isset($_SESSION['spieler_lebenspunkte']) && isset($charakter_lebenspunkte)) {
    $_SESSION['spieler_lebenspunkte'] = $charakter_lebenspunkte;
}

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

// Boss initialisieren, wenn noch nicht gesetzt
if (!isset($_SESSION['boss_name'])) {
    include_once 'bosse.php';
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

// Abrufen des gespeicherten Bosses
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
    $spieler_schaden = 0;
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
    }

    // Boss verliert Lebenspunkte
    $_SESSION['boss_lebenspunkte'] -= $spieler_schaden;

    // Zufälliger Boss-Angriff
    $boss_angriff = rand(1, 3);
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
        $_SESSION['punkte'] += 101; // Spieler erhält 101 Punkte
        
        // Level aus der Session holen
        $level = $_SESSION['level'];

        $details = "Benutzer: $benutzername, Level: $level, Charakter: $charakter_name"; 

        $sql_update_punkte = 'INSERT INTO nutzer_log (nutzer_id, aktion, punkte, level, charakter_id, status, details) values 
                              (?, ?, ?, ?, ?, ?, ?)';

        $stmt_update_punkte = $conn->prepare($sql_update_punkte);
        $test123 = 'Boss besiegt';
        $test345 = 'Gewonnen';
        $stmt_update_punkte->bind_param("isiiiss", $nutzer_id, $test123 , $_SESSION['punkte'],   $level, $charakter_id, $test345, $details); 
        $stmt_update_punkte->execute();
    }

    // Überprüfen, ob der Spieler besiegt wurde
    if ($_SESSION['spieler_lebenspunkte'] <= 0) {
        $_SESSION['spieler_lebenspunkte'] = 0;
    }
}
$stmt = $conn->prepare("SELECT punkte FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $nutzer_id);  
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
    <title>Codebreakers - Battle of Minds</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>
    <div class="clouds">
        <div class="clouds-1"></div>
        <div class="clouds-2"></div>
        <div class="clouds-3"></div>
    </div>

    <header>
        <h1>Willkommen zurück, <?php echo htmlspecialchars($benutzername); ?>!</h1>
        <p>Du spielst als: <?php echo htmlspecialchars($charakter_name); ?></p>
        <p>Deine aktuellen Punkte: <?php echo $_SESSION['punkte']; ?></p>
    </header>

    <main>
        <div class="game-container">
            <section class="opponent">
                <div class="player-info">
                    <div class="boss-layout">
                        <div class="attacks-left">
                            <p class="attack"><?php echo htmlspecialchars($boss_angriff1) . " - Schaden: " . $boss_schaden1; ?></p>
                            <p class="attack"><?php echo htmlspecialchars($boss_angriff2) . " - Schaden: " . $boss_schaden2; ?></p>
                        </div>

                        <div class="boss-image-container">
                            <?php if (!empty($boss_bild_url)): ?>
                                <img src="<?php echo $boss_bild_url; ?>" alt="Bild des Bosses" class="boss-image">
                            <?php else: ?>
                                <p>Kein Bild verfügbar.</p>
                            <?php endif; ?>
                        </div>

                        <div class="attacks-right">
                            <p class="attack"><?php echo htmlspecialchars($boss_angriff3) . " - Schaden: " . $boss_schaden3; ?></p>
                            <p class="attack"><?php echo htmlspecialchars($boss_spezialattacke) . " - Schaden: " . $boss_schaden_spezial; ?></p>
                        </div>
                    </div>

                    <div class="health-bar-container">
                        <div class="hs-wrapper gold">
                            <p class="hs-text gold"><?php echo htmlspecialchars($boss_name); ?></p>
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

            <article class="<?php echo ($_SESSION['boss_lebenspunkte'] <= 0 || $_SESSION['spieler_lebenspunkte'] <= 0) ? 'show-message' : ''; ?>">
                <?php
                if ($_SESSION['boss_lebenspunkte'] <= 0) {
                    echo "<p>Herzlichen Glückwunsch! Du hast den Boss " . htmlspecialchars($boss_name) . " besiegt!</p>";
                    // Lösche Session-Daten nach dem Sieg
                    unset($_SESSION['spieler_lebenspunkte']);
                    unset($_SESSION['level']);
                    unset($_SESSION['punkte']);
                    unset($_SESSION['boss_lebenspunkte']);
                    unset($_SESSION['boss_name']);
                } elseif ($_SESSION['spieler_lebenspunkte'] <= 0) {
                    echo "<p>Du wurdest mental gebrochen! " . htmlspecialchars($charakter_name) . " darf jetzt Hartz 4 beantragen und das sinnlose Leben ertragen. Du hast das Spiel verloren.</p>";
                    // Lösche Session-Daten nach der Niederlage
                    unset($_SESSION['spieler_lebenspunkte']);
                    unset($_SESSION['level']);
                    unset($_SESSION['punkte']);
                    unset($_SESSION['boss_lebenspunkte']);
                    unset($_SESSION['boss_name']);
                }
                ?>
            </article>

            <section class="player">
                <div class="player-info">
                    <p class="attack-button">
                        <form method="POST" action="">
                            <label for="angriff">Wähle deinen Angriff: </label>
                            <select name="angriff" id="angriff">
                                <option value="angriff1"><?php echo $charakter_angriff1; ?> - Schaden: <?php echo $charakter_schaden1; ?></option>
                                <option value="angriff2"><?php echo $charakter_angriff2; ?> - Schaden: <?php echo $charakter_schaden2; ?></option>
                                <option value="angriff3"><?php echo $charakter_angriff3; ?> - Schaden: <?php echo $charakter_schaden3; ?></option>
                                <option value="spezial"><?php echo $charakter_spezialangriff; ?> - Schaden: <?php echo $charakter_schaden_spezial; ?></option>
                            </select>
                            <input type="submit" value="Angreifen">
                        </form>
                    </p>
                </div>
            </section>
        </div>
    </main>

</body>
</html>








<?php
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Wenn nicht eingeloggt, zur Login-Seite weiterleiten
    exit();
}

// Benutzerinformationen aus der Session
$benutzername = $_SESSION['benutzername'];
$nutzer_id = $_SESSION['user_id']; // Benutzer-ID aus der Session

// Verbindung zur Datenbank
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

// Abrufen des Charakters des Spielers aus der nutzer_log Tabelle
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

// Charakter-Daten
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

// Sicherstellen, dass die Lebenspunkte des Charakters gesetzt werden
if (!isset($_SESSION['spieler_lebenspunkte']) && isset($charakter_lebenspunkte)) {
    $_SESSION['spieler_lebenspunkte'] = $charakter_lebenspunkte;
}

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

// Restlicher Code bleibt unverändert bis zu diesem Punkt...
// Abrufen des gespeicherten Bosses
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
    $spieler_schaden = 0;
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
    }

    // Boss verliert Lebenspunkte
    $_SESSION['boss_lebenspunkte'] -= $spieler_schaden;

    // Zufälliger Boss-Angriff
    $boss_angriff = rand(1, 3);
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
        $_SESSION['punkte'] += 101; // Spieler erhält 101 Punkte
        
        // Level aus der Session holen
        $level = $_SESSION['level'];

        $details = "Benutzer: $benutzername, Level: $level, Charakter: $charakter_name"; 

$sql_update_punkte = 'INSERT INTO nutzer_log (nutzer_id, aktion, punkte, level, charakter_id, status, details) values 
                      (?, ?, ?, ?, ?, ?, ?)';
$stmt_update_punkte = $conn->prepare($sql_update_punkte);
$test123 = 'Boss besiegt';
$test345 = 'Gewonnen';
$stmt_update_punkte->bind_param("isiiiss", $nutzer_id, $test123 , $_SESSION['punkte'],   $level, $charakter_id, $test345, $details); // Hier wird der nutzer_id zweimal gebunden
$stmt_update_punkte->execute();
    }

    // Überprüfen, ob der Spieler besiegt wurde
    if ($_SESSION['spieler_lebenspunkte'] <= 0) {
        $_SESSION['spieler_lebenspunkte'] = 0;
    }
}

// 8 Sekunden nach Ende des Spiels die Session löschen und weiterleiten
if ($_SESSION['boss_lebenspunkte'] <= 0 || $_SESSION['spieler_lebenspunkte'] <= 0) {
    echo "<script>
        setTimeout(function() {
            window.location.href = 'spiel.php?session_end=true';
        }, 8000); // 8 Sekunden Verzögerung
    </script>";
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
    <div class="clouds">
        <div class="clouds-1"></div>
        <div class="clouds-2"></div>
        <div class="clouds-3"></div>
    </div>

    <header>
        <h1>Willkommen zurück, <?php echo htmlspecialchars($benutzername); ?>!</h1>
        <p>Du spielst als: <?php echo htmlspecialchars($charakter_name); ?></p>
        <p>Deine aktuellen Punkte: <?php echo $_SESSION['punkte']; ?></p>
    </header>

    <main>
        <div class="game-container">
            <section class="opponent">
                <div class="player-info">
                    <div class="boss-layout">
                        <div class="attacks-left">
                            <p class="attack"><?php echo htmlspecialchars($boss_angriff1) . " - Schaden: " . $boss_schaden1; ?></p>
                            <p class="attack"><?php echo htmlspecialchars($boss_angriff2) . " - Schaden: " . $boss_schaden2; ?></p>
                        </div>

                        <div class="boss-image-container">
                            <?php if (!empty($boss_bild_url)): ?>
                                <img src="<?php echo $boss_bild_url; ?>" alt="Bild des Bosses" class="boss-image">
                            <?php else: ?>
                                <p>Kein Bild verfügbar.</p>
                            <?php endif; ?>
                        </div>

                        <div class="attacks-right">
                            <p class="attack"><?php echo htmlspecialchars($boss_angriff3) . " - Schaden: " . $boss_schaden3; ?></p>
                            <p class="attack"><?php echo htmlspecialchars($boss_spezialattacke) . " - Schaden: " . $boss_schaden_spezial; ?></p>
                        </div>
                    </div>

                    <div class="health-bar-container">
                        <div class="hs-wrapper gold">
                            <p class="hs-text gold"><?php echo htmlspecialchars($boss_name); ?></p>
                        </div>
                        <div class="health-bar--outline">
                            <div class="health-bar--border">
                                <div class="health-bar--background">
                                    <div class="health-bar--inner" style="width: <?php echo (($_SESSION['boss_lebenspunkte'] / $boss_lebenspunkte) * 100); ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="player">
                <div class="player-info">
                    <div class="attacks-left">
                        <p class="attack"><?php echo htmlspecialchars($charakter_angriff1) . " - Schaden: " . $charakter_schaden1; ?></p>
                        <p class="attack"><?php echo htmlspecialchars($charakter_angriff2) . " - Schaden: " . $charakter_schaden2; ?></p>
                    </div>

                    <div class="player-image-container">
                        <img src="<?php echo htmlspecialchars($charakter_bild_url); ?>" alt="Bild des Charakters" class="player-image">
                    </div>

                    <div class="attacks-right">
                        <p class="attack"><?php echo htmlspecialchars($charakter_angriff3) . " - Schaden: " . $charakter_schaden3; ?></p>
                        <p class="attack"><?php echo htmlspecialchars($charakter_spezialangriff) . " - Schaden: " . $charakter_schaden_spezial; ?></p>
                    </div>
                </div>

                <div class="health-bar-container">
                    <div class="hs-wrapper gold">
                        <p class="hs-text gold"><?php echo htmlspecialchars($charakter_name); ?></p>
                    </div>
                    <div class="health-bar--outline">
                        <div class="health-bar--border">
                            <div class="health-bar--background">
                                <div class="health-bar--inner" style="width: <?php echo (($_SESSION['spieler_lebenspunkte'] / $charakter_lebenspunkte) * 100); ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>






<?php
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Wenn nicht eingeloggt, zur Login-Seite weiterleiten
    exit();
}

// Benutzerinformationen aus der Session
$benutzername = $_SESSION['benutzername'];
$nutzer_id = $_SESSION['user_id']; // Benutzer-ID aus der Session

// Verbindung zur Datenbank
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

// Abrufen des Charakters des Spielers aus der nutzer_log Tabelle
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

// Charakter-Daten
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

// Sicherstellen, dass die Lebenspunkte des Charakters gesetzt werden
if (!isset($_SESSION['spieler_lebenspunkte']) && isset($charakter_lebenspunkte)) {
    $_SESSION['spieler_lebenspunkte'] = $charakter_lebenspunkte;
}

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


// Prüfen, ob die Level in der Session vorhanden sind
if (isset($_SESSION['level'])) {
    // Wenn level in der Session sind, diese verwenden
    $level = $_SESSION['level'];
} else {
    // Wenn keine level in der Session vorhanden sind, aus der Datenbank holen
    $stmt = $conn->prepare("SELECT level FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $nutzer_id);
    $stmt->execute();
    $stmt->bind_result($level);
    $stmt->fetch();
    $stmt->close();  
    // Speichern des Levels in der Session
    $_SESSION['level'] = $level;
}

// Boss initialisieren, wenn noch nicht gesetzt
if (!isset($_SESSION['boss_name'])) {
    include_once 'bosse.php';
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

// Abrufen des gespeicherten Bosses
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
    $spieler_schaden = 0;
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
    }

    // Boss verliert Lebenspunkte
    $_SESSION['boss_lebenspunkte'] -= $spieler_schaden;

    // Zufälliger Boss-Angriff
    $boss_angriff = rand(1, 3);
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
        $_SESSION['punkte'] += 101; // Spieler erhält 101 Punkte
        
        // Level aus der Session holen
        $level = $_SESSION['level'];

        $details = "Benutzer: $benutzername, Level: $level, Charakter: $charakter_name"; 

$sql_update_punkte = 'INSERT INTO nutzer_log (nutzer_id, aktion, punkte, level, charakter_id, status, details) values 
                      (?, ?, ?, ?, ?, ?, ?)';

$stmt_update_punkte = $conn->prepare($sql_update_punkte);
$test123 = 'Boss besiegt';
$test345 = 'Gewonnen';
$stmt_update_punkte->bind_param("isiiiss", $nutzer_id, $test123 , $_SESSION['punkte'],   $level, $charakter_id, $test345, $details); // Hier wird der nutzer_id zweimal gebunden
$stmt_update_punkte->execute();

    }

    // Überprüfen, ob der Spieler besiegt wurde
    if ($_SESSION['spieler_lebenspunkte'] <= 0) {
        $_SESSION['spieler_lebenspunkte'] = 0;
    }
}
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
    <title>Codebreakers - Battle of Minds</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>
    <div class="clouds">
        <div class="clouds-1"></div>
        <div class="clouds-2"></div>
        <div class="clouds-3"></div>
    </div>

    <header>
        <h1>Willkommen zurück, <?php echo htmlspecialchars($benutzername); ?>!</h1>
        <p>Du spielst als: <?php echo htmlspecialchars($charakter_name); ?></p>
        <p>Deine aktuellen Punkte: <?php echo $_SESSION['punkte']; ?></p>
    </header>

    <main>
        <div class="game-container">
            <section class="opponent">
                <div class="player-info">
                    <div class="boss-layout">
                        <div class="attacks-left">
                            <p class="attack"><?php echo htmlspecialchars($boss_angriff1) . " - Schaden: " . $boss_schaden1; ?></p>
                            <p class="attack"><?php echo htmlspecialchars($boss_angriff2) . " - Schaden: " . $boss_schaden2; ?></p>
                        </div>

                        <div class="boss-image-container">
                            <?php if (!empty($boss_bild_url)): ?>
                                <img src="<?php echo $boss_bild_url; ?>" alt="Bild des Bosses" class="boss-image">
                            <?php else: ?>
                                <p>Kein Bild verfügbar.</p>
                            <?php endif; ?>
                        </div>

                        <div class="attacks-right">
                            <p class="attack"><?php echo htmlspecialchars($boss_angriff3) . " - Schaden: " . $boss_schaden3; ?></p>
                            <p class="attack"><?php echo htmlspecialchars($boss_spezialattacke) . " - Schaden: " . $boss_schaden_spezial; ?></p>
                        </div>
                    </div>

                    <div class="health-bar-container">
                        <div class="hs-wrapper gold">
                            <p class="hs-text gold"><?php echo htmlspecialchars($boss_name); ?></p>
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

            <article class="<?php echo ($_SESSION['boss_lebenspunkte'] <= 0 || $_SESSION['spieler_lebenspunkte'] <= 0) ? 'show-message' : ''; ?>">
                <?php
                if ($_SESSION['boss_lebenspunkte'] <= 0) {
                    echo "<p>Herzlichen Glückwunsch! Du hast den Boss " . htmlspecialchars($boss_name) . " besiegt!</p>";
                    // Lösche Session-Daten nach dem Sieg
                    unset($_SESSION['spieler_lebenspunkte']);
                    unset($_SESSION['level']);
                    unset($_SESSION['punkte']);
                    unset($_SESSION['boss_lebenspunkte']);
                    unset($_SESSION['boss_name']);
                } elseif ($_SESSION['spieler_lebenspunkte'] <= 0) {
                    echo "<p>Du wurdest mental gebrochen! " . htmlspecialchars($charakter_name) . " darf jetzt Hartz 4 beantragen und das sinnlose Leben ertragen. Du hast das Spiel verloren.</p>";
                    // Lösche Session-Daten nach der Niederlage
                    unset($_SESSION['spieler_lebenspunkte']);
                    unset($_SESSION['level']);
                    unset($_SESSION['punkte']);
                    unset($_SESSION['boss_lebenspunkte']);
                    unset($_SESSION['boss_name']);
                }
                ?>
            </article>

            <section class="player">
                <div class="player-info">
                    <div class="player-layout">
                        <form method="POST">
                            <div class="attack-buttons">
                                <div class="attacks-left">
                                    <button type="submit" name="angriff" value="angriff1"><?php echo htmlspecialchars($charakter_angriff1) . " - Schaden: " . $charakter_schaden1; ?></button>
                                    <button type="submit" name="angriff" value="angriff2"><?php echo htmlspecialchars($charakter_angriff2) . " - Schaden: " . $charakter_schaden2; ?></button>
                                </div>
                            </div>
                        </form>

                        <div class="player-image-container">
                            <?php if (!empty($charakter_bild_url)): ?>
                                <img src="<?php echo $charakter_bild_url; ?>" alt="Bild des Charakters" class="player-image">
                            <?php else: ?>
                                <p>Kein Bild verfügbar.</p>
                            <?php endif; ?>
                        </div>

                        <form method="POST">
                            <div class="attacks-right">
                                <button type="submit" name="angriff" value="angriff3"><?php echo htmlspecialchars($charakter_angriff3) . " - Schaden: " . $charakter_schaden3; ?></button>
                                <button type="submit" name="angriff" value="spezial"><?php echo htmlspecialchars($charakter_spezialangriff) . " - Schaden: " . $charakter_schaden_spezial; ?></button>
                            </div>
                        </form>
                    </div>

                    <div class="health-bar-container">
                        <div class="hs-wrapper gold">
                            <p class="hs-text gold"><?php echo htmlspecialchars($charakter_name); ?></p>
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

    <script>
        // Wenn entweder der Boss oder der Spieler besiegt ist, 3 Sekunden warten und dann auf 'spiel.php' weiterleiten
        if (<?php echo ($_SESSION['boss_lebenspunkte'] <= 0 || $_SESSION['spieler_lebenspunkte'] <= 0) ? 'true' : 'false'; ?>) {
            setTimeout(function() {
                window.location.href = 'spiel.php';
            }, 3000);
        }
    </script>
</body>
<footer>
        <p>&copy; 2025 Codebreakers - Battle of Minds Trading Card Game - Alle Rechte vorbehalten.</p>
    </footer>
</html>