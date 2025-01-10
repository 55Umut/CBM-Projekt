<?php
require_once 'logik1.php';
require_once 'config.php'; // Stellen Sie sicher, dass die Konfigurationsdatei eingebunden ist

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

    public function __construct($id, $name, $spezialattacke, $schaden_spezial, $standardangriff1, $schaden1, $standardangriff2, $schaden2, $standardangriff3, $schaden3) {
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
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getSpezialattacke() {
        return $this->spezialattacke;
    }

    public function getSchadenSpezial() {
        return $this->schaden_spezial;
    }

    public function getStandardangriff1() {
        return $this->standardangriff1;
    }

    public function getSchaden1() {
        return $this->schaden1;
    }

    public function getStandardangriff2() {
        return $this->standardangriff2;
    }

    public function getSchaden2() {
        return $this->schaden2;
    }

    public function getStandardangriff3() {
        return $this->standardangriff3;
    }

    public function getSchaden3() {
        return $this->schaden3;
    }

    // Statische Methode, um alle Bosse aus der Datenbank zu laden
    public static function getAllBosse() {
        $pdo = getDbConnection();
        $stmt = $pdo->query("SELECT * FROM bosse");
        $bosse = [];

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
                $row['schaden3']
            );
        }

        return $bosse;
    }
}

?>
