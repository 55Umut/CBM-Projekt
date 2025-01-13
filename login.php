<?php
// Verbindungsdaten zur Datenbank
$servername = "localhost";  // Hostname
$username = "root";         // Datenbank-Benutzername
$password = "";             // Datenbank-Passwort
$dbname = "kartenspiel1_db"; // Deine Datenbank

// Verbindung zur Datenbank herstellen
$conn = new mysqli($servername, $username, $password, $dbname);

// Überprüfen, ob die Verbindung erfolgreich war
if ($conn->connect_error) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error);
}

$error = "";
$success = "";

// Wenn das Formular abgesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Benutzereingaben aus dem Formular
    $email = $_POST['email'];
    $passwort = $_POST['password'];

    // Starten einer Transaktion
    $conn->begin_transaction();

    try {
        // Überprüfen, ob die E-Mail und das Passwort korrekt sind
        $sql = "SELECT * FROM nutzer WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Falls Benutzer gefunden wurde
        if ($result->num_rows > 0) {
            // Daten des Benutzers aus der Datenbank holen
            $row = $result->fetch_assoc();
            
            // Überprüfen, ob das eingegebene Passwort mit dem gehashten Passwort übereinstimmt
            if (password_verify($passwort, $row['passwort'])) {
                // Erfolg: Benutzer eingeloggt, Session starten und zum Dashboard weiterleiten
                session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['benutzername'] = $row['benutzername'];
                $_SESSION['email'] = $row['email'];
                
                // Commit der Transaktion, falls alles erfolgreich war
                $conn->commit();

                // Weiterleitung zur Startseite (z.B. projekt.php oder abenteuer.php)
                header("Location: abenteuer.php");
                exit();
            } else {
                $error = "Ungültige E-Mail oder Passwort!";
            }
        } else {
            $error = "Ungültige E-Mail oder Passwort!";
        }

        // Wenn ein Fehler auftritt, Transaktion zurückrollen
        if (!empty($error)) {
            $conn->rollback();
        }

    } catch (Exception $e) {
        // Falls ein Fehler auftritt, wird die Transaktion zurückgerollt
        $conn->rollback();
        $error = "Ein Fehler ist aufgetreten. Bitte versuche es später erneut.";
    }
}

// Verbindung schließen
$conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Trading Card Game</title>
    <link rel="stylesheet" href="styles.css">  <!-- CSS-Datei einbinden -->
</head>
<body>
    <audio id="background-audio" preload="auto">
        <source src="Willkommen.mp3" type="audio/mp3">
        Dein Browser unterstützt das Abspielen von Audio nicht.
    </audio>

    <header>
        <h1>Willkommen!</h1>
    </header>

    <main>
        <section id="login">
            <h2>Login</h2>

            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="input-group">
                    <label for="email">E-Mail:</label>
                    <input type="email" id="email" name="email" required placeholder="Deine E-Mail">
                </div>
                <div class="input-group">
                    <label for="password">Passwort:</label>
                    <input type="password" id="password" name="password" required placeholder="Dein Passwort">
                </div>
                <button type="submit" class="btn">Einloggen</button>
            </form>
            <p>Noch kein Konto? <a href="register.php">Jetzt registrieren</a></p>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Trading Card Game - Alle Rechte vorbehalten.</p>
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
