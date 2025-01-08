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
        // Überprüfen, ob der Benutzername oder die E-Mail bereits existiert
        $sql = "SELECT * FROM nutzer WHERE email = ? OR benutzername = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $benutzername);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Benutzername oder E-Mail bereits vergeben!";
        } else {
            // Passwort hashen
            $hashed_password = password_hash($passwort, PASSWORD_DEFAULT);

            // Neuen Benutzer in der Datenbank einfügen
            $sql = "INSERT INTO nutzer (benutzername, email, passwort) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $benutzername, $email, $hashed_password);

            if ($stmt->execute()) {
                $success = "Registrierung erfolgreich! Du kannst dich jetzt einloggen.";
                // Weiterleitung zum Projekt nach erfolgreicher Registrierung
                header("Location: projekt.php");  // Hier änderst du die Weiterleitung auf projekt.php
                exit();
            } else {
                $error = "Fehler bei der Registrierung! Bitte versuche es später erneut.";
            }
        }
    }
}

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

    <main>
      <section id="register">
        <h2>Registrierung</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
          <div class="input-group">
            <label for="username">Benutzername:</label>
            <input
              type="text"
              id="username"
              name="username"
              required
              placeholder="Wähle einen Benutzernamen"
            />
          </div>
          <div class="input-group">
            <label for="email">E-Mail:</label>
            <input
              type="email"
              id="email"
              name="email"
              required
              placeholder="Deine E-Mail"
            />
          </div>
          <div class="input-group">
            <label for="password">Passwort:</label>
            <input
              type="password"
              id="password"
              name="password"
              required
              placeholder="Dein Passwort"
            />
          </div>
          <div class="input-group">
            <label for="confirm-password">Passwort bestätigen:</label>
            <input
              type="password"
              id="confirm-password"
              name="confirm-password"
              required
              placeholder="Passwort erneut eingeben"
            />
          </div>
          <button type="submit" class="btn">Registrieren</button>
        </form>
        <p>Bereits ein Konto? <a href="login.php">Jetzt anmelden</a></p>
      </section>
    </main>

    <footer>
      <p>&copy; 2025 Trading Card Game - Alle Rechte vorbehalten.</p>
    </footer>
  </body>
</html>
