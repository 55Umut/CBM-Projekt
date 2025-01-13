<?php


// Spielerklasse (Charakter)
class Charakter {
    private $id;
    private $name;
    private $leben;
    private $standardangriff1;
    private $schaden1;
    private $standardangriff2;
    private $schaden2;
    private $standardangriff3;
    private $schaden3;
    private $spezialangriff;
    private $schaden_spezial;
    private $bild_url;

    public function __construct($id, $name, $leben, $standardangriff1, $schaden1, $standardangriff2, $schaden2, $standardangriff3, $schaden3, $spezialangriff, $schaden_spezial, $bild_url) {
        $this->id = $id;
        $this->name = $name;
        $this->leben = $leben;
        $this->standardangriff1 = $standardangriff1;
        $this->schaden1 = $schaden1;
        $this->standardangriff2 = $standardangriff2;
        $this->schaden2 = $schaden2;
        $this->standardangriff3 = $standardangriff3;
        $this->schaden3 = $schaden3;
        $this->spezialangriff = $spezialangriff;
        $this->schaden_spezial = $schaden_spezial;
        $this->bild_url = $bild_url;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getLeben() {
        return $this->leben;
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

    public function getSpezialangriff() {
        return $this->spezialangriff;
    }

    public function getSchadenSpezial() {
        return $this->schaden_spezial;
    }

    public function getBildUrl() {
        return $this->bild_url;
    }

    // Methode, um alle Charaktere aus der Datenbank zu laden
    public static function getAllCharaktere() {
        $pdo = getDbConnection();
        $stmt = $pdo->query("SELECT * FROM charaktere");
        $charaktere = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $charaktere[] = new self(
                $row['id'],
                $row['name'],
                $row['leben'],
                $row['standardangriff1'],
                $row['schaden1'],
                $row['standardangriff2'],
                $row['schaden2'],
                $row['standardangriff3'],
                $row['schaden3'],
                $row['spezialangriff'],
                $row['schaden_spezial'],
                $row['bild_url']
            );
        }

        return $charaktere;
    }
}

// Alle Charaktere aus der Datenbank holen
$charaktere = Charakter::getAllCharaktere();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charaktere</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Deine Charaktere</h1>
    <?php
    foreach ($charaktere as $charakter) {
        echo "<h2>" . $charakter->getName() . "</h2>";
        echo "Leben: " . $charakter->getLeben() . "<br>";
        echo "Standardangriff 1: " . $charakter->getStandardangriff1() . " (Schaden: " . $charakter->getSchaden1() . ")<br>";
        echo "Standardangriff 2: " . $charakter->getStandardangriff2() . " (Schaden: " . $charakter->getSchaden2() . ")<br>";
        echo "Standardangriff 3: " . $charakter->getStandardangriff3() . " (Schaden: " . $charakter->getSchaden3() . ")<br>";
        echo "Spezialangriff: " . $charakter->getSpezialangriff() . " (Schaden: " . $charakter->getSchadenSpezial() . ")<br>";
        echo "Bild: <img src='" . $charakter->getBildUrl() . "' alt='" . $charakter->getName() . "'><br><br>";
    }
    ?>
</body>
</html>
