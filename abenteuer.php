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

// Starten einer Transaktion
$conn->begin_transaction();

try {
    // Liste der Charaktere aus der Datenbank abrufen (mit Bild-URL)
    $sql = "SELECT id, name, bild_url FROM charaktere"; // Angenommen, die Tabelle heißt "charaktere"
    $result = $conn->query($sql);

    // Array für die Charaktere
    $charaktere = [];

    if ($result->num_rows > 0) {
        // Ergebnisse in das Array laden
        while($row = $result->fetch_assoc()) {
            $charaktere[] = $row;
        }
    } else {
        echo "Keine Charaktere gefunden.";
    }

    // Abfrage der Top 5 Benutzer nach Punkten, sortiert nach der höchsten Punktzahl
    $sql_highscore = "SELECT benutzername, registriert_am, punkte FROM nutzer ORDER BY punkte DESC LIMIT 5";
    $result_highscore = $conn->query($sql_highscore);

    // Array für die Highscore-Daten
    $highscores = [];

    if ($result_highscore->num_rows > 0) {
        // Ergebnisse in das Array laden
        while($row = $result_highscore->fetch_assoc()) {
            $highscores[] = $row;
        }
    } else {
        echo "Keine Highscores gefunden.";
    }

    // Commit der Transaktion
    $conn->commit();
    
} catch (Exception $e) {
    // Falls ein Fehler auftritt, wird die Transaktion zurückgerollt
    $conn->rollback();
    echo "Ein Fehler ist aufgetreten. Bitte versuche es später erneut.";
    exit();
}

$conn->close();

// Charakterauswahl-Funktion
if (isset($_POST['charakter'])) {
    // Sicherstellen, dass ein Charakter ausgewählt wurde
    if (!empty($_POST['charakter'])) {
        $_SESSION['charakter'] = $_POST['charakter']; // Den ausgewählten Charakter speichern

        // Verbindung zur Datenbank herstellen
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Überprüfen, ob die Verbindung erfolgreich war
        if ($conn->connect_error) {
            die("Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error);
        }

        // Sicherstellen, dass der Charakter existiert
        $sql_check = "SELECT id FROM charaktere WHERE id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $_POST['charakter']);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Logeintrag erstellen
            require_once 'nutzerlog.php';
            $nutzerLog = new NutzerLog($servername, $username, $password, $dbname);
            $nutzerLog->insertLog(
                $_SESSION['user_id'], 
                'Character selection', 
                $_SESSION['user_id'] . ' hat sich einen Charakter ausgesucht.', 
                null, 
                null, 
                $_POST['charakter']
            );

            // Weiterleitung zur nächsten Seite nach der Auswahl
            header("Location: spiel.php");
            exit();
        } else {
            echo "Ungültiger Charakter.";
        }

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codebreakers - Battle of Minds</title>
    <link rel="stylesheet" href="styles1.css"> <!-- Verweis auf die angepasste CSS-Datei -->
    <style>
        /* Zusätzliche CSS-Regeln für das Layout */
        .container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }
        .charaktere-container {
            flex: 0 0 70%;
        }
        .highscore-container {
            flex: 0 0 25%;
            background-color: #333;
            padding: 10px;
            border-radius: 8px;
            color: #ffd700;
        }
        .highscore-container table {
            width: 100%;
            color: #ffd700;
            border-collapse: collapse;
        }
        .highscore-container table th, .highscore-container table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #fff;
        }
        .highscore-container table caption {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Wolken-Background -->
    <div class="clouds">
        <div class="clouds-1"></div>
        <div class="clouds-2"></div>
        <div class="clouds-3"></div>
    </div>
    <header>
        <h1>Wähle deinen Charakter!</h1>
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
    </header>

    <main>
        <div class="container">
            <!-- Charakter Auswahl -->
            <section class="charaktere-container">
                <h2>Wähle einen der folgenden Charaktere:</h2>
                <form action="abenteuer.php" method="POST">
                    <div class="charaktere">
                        <?php foreach ($charaktere as $charakter): ?>
                            <div>
                                <input type="radio" name="charakter" id="charakter_<?php echo $charakter['id']; ?>" value="<?php echo $charakter['id']; ?>" required>
                                <label for="charakter_<?php echo $charakter['id']; ?>">
                                    <span><?php echo $charakter['name']; ?></span>
                                    <img src="<?php echo $charakter['bild_url']; ?>" alt="Bild von <?php echo $charakter['name']; ?>">
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn">Bestätigen</button>
                </form>
            </section>

            <!-- Highscore Tabelle -->
            <section class="highscore-container">
                <h2>Highscore</h2>
                <table>
                    <caption>Punkte</caption>
                    <thead>
                        <tr>
                            <th>Position</th>
                            <th>Name</th>
                            <th>Datum</th>
                            <th>Punkte</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ausgabe der Highscore-Daten
                        if (count($highscores) > 0) {
                            $position = 1; // Startposition für das Ranking
                            foreach ($highscores as $highscore) {
                                $datum = new DateTime($highscore['registriert_am']);
                                echo "<tr>";
                                echo "<td>" . $position . "</td>";
                                echo "<td>" . htmlspecialchars($highscore['benutzername']) . "</td>";
                                echo "<td>" . $datum->format('d.m.Y') . "</td>";
                                echo "<td>" . $highscore['punkte'] . "</td>";
                                echo "</tr>";
                                $position++;
                            }
                        } else {
                            echo "<tr><td colspan='4'>Keine Highscores vorhanden.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>

   <footer>
        <p>&copy; 2025 Codebreakers - Battle of Minds Trading Card Game - Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>
