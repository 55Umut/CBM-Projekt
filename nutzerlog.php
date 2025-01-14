<?php
class NutzerLog {
    private $conn;

    // Konstruktor: Datenbankverbindung herstellen
    public function __construct($servername, $username, $password, $dbname) {
        // Verbindung zur Datenbank herstellen
        $this->conn = new mysqli($servername, $username, $password, $dbname);
        
        // Überprüfen, ob die Verbindung erfolgreich war
        if ($this->conn->connect_error) {
            die("Verbindung zur Datenbank fehlgeschlagen: " . $this->conn->connect_error);
        }
    }

    // Methode zum Einfügen eines Logeintrags
    public function insertLog($nutzer_id, $aktion, $details, $level = NULL, $punkte = NULL, $charakter_id = NULL, $status = 'Aktiv', $spiel_id = NULL) {
        // SQL-Statement vorbereiten, mit Platzhaltern für Parameter
        $sql = "INSERT INTO nutzer_log (nutzer_id, aktion, details, level, punkte, charakter_id, status, spiel_id, Login) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        // Vorbereiten der SQL-Anweisung
        $stmt = $this->conn->prepare($sql);
        
        // Überprüfen, ob das Statement erfolgreich vorbereitet wurde
        if ($stmt === false) {
            die("Fehler bei der Vorbereitung der SQL-Anweisung: " . $this->conn->error);
        }

        // Parametrisieren der Werte
        $stmt->bind_param("issiiiss", $nutzer_id, $aktion, $details, $level, $punkte, $charakter_id, $status, $spiel_id);
        
        // Ausführen und Fehlerbehandlung
        if ($stmt->execute()) {
            return true;
        } else {
            die("Fehler beim Ausführen des INSERT-Statements: " . $stmt->error);
        }
    }

    // Methode zum Abrufen der Logeinträge eines Nutzers
    public function getLogs($nutzer_id) {
        // SQL-Statement vorbereiten, um Logs eines Nutzers abzurufen
        $sql = "SELECT * FROM nutzer_log WHERE nutzer_id = ? ORDER BY Login DESC";
        
        // Vorbereiten der SQL-Anweisung
        $stmt = $this->conn->prepare($sql);
        
        // Überprüfen, ob das Statement erfolgreich vorbereitet wurde
        if ($stmt === false) {
            return false; // Fehler bei der Vorbereitung
        }

        // Parametrisieren der Werte
        $stmt->bind_param("i", $nutzer_id);
        
        // Ausführen der Anfrage
        $stmt->execute();
        
        // Ergebnisse abholen
        $result = $stmt->get_result();
        
        // Logs in ein Array speichern
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        
        return $logs;
    }

    // Verbindung schließen
    public function closeConnection() {
        $this->conn->close();
    }
}
?>
