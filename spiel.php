<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$nutzer_id = $_SESSION['user_id'];
$benutzername = $_SESSION['benutzername'];
$charakter = isset($_SESSION['charakter']) ? (int) $_SESSION['charakter'] : '';
$level = isset($_SESSION['level']) ? (int) $_SESSION['level'] : ''; 
$punkte = isset($_SESSION['punkte']) ? (int) $_SESSION['punkte'] :''; 
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kartenspiel1_db";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error);
}
$nutzer_id = $_SESSION['user_id'];
if (isset($_SESSION['level'])) {
    $level = $_SESSION['level'];
} else {
    $sql_level = "SELECT level FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1";
    $stmt_level = $conn->prepare($sql_level);
    $stmt_level->bind_param("i", $nutzer_id);
    $stmt_level->execute();
    $stmt_level->bind_result($level);
    $stmt_level->fetch();
    $stmt_level->close();
    $_SESSION['level'] = $level;
}
if (isset($_SESSION['punkte'])) {
    $punkte = $_SESSION['punkte'];
} else {
    $sql_punkte = "SELECT punkte FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1";
    $stmt_punkte = $conn->prepare($sql_punkte);
    $stmt_punkte->bind_param("i", $nutzer_id);
    $stmt_punkte->execute();
    $stmt_punkte->bind_result($punkte);
    $stmt_punkte->fetch();
    $stmt_punkte->close();
    $_SESSION['punkte'] = $punkte;
}
if (isset($_SESSION['charakter_id'])) {
    $charakter_id = $_SESSION['charakter_id'];
} else {
    $sql_charakter = "SELECT charakter_id FROM nutzer_log WHERE nutzer_id = ? AND aktion = 'Character selection' ORDER BY id DESC LIMIT 1";
    $stmt_charakter = $conn->prepare($sql_charakter);
    $stmt_charakter->bind_param("i", $nutzer_id);
    $stmt_charakter->execute();
    $stmt_charakter->bind_result($charakter_id);
    $stmt_charakter->fetch();
    $stmt_charakter->close();
    $_SESSION['charakter_id'] = $charakter_id;
}
$fields = [
    'aktion' => 'Kein Eintrag',
    'level' => $level,
    'punkte' => $punkte,
    'charakter_id' => $charakter_id,
    'login' => 'Aktiv',
    'status' => 'Aktiv',
    'details' => 'Kein Detail'
];
foreach ($fields as $field => $default) {
    if (!isset($_SESSION[$field])) {
        $stmt = $conn->prepare("SELECT $field FROM nutzer_log WHERE nutzer_id = ? ORDER BY id DESC LIMIT 1");
        if (!$stmt) {
            die("Fehler bei der SQL-Abfrage: " . $conn->error);
        }
        $stmt->bind_param("i", $nutzer_id);
        $stmt->execute();
        $stmt->bind_result($value);
        $stmt->fetch();
        $_SESSION[$field] = $value ? $value : $default;
        $stmt->close();
    }
}
if (isset($_GET['level'])) {
    if (!empty($_GET['level'])) {
        $selected_level = (int) $_GET['level'];
        $stmt_check = $conn->prepare("SELECT level FROM level_system WHERE level = ?");
        if (!$stmt_check) {
            die("Fehler bei der SQL-Abfrage: " . $conn->error);
        }
        $stmt_check->bind_param("i", $selected_level);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $_SESSION['level'] = $selected_level;
            $stmt_insert = $conn->prepare("INSERT INTO nutzer_log (nutzer_id, aktion, level, punkte, charakter_id, login, status, details) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)");
            if (!$stmt_insert) {
                die("Fehler bei der SQL-Abfrage: " . $conn->error);
            }
            $aktion = 'Level selection';
            $punkte = $_SESSION['punkte'];
            $charakter_id = $_SESSION['charakter'];
            $status = 'Aktiv';
            $details = "Benutzer {$_SESSION['user_id']} hat Level $selected_level ausgewählt.";
            $stmt_insert->bind_param("isiiiss", $nutzer_id, $aktion, $selected_level, $punkte, $charakter_id, $status, $details);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt_check->close();
        header('location: intro1.php');
        exit;
    }
}
$stmt = $conn->prepare("SELECT `name` FROM charaktere WHERE id = ?");
$stmt->bind_param("i", $charakter);
$stmt->execute();
$stmt->bind_result($charakter_name);
$stmt->fetch();
$stmt->close();
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
    <title>Dein Abenteuer</title>
    <link rel="stylesheet" href="styles2.css">
    <style>
        .disabled {
            background-color: #ccc;
            cursor: not-allowed;
            pointer-events: none; 
        }
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
     <audio id="background-audio" preload="auto">
        <source src="Codebreakers.mp3" type="audio/mp3">
        Dein Browser unterstützt das Abspielen von Audio nicht.
    </audio>
    <div class="clouds">
        <div class="clouds-1"></div>
        <div class="clouds-2"></div>
        <div class="clouds-3"></div>
    </div>
    <header>
        <nav>
            <ul>
                <li><a href="abenteuer.php">Startseite</a></li>
                <li><a href="charaktere.php">Meine Charaktere</a></li>
                <li><a href="highscore.php">Highscore</a></li>
            </ul>
        </nav>
        <form action="login.php" method="POST">
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
                <button class="my-button3 <?php echo ($punkte >= 200) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='spiel.php?level=3';">LEVEL 3</button>
                <button class="my-button4 <?php echo ($punkte >= 300) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='spiel.php?level=4';">LEVEL 4</button>
                <button class="my-button5 <?php echo ($punkte >= 400) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro5.php';">LEVEL 5</button>
                <button class="my-button6 <?php echo ($punkte >= 500) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro6.php';">LEVEL 6</button>
            </div>
        </section>
        <section class="section">
            <div class="button-container">
                <button class="my-button7 <?php echo ($punkte >= 600) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro7.php';">LEVEL 7</button>
                <button class="my-button8 <?php echo ($punkte >= 700) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro8.php';">LEVEL 8</button>
                <button class="my-button9 <?php echo ($punkte >= 800) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro9.php';">LEVEL 9</button>
                <button class="my-button10 <?php echo ($punkte >= 900) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro10.php';">LEVEL 10</button>
                <button class="my-button11 <?php echo ($punkte >= 1000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro11.php';">LEVEL 11</button>
                <button class="my-button12 <?php echo ($punkte >= 1100) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro12.php';">LEVEL 12</button>
            </div>
        </section>
        <section class="section">
            <div class="button-container">
                <button class="my-button13 <?php echo ($punkte >= 1200) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro13.php';">LEVEL 13</button>
                <button class="my-button14 <?php echo ($punkte >= 1300) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro14.php';">LEVEL 14</button>
                <button class="my-button15 <?php echo ($punkte >= 1400) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro15.php';">LEVEL 15</button>
                <button class="my-button16 <?php echo ($punkte >= 1500) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro16.php';">LEVEL 16</button>
                <button class="my-button17 <?php echo ($punkte >= 1600) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro17.php';">LEVEL 17</button>
                <button class="my-button18 <?php echo ($punkte >= 1700) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro18.php';">LEVEL 18</button>
            </div>
        </section>
        <section class="section">
            <div class="button-container">
                <button class="my-button19 <?php echo ($punkte >= 1800) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro19.php';">LEVEL 19</button>
                <button class="my-button20 <?php echo ($punkte >= 1900) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro20.php';">LEVEL 20</button>
                <button class="my-button21 <?php echo ($punkte >= 2000) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro21.php';">LEVEL 21</button>
                <button class="my-button22 <?php echo ($punkte >= 2100) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro22.php';">LEVEL 22</button>
                <button class="my-button23 <?php echo ($punkte >= 2200) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro23.php';">LEVEL 23</button>
                <button class="my-button24 <?php echo ($punkte >= 2300) ? 'enabled' : 'disabled'; ?>" onclick="window.location.href='intro24.php';">LEVEL 24</button>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2025 Codebreakers - Battle of Minds Game - Alle Rechte vorbehalten.</p>
    </footer>
    <script>
        // Funktion zum Abspielen des Audios beim Laden der Seite
        window.onload = function() {
            var audio = document.getElementById('background-audio');
            // Versuchen, das Audio ohne Benutzerinteraktion abzuspielen
            audio.play().catch(function(error) {
                // Falls das Abspielen blockiert wurde, simulieren wir eine Benutzerinteraktion
                var div = document.createElement('div');
                div.style.display = 'none';
                document.body.appendChild(div);
                div.click();
            });
        };
    </script>
</body>
</html>
<?php
$conn->close();
?>
