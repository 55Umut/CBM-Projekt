<?php
// logik1.php

// Funktion zur Berechnung des Schadens
function berechneSchaden($angriff_schaden, $verteidigung) {
    // Einfacher Schadensberechnungsmechanismus (z. B. Schaden wird um die Verteidigung des Gegners reduziert)
    $schaden = max(0, $angriff_schaden - $verteidigung);
    return $schaden;
}

// Funktion zum Angreifen
function angreifen($angreifer, $opfer, $angriffTyp) {
    // Bestimmen des Angriffs
    switch ($angriffTyp) {
        case 1:
            $angriffSchaden = $angreifer->getSchaden1();
            $angriffName = $angreifer->getStandardangriff1();
            break;
        case 2:
            $angriffSchaden = $angreifer->getSchaden2();
            $angriffName = $angreifer->getStandardangriff2();
            break;
        case 3:
            $angriffSchaden = $angreifer->getSchaden3();
            $angriffName = $angreifer->getStandardangriff3();
            break;
        case 'spezial':
            $angriffSchaden = $angreifer->getSchadenSpezial();
            $angriffName = $angreifer->getSpezialattacke();
            break;
        default:
            return "Ungültiger Angriff.";
    }

    // Berechne den verursachten Schaden unter Berücksichtigung der Verteidigung des Opfers
    $schaden = berechneSchaden($angriffSchaden, $opfer->getVerteidigung());
    
    // Schaden anwenden (z.B. Gesundheit des Opfers verringern)
    $opfer->setGesundheit($opfer->getGesundheit() - $schaden);

    // Rückgabe des Angriffs mit Schaden
    return "$angreifer->getName() greift mit $angriffName an und verursacht $schaden Schaden.";
}

// Funktion zur Heilung des Spielers
function heilen($spieler, $heilungsmenge) {
    $spieler->setGesundheit($spieler->getGesundheit() + $heilungsmenge);
    return "$spieler->getName() heilt sich um $heilungsmenge Punkte.";
}

// Funktion zur Bestimmung des Kampfergebnisses
function kampf($spieler, $boss) {
    // Hier führen wir die Angriffe im Kampf durch (Beispiel: 3 Angriffe des Spielers, dann Spezialangriff des Bosses)
    
    $angreifer = $spieler; // Der Spieler greift zuerst an
    $verteidiger = $boss;

    // Angriffe des Spielers
    $angriffErgebnis = angreifen($angreifer, $verteidiger, 1); // Beispiel: Standardangriff 1
    echo $angriffErgebnis . "<br>";
    
    $angriffErgebnis = angreifen($angreifer, $verteidiger, 2); // Standardangriff 2
    echo $angriffErgebnis . "<br>";

    // Spezialangriff des Spielers
    $angriffErgebnis = angreifen($angreifer, $verteidiger, 'spezial');
    echo $angriffErgebnis . "<br>";

    // Der Boss kontert mit einem Angriff
    $angriffErgebnis = angreifen($verteidiger, $angreifer, 1); // Beispiel: Standardangriff 1 des Bosses
    echo $angriffErgebnis . "<br>";

    // Prüfen, ob der Boss oder der Spieler besiegt wurde
    if ($verteidiger->getGesundheit() <= 0) {
        echo $verteidiger->getName() . " wurde besiegt!";
        return;
    }

    if ($angreifer->getGesundheit() <= 0) {
        echo $angreifer->getName() . " wurde besiegt!";
        return;
    }
}

// Beispiel für die Spielerklasse
class Spieler {
    private $name;
    private $gesundheit;
    private $verteidigung;
    
    public function __construct($name, $gesundheit, $verteidigung) {
        $this->name = $name;
        $this->gesundheit = $gesundheit;
        $this->verteidigung = $verteidigung;
    }

    public function getName() {
        return $this->name;
    }

    public function getGesundheit() {
        return $this->gesundheit;
    }

    public function setGesundheit($gesundheit) {
        $this->gesundheit = $gesundheit;
    }

    public function getVerteidigung() {
        return $this->verteidigung;
    }
}

// Beispiel für die Bossklasse (siehe bosse.php)
class Boss {
    private $name;
    private $gesundheit;
    private $verteidigung;

    public function __construct($name, $gesundheit, $verteidigung) {
        $this->name = $name;
        $this->gesundheit = $gesundheit;
        $this->verteidigung = $verteidigung;
    }

    public function getName() {
        return $this->name;
    }

    public function getGesundheit() {
        return $this->gesundheit;
    }

    public function setGesundheit($gesundheit) {
        $this->gesundheit = $gesundheit;
    }

    public function getVerteidigung() {
        return $this->verteidigung;
    }
}

// Beispiel für die Verwendung der Kampf-Logik
$spieler = new Spieler("Held", 100, 10);
$boss = new Boss("Drache", 150, 5);

// Kampf durchführen
kampf($spieler, $boss);
?>
