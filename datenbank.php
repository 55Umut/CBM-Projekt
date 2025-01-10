<?php
// Datenbankverbindung
$dsn = 'mysql:host=localhost;dbname=kartenspiel1_db;charset=utf8';
$username = 'root'; // Benutzername für XAMPP (oder je nach deinem Setup)
$password = ''; // Passwort für XAMPP (oder je nach deinem Setup)

try {
    $pdo = new PDO($dsn, $username, $password);
    // Setzt den PDO-Fehlermodus auf Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Verbindungsfehler: ' . $e->getMessage();
    exit;
}

// Funktion zum Abrufen aller Daten einer Tabelle
function fetchAllRecords($pdo, $table) {
    $stmt = $pdo->query("SELECT * FROM $table");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Wenn das Formular zur Aktualisierung eines Datensatzes gesendet wird
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $table = $_POST['table'];
    $id = $_POST['id'];
    $column = $_POST['column'];
    $value = $_POST['value'];

    // SQL-Abfrage zum Aktualisieren eines Datensatzes
    $sql = "UPDATE $table SET $column = :value WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['value' => $value, 'id' => $id]);

    echo "Datensatz wurde aktualisiert!";
}

// Wenn das Formular zum Löschen eines Datensatzes gesendet wird
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $table = $_POST['table'];
    $id = $_POST['id'];

    // SQL-Abfrage zum Löschen eines Datensatzes
    $sql = "DELETE FROM $table WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    echo "Datensatz wurde gelöscht!";
}

// Wenn das Formular zum Hinzufügen eines Datensatzes gesendet wird
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $table = $_POST['table'];
    $columns = $_POST['columns']; // z.B. name, email
    $values = $_POST['values']; // z.B. "Test", "test@mail.com"

    // SQL-Abfrage zum Hinzufügen eines Datensatzes
    $sql = "INSERT INTO $table ($columns) VALUES ($values)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    echo "Neuer Datensatz wurde hinzugefügt!";
}

// Funktion zur Anzeige der Tabelleninhalte
function displayTable($records, $table) {
    echo "<h3>Inhalt der Tabelle: $table</h3>";
    echo "<table border='1'><thead><tr>";
    // Spaltenüberschriften dynamisch basierend auf den Daten
    foreach ($records[0] as $column => $value) {
        echo "<th>$column</th>";
    }
    echo "<th>Aktionen</th></tr></thead><tbody>";

    // Zeilen ausgeben
    foreach ($records as $record) {
        echo "<tr>";
        foreach ($record as $column => $value) {
            echo "<td>$value</td>";
        }
        echo "<td>
                <a href='#' onclick='showUpdateForm(\"$table\", {$record['id']})'>Bearbeiten</a> | 
                <a href='#' onclick='deleteRecord(\"$table\", {$record['id']})'>Löschen</a>
              </td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Datenbank anzeigen und bearbeiten</title>
    <script>
        // Funktion zur Anzeige des Update-Formulars
        function showUpdateForm(table, id) {
            const column = prompt("Welche Spalte möchtest du bearbeiten?");
            const value = prompt("Gib den neuen Wert ein:");
            if (column && value) {
                // Formulardaten senden
                document.getElementById("update-form").elements['table'].value = table;
                document.getElementById("update-form").elements['id'].value = id;
                document.getElementById("update-form").elements['column'].value = column;
                document.getElementById("update-form").elements['value'].value = value;
                document.getElementById("update-form").submit();
            }
        }

        // Funktion zum Löschen eines Datensatzes
        function deleteRecord(table, id) {
            if (confirm("Bist du sicher, dass du diesen Datensatz löschen möchtest?")) {
                document.getElementById("delete-form").elements['table'].value = table;
                document.getElementById("delete-form").elements['id'].value = id;
                document.getElementById("delete-form").submit();
            }
        }
    </script>
</head>
<body>
    <h1>Verwalte deine Datenbank</h1>

    <!-- Formulare zum Bearbeiten, Löschen und Hinzufügen -->
    <form id="update-form" method="POST">
        <input type="hidden" name="update" value="1">
        <input type="hidden" name="table">
        <input type="hidden" name="id">
        <input type="hidden" name="column">
        <input type="hidden" name="value">
    </form>

    <form id="delete-form" method="POST">
        <input type="hidden" name="delete" value="1">
        <input type="hidden" name="table">
        <input type="hidden" name="id">
    </form>

    <form method="POST">
        <h3>Neuen Datensatz hinzufügen</h3>
        <label for="table">Tabelle wählen:</label>
        <select name="table" required>
            <option value="aktionen">Aktionen</option>
            <option value="bosse">Bosse</option>
            <option value="charaktere">Charaktere</option>
            <!-- Weitere Tabellen hier einfügen -->
        </select>
        <br>
        <label for="columns">Spalten (kommagetrennt):</label>
        <input type="text" name="columns" required placeholder="z.B. name, email">
        <br>
        <label for="values">Werte (kommagetrennt):</label>
        <input type="text" name="values" required placeholder="z.B. 'Test', 'test@mail.com'">
        <br>
        <button type="submit" name="add" value="1">Hinzufügen</button>
    </form>

    <hr>

    <?php
    // Alle Tabellen abrufen und anzeigen
    $tables = ['aktionen', 'bosse', 'charaktere', 'freigeschaltete_charaktere', 'kampf_status', 'level_system', 'nutzer', 'runden', 'spiel_status', 'uhrzeiten'];
    
    foreach ($tables as $table) {
        $records = fetchAllRecords($pdo, $table);
        if (!empty($records)) {
            displayTable($records, $table);
        } else {
            echo "<h3>Keine Daten in der Tabelle: $table</h3>";
        }
        echo "<hr>";
    }
    ?>

</body>
</html>
