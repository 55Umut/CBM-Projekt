<?php
// Funktion zur Verbindung mit der Datenbank
function getDbConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "kartenspiel1_db";

    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        echo "Verbindung fehlgeschlagen: " . $e->getMessage();
        exit();
    }
}

// Boss-Klasse
class Boss {
    private $id;
    private $name;
    private $spezialattacke;
    private $schaden_spezial;
    private $standardangriff1;
    private $schaden1;
    private $standardangriff2;
    private $schaden2;
    private $standardangriff3;
    private $schaden3;
    private $leben;
    private $bild_url;

    // Konstruktor, um die Boss-Daten zu initialisieren
    public function __construct($id, $name, $spezialattacke, $schaden_spezial, $standardangriff1, $schaden1, $standardangriff2, $schaden2, $standardangriff3, $schaden3, $leben, $bild_url) {
        $this->id = $id;
        $this->name = $name;
        $this->spezialattacke = $spezialattacke;
        $this->schaden_spezial = $schaden_spezial;
        $this->standardangriff1 = $standardangriff1;
        $this->schaden1 = $schaden1;
        $this->standardangriff2 = $standardangriff2;
        $this->schaden2 = $schaden2;
        $this->standardangriff3 = $standardangriff3;
        $this->schaden3 = $schaden3;
        $this->leben = $leben;
        $this->bild_url = $bild_url;
    }

    // Getter-Methoden
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getSpezialattacke() { return $this->spezialattacke; }
    public function getSchadenSpezial() { return $this->schaden_spezial; }
    public function getStandardangriff1() { return $this->standardangriff1; }
    public function getSchaden1() { return $this->schaden1; }
    public function getStandardangriff2() { return $this->standardangriff2; }
    public function getSchaden2() { return $this->schaden2; }
    public function getStandardangriff3() { return $this->standardangriff3; }
    public function getSchaden3() { return $this->schaden3; }
    public function getLeben() { return $this->leben; }
    public function getBildUrl() { return $this->bild_url; }

    // Statische Methode, um alle Bosse aus der Datenbank zu laden
    public static function getAllBosse() {
        // Verbindung zur Datenbank
        $pdo = getDbConnection();
        
        // SQL-Abfrage, um alle Bosse zu laden
        $stmt = $pdo->query("SELECT * FROM bosse");
        
        // Array, um die Boss-Objekte zu speichern
        $bosse = [];

        // Alle Bosse laden und in Objekte umwandeln
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bosse[] = new self(
                $row['id'],
                $row['name'],
                $row['spezialattacke'],
                $row['schaden_spezial'],
                $row['standardangriff1'],
                $row['schaden1'],
                $row['standardangriff2'],
                $row['schaden2'],
                $row['standardangriff3'],
                $row['schaden3'],
                $row['leben'],
                $row['bild_url']
            );
        }

        // Alle geladenen Bosse zurückgeben
        return $bosse;
    }

    // Zufällig einen Boss auswählen
    public static function getRandomBoss() {
        // Verbindung zur Datenbank
        $pdo = getDbConnection();
        
        // SQL-Abfrage, um einen zufälligen Boss zu laden
        $stmt = $pdo->query("SELECT * FROM bosse ORDER BY RAND() LIMIT 1");
        
        // Einen Boss laden und zurückgeben
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return new self(
            $row['id'],
            $row['name'],
            $row['spezialattacke'],
            $row['schaden_spezial'],
            $row['standardangriff1'],
            $row['schaden1'],
            $row['standardangriff2'],
            $row['schaden2'],
            $row['standardangriff3'],
            $row['schaden3'],
            $row['leben'],
            $row['bild_url']
        );
    }
}
?>
