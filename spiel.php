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

// Start der Transaktion
$conn->begin_transaction();

try {
    // Bereite die SQL-Abfrage vor, um SQL-Injection zu vermeiden
    $stmt = $conn->prepare("SELECT punkte FROM nutzer WHERE benutzername = ?");
    $stmt->bind_param("s", $benutzername);  // "s" für String
    $stmt->execute();
    $stmt->bind_result($punkte);
    $stmt->fetch();
    $stmt->close();

    if ($punkte === null) {
        throw new Exception("Keine Punkte gefunden für den Benutzer.");
    }

    // Abfrage der Leveldaten
    $level_status = [];
    $stmt = $conn->prepare("SELECT level, punkte_bis FROM level_system ORDER BY level");
    $stmt->execute();
    $stmt->bind_result($level, $punkte_bis);

    while ($stmt->fetch()) {
        $level_status[$level] = ($punkte >= $punkte_bis);
    }
    $stmt->close();

    // Commit der Transaktion
    $conn->commit();
} catch (Exception $e) {
    // Falls ein Fehler auftritt, machen wir ein Rollback
    $conn->rollback();
    echo "Fehler: " . $e->getMessage();
    exit();
} finally {
    // Verbindung schließen
    $conn->close();
}
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
        <p>Du spielst als: <?php echo htmlspecialchars($charakter); ?></p>
        <p>Deine aktuellen Punkte: <?php echo $punkte; ?></p>
    </header>

    <main>
        <section class="section">
            <div class="button-container">
                <!-- LEVEL 1 immer aktiv, auch wenn der Benutzer keine Punkte hat -->
                <button class="my-button1 <?php echo ($punkte >= 0) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro1.php';">LEVEL 1</button>
                <button class="my-button2 <?php echo ($punkte >= 100) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro2.php';">LEVEL 2</button>
                <button class="my-button3 <?php echo ($punkte >= 250) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro3.php';">LEVEL 3</button>
                <button class="my-button4 <?php echo ($punkte >= 500) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro4.php';">LEVEL 4</button>
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
        <p>&copy; 2025 Trading Card Game - Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>
