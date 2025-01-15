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