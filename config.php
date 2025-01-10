<?php
// Überprüfen, ob eine Session bereits gestartet wurde, und nur dann starten, wenn noch keine existiert
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Wenn nicht eingeloggt, zur Login-Seite weiterleiten
    exit();
}

// Benutzerinformationen aus der Session
$benutzername = $_SESSION['benutzername'];

// Überprüfen, ob ein Charakter ausgewählt wurde
if (!isset($_SESSION['charakter'])) {
    header('Location: abenteuer.php'); // Umleitung, falls kein Charakter ausgewählt wurde
    exit();
}

$charakter = $_SESSION['charakter']; // Ausgewählter Charakter
$punkte = isset($_SESSION['punkte']) ? $_SESSION['punkte'] : 0; // Punkte des Spielers
$level = isset($_SESSION['level']) ? $_SESSION['level'] : 1; // Aktuelles Level des Spielers

// Verbindung zur Datenbank herstellen (PDO-Verbindung)

$servername = "localhost";  // Hier den Servernamen eintragen
$username = "root";         // Dein Datenbank-Benutzername
$password = "";             // Dein Datenbank-Passwort
$dbname = "kartenspiel1_db"; // Dein Datenbankname

// Erstelle eine neue PDO-Verbindung
function getDbConnection() {
    global $servername, $username, $password, $dbname; // Zugriff auf die globalen Variablen
    try {
        $pdo = new PDO('mysql:host=' . $servername . ';dbname=' . $dbname, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die('Verbindung fehlgeschlagen: ' . $e->getMessage());
    }
}
?>
