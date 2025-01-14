<?php
// Datenbankverbindung
$servername = "localhost";  // Dein Datenbank-Host
$username = "root";         // Dein Datenbank-Benutzername
$password = "";             // Dein Datenbank-Passwort
$dbname = "kartenspiel1_db"; // Der Name deiner Datenbank

// Verbindung zur Datenbank herstellen
$conn = new mysqli($servername, $username, $password, $dbname);

// Überprüfen, ob die Verbindung erfolgreich war
if ($conn->connect_error) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error);
}

$error = $success = "";

// Wenn das Formular abgesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Eingabedaten
    $benutzername = $_POST['username'];
    $email = $_POST['email'];
    $passwort = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    // Passwortbestätigung
    if ($passwort !== $confirm_password) {
        $error = "Die Passwörter stimmen nicht überein!";
    } else {
        // Starten einer Transaktion
        $conn->begin_transaction();

        try {
            // Überprüfen, ob der Benutzername oder die E-Mail bereits existiert
            $sql = "SELECT * FROM nutzer WHERE email = ? OR benutzername = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $email, $benutzername);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Benutzername oder E-Mail bereits vergeben!";
                $conn->rollback(); // Rollback bei Fehler
            } else {
                // Passwort hashen
                $hashed_password = password_hash($passwort, PASSWORD_DEFAULT);

                // Neuen Benutzer in der Datenbank einfügen
                $sql = "INSERT INTO nutzer (benutzername, email, passwort) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $benutzername, $email, $hashed_password);

                if ($stmt->execute()) {
                    // Erfolgreiche Registrierung
                    $success = "Registrierung erfolgreich! Du kannst dich jetzt einloggen.";

                    // Session starten
                    session_start();
                    $_SESSION['user_id'] = $conn->insert_id; // Die ID des eingefügten Benutzers
                    $_SESSION['benutzername'] = $benutzername;
                    $_SESSION['email'] = $email;

                    // Commit der Transaktion
                    $conn->commit();

                    // Weiterleitung zur Startseite (z.B. abenteuer.php)
                    header("Location: abenteuer.php");
                    exit();
                } else {
                    $error = "Fehler bei der Registrierung! Bitte versuche es später erneut.";
                    $conn->rollback(); // Rollback bei Fehler
                }
            }
        } catch (Exception $e) {
            // Falls ein Fehler auftritt, wird die Transaktion zurückgerollt
            $conn->rollback();
            $error = "Ein Fehler ist aufgetreten. Bitte versuche es später erneut.";
        }
    }
}

// Verbindung schließen
$conn->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Trading Card Game - Registrierung</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <header>
        <h1>Registriere dich für das Trading Card Game</h1>
    </header>
    
    <audio id="background-audio" preload="auto">
        <source src="Allefelder.mp3" type="audio/mp3">
        Dein Browser unterstützt das Abspielen von Audio nicht.
    </audio>
    
    <main>
        <section id="register">
            <h2>Registrierung</h2>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form action="register.php" method="POST" autocomplete="on">
                <div class="input-group">
                    <label for="username">Benutzername:</label>
                    <input type="text" id="username" name="username" required placeholder="Wähle einen Benutzernamen" autocomplete="username" />
                </div>
                <div class="input-group">
                    <label for="email">E-Mail:</label>
                    <input type="email" id="email" name="email" required placeholder="Deine E-Mail" autocomplete="email" />
                </div>
                <div class="input-group">
                    <label for="password">Passwort:</label>
                    <input type="password" id="password" name="password" required placeholder="Dein Passwort" autocomplete="new-password" />
                </div>
                <div class="input-group">
                    <label for="confirm-password">Passwort bestätigen:</label>
                    <input type="password" id="confirm-password" name="confirm-password" required placeholder="Passwort erneut eingeben" autocomplete="new-password" />
                </div>
                <button type="submit" class="btn">Registrieren</button>
            </form>
            <p>Bereits ein Konto? <a href="login.php">Jetzt anmelden</a></p>
        </section>
    </main>

  <footer>
        <p>&copy; 2025 Codebreakers - Battle of Minds Trading Card Game - Alle Rechte vorbehalten.</p>
    </footer>
    <script>
        // Funktion zum Abspielen des Audios beim Laden der Seite
        window.onload = function() {
            var audio = document.getElementById('background-audio');
            audio.play().catch(function(error) {
                var div = document.createElement('div');
                div.style.display = 'none';
                document.body.appendChild(div);
                div.click();
            });
        };
    </script>
</body>
</html>
