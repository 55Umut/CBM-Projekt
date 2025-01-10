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
$spieler_punkte = 30;
$gegner_punkte = 30;

// Datenbankverbindung
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kartenspiel1_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Zufällig einen Boss auswählen
$randomBossQuery = "SELECT * FROM bosse ORDER BY RAND() LIMIT 1";
$result = $conn->query($randomBossQuery);

if ($result->num_rows > 0) {
    $boss = $result->fetch_assoc();
    $boss_name = $boss['name'];
    $boss_spezialattacke = $boss['spezialattacke'];
    $boss_schaden_spezial = $boss['schaden_spezial'];
    $boss_lebenspunkte = $boss['leben'];
    $boss_bild_url = $boss['bild_url'];
    $boss_angriff1 = $boss['standardangriff1'];
    $boss_schaden1 = $boss['schaden1'];
    $boss_angriff2 = $boss['standardangriff2'];
    $boss_schaden2 = $boss['schaden2'];
    $boss_angriff3 = $boss['standardangriff3'];
    $boss_schaden3 = $boss['schaden3'];
} else {
    // Default-Werte, falls kein Boss gefunden wurde
    $boss_name = "Unbekannter Boss";
    $boss_spezialattacke = "Keine Spezialattacke";
    $boss_schaden_spezial = 0;
    $boss_lebenspunkte = 0;
    $boss_bild_url = ''; // Bild fehlt
    $boss_angriff1 = 'Unbekannt';
    $boss_schaden1 = 0;
    $boss_angriff2 = 'Unbekannt';
    $boss_schaden2 = 0;
    $boss_angriff3 = 'Unbekannt';
    $boss_schaden3 = 0;
}

// Lade die Charakterdaten des Spielers
$charakterQuery = "SELECT * FROM charaktere WHERE name = '$charakter_name'";
$result_charakter = $conn->query($charakterQuery);

if ($result_charakter->num_rows > 0) {
    $charakter = $result_charakter->fetch_assoc();
    $charakter_name = $charakter['name'];
    $charakter_lebenspunkte = $charakter['leben'];
    $charakter_angriff1 = $charakter['standardangriff1'];
    $charakter_schaden1 = $charakter['schaden1'];
    $charakter_angriff2 = $charakter['standardangriff2'];
    $charakter_schaden2 = $charakter['schaden2'];
    $charakter_angriff3 = $charakter['standardangriff3'];
    $charakter_schaden3 = $charakter['schaden3'];
    $charakter_spezialangriff = $charakter['spezialangriff'];
    $charakter_schaden_spezial = $charakter['schaden_spezial'];
    $charakter_bild_url = $charakter['bild_url'];
} else {
    echo "Charakter nicht gefunden!";
}

$conn->close();
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
        <h1>Willkommen auf dem Spielfeld, <?php echo htmlspecialchars($benutzername); ?>!</h1>
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
                                    <div class="health-bar--health" style="--health: <?php echo $boss_lebenspunkte; ?>; --max-health: 255;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="health-indicator">
                            <p>Lebenspunkte: <?php echo $boss_lebenspunkte; ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Spielerbereich (fixiert unten) -->
            <section class="player">
                <div class="player-info">
                    <div class="player-layout">
                        <!-- Links: 2 Standardangriffe -->
                        <div class="attacks-left">
                            <p class="attack">
                                <?php echo htmlspecialchars($charakter_angriff1) . " - Schaden: " . $charakter_schaden1; ?>
                            </p>
                            <p class="attack">
                                <?php echo htmlspecialchars($charakter_angriff2) . " - Schaden: " . $charakter_schaden2; ?>
                            </p>
                        </div>

                        <!-- Bild des Charakters -->
                        <div class="player-image-container">
                            <?php if (!empty($charakter_bild_url)): ?>
                                <img src="<?php echo $charakter_bild_url; ?>" alt="Bild des Charakters" class="player-image">
                            <?php else: ?>
                                <p>Kein Bild verfügbar.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Rechts: 1 Standardangriff und Spezialangriff -->
                        <div class="attacks-right">
                            <p class="attack">
                                <?php echo htmlspecialchars($charakter_angriff3) . " - Schaden: " . $charakter_schaden3; ?>
                            </p>
                            <p class="attack">
                                <?php echo htmlspecialchars($charakter_spezialangriff) . " - Schaden: " . $charakter_schaden_spezial; ?>
                            </p>
                        </div>
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
                                    <div class="health-bar--health" style="--health: <?php echo $charakter_lebenspunkte; ?>; --max-health: 255;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="health-indicator">
                            <p>Lebenspunkte: <?php echo $charakter_lebenspunkte; ?></p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <p> Codebreakers - Battle of Minds  Trading Card Game - Alle Rechte vorbehalten. &copy;2025</p>
    </footer>
</body>
</html>
