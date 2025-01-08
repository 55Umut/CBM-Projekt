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

$conn->close();

// Charakterauswahl-Funktion
if (isset($_POST['charakter'])) {
    $_SESSION['charakter'] = $_POST['charakter']; // Den ausgewählten Charakter speichern
    header("Location: spiel.php"); // Weiterleitung zur nächsten Seite nach der Auswahl
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abenteuer - Trading Card Game</title>
    <link rel="stylesheet" href="styles1.css"> <!-- Verweis auf die angepasste CSS-Datei -->
    <style>
        /* Zusätzliche CSS-Regeln für das Layout der Charaktere */
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        /* Der Charakterbereich soll den gesamten Bildschirm füllen */
        .charaktere-container {
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Verteile Platz zwischen den Charakteren und Footer */
            height: calc(100vh - 140px); /* 100% Höhe minus Header und Footer */
            padding: 20px;
            overflow-y: auto; /* Scrollen falls der Inhalt zu groß ist */
        }

        .charaktere {
            display: grid;
            grid-template-columns: repeat(6, 1fr); /* 6 Spalten */
            gap: 20px;
            margin-top: 30px;
            justify-items: center;
            align-items: center;
            flex: 1; /* Der Charakterbereich nimmt den verfügbaren Platz ein */
        }

        .charaktere label {
            display: flex;
            flex-direction: column;
            align-items: center; /* Vertikal zentrieren von Text und Bild */
            cursor: pointer;
            position: relative;
            text-align: center;
            width: 100%;
            max-width: 150px; /* Maximale Breite für jedes Label */
            transition: transform 0.3s ease;
        }

        .charaktere img {
            width: 100%;  /* Bildgröße an die Breite des Containers anpassen */
            height: 150px; /* Bildhöhe festgelegt */
            margin-bottom: 10px; /* Abstand zwischen Bild und Name */
            border-radius: 8px; /* Runde Ecken für das Bild */
            object-fit: cover; /* Bild proportional anpassen */
            transition: transform 0.3s ease;
        }

        .charaktere input[type="radio"] {
            display: none; /* Radio-Buttons unsichtbar machen */
        }

        .charaktere input[type="radio"]:checked + label img {
            border: 3px solid #4CAF50; /* Wenn ausgewählt, das Bild umranden */
        }

        /* Hover-Effekte für Bilder */
        .charaktere label:hover img {
            transform: scale(1.05); /* Bild beim Hover leicht vergrößern */
        }

        /* Bestätigungsbutton */
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            font-size: 1.2em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 30px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #45a049;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px;
            margin-top: 40px;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        footer p {
            margin: 0;
            font-size: 1rem;
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
        <section class="charaktere-container">
            <h2>Wähle einen der folgenden Charaktere:</h2>
            <form action="abenteuer.php" method="POST">
                <div class="charaktere">
                    <?php foreach ($charaktere as $charakter): ?>
                        <div>
                            <input type="radio" name="charakter" id="charakter_<?php echo $charakter['id']; ?>" value="<?php echo $charakter['name']; ?>" required>
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
    </main>

    <footer>
        <p>&copy; 2025 Trading Card Game - Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>
