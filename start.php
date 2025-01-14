<?php
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Wenn nicht eingeloggt, zur Login-Seite weiterleiten
    exit();
}

// Benutzerinformationen aus der Session
$benutzername = $_SESSION['benutzername'];

// Abmelden-Funktion
if (isset($_POST['logout'])) {
    // Alle Session-Daten löschen und den Benutzer abmelden
    session_unset();
    session_destroy();
    header("Location: login.php"); // Nach dem Abmelden zur Login-Seite weiterleiten
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start - Trading Card Game</title>
    <link rel="stylesheet" href="styles1.css"> <!-- Verweis auf das Stylesheet -->
</head>
<body>
    <!-- Wolken-Background -->
    <div class="clouds">
        <div class="clouds-1"></div>
        <div class="clouds-2"></div>
        <div class="clouds-3"></div>
    </div>

    <header>
        <div class="container">
            <h1>Willkommen, <?php echo htmlspecialchars($benutzername); ?>!</h1>
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
        </div>
    </header>

    <main>
        <section>
            <h2>Dein Abenteuer beginnt!</h2>
            <p>Willkommen im Trading Card Game. Hier kannst du dein Abenteuer starten und gegen andere Spieler antreten.</p>
            <p>Wähle eine der Optionen im Menü, um zu deinem Deck zu gehen oder neue Karten zu kaufen.</p>
            <!-- Los-Button -->
            <form action="abenteuer.php" method="GET">
                <button type="submit" class="btn">Los!</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Codebreakers - Battle of Minds Trading Card Game - Alle Rechte vorbehalten.</p>
    </footer>
</body>
</html>
