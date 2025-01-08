<?php
// Datenbankverbindung
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kartenspiel1_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Beim Hochladen des Bildes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['character_id']) && isset($_POST['image'])) {
        $character_id = $_POST['character_id'];
        $imageData = $_POST['image'];

        // Entfernen der Base64-Daten und nur das Bild übrig lassen
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
        $imageData = str_replace('data:image/gif;base64,', '', $imageData);
        $decodedImage = base64_decode($imageData);

        // Zufälligen Dateinamen für das Bild generieren
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Verzeichnis erstellen, falls es nicht existiert
        }
        $fileName = uniqid() . '.png';
        $filePath = $uploadDir . $fileName;

        // Bild auf dem Server speichern
        file_put_contents($filePath, $decodedImage);

        // Das Bild in der Datenbank für den ausgewählten Charakter speichern
        $stmt = $conn->prepare("UPDATE charaktere SET bild_url = ? WHERE id = ?");
        $stmt->bind_param("si", $filePath, $character_id);
        $stmt->execute();

        $stmt->close();
        $conn->close();

        // Erfolgsmeldung zurückgeben
        echo json_encode(['success' => true, 'filePath' => $filePath]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Fehler: Kein Charakter ausgewählt oder kein Bild hochgeladen.']);
    }
}

// Alle Charaktere aus der Datenbank laden
$charactersQuery = "SELECT id, name FROM charaktere";
$charactersResult = $conn->query($charactersQuery);
$characters = [];
if ($charactersResult->num_rows > 0) {
    while($row = $charactersResult->fetch_assoc()) {
        $characters[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bild für Charakter hochladen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f7f7f7;
        }
        .container {
            text-align: center;
        }
        .drop-area {
            border: 2px dashed #ccc;
            padding: 20px;
            width: 300px;
            height: 200px;
            margin: 20px auto;
            position: relative;
            cursor: pointer;
        }
        .drop-area p {
            color: #ccc;
            font-size: 16px;
        }
        .drop-area img {
            max-width: 100%;
            max-height: 100%;
            display: block;
            margin: 0 auto;
        }
        .preview-area {
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #45a049;
        }
        /* Runde Bilder */
        .rounded-image {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Wählen Sie einen Charakter und laden Sie ein Bild hoch</h2>

        <!-- Auswahlfeld für Charaktere -->
        <form id="upload-form" method="POST" enctype="multipart/form-data">
            <label for="character-select">Wählen Sie einen Charakter:</label>
            <select name="character_id" id="character-select">
                <?php foreach ($characters as $character): ?>
                    <option value="<?= $character['id']; ?>"><?= htmlspecialchars($character['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <br><br>

            <!-- Drop-Area für Bild -->
            <div class="drop-area" id="drop-area">
                <p>Ziehe dein Bild hierher oder klicke, um ein Bild auszuwählen</p>
                <input type="file" id="file-input" name="image" style="display:none" accept="image/*">
                <img id="image-preview" class="rounded-image" src="" alt="" style="display:none;">
            </div>

            <!-- Vorschau des Bildes -->
            <div class="preview-area" id="preview-area" style="display:none;">
                <h3>Vorschau des Bildes:</h3>
                <canvas id="canvas" style="display:none;"></canvas>
                <img id="final-preview" class="rounded-image" src="" alt="Bildvorschau">
                <br><br>
                <button type="submit" class="btn">Bild hochladen</button>
            </div>
        </form>
    </div>

    <script>
        // Elemente
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('file-input');
        const imagePreview = document.getElementById('image-preview');
        const finalPreview = document.getElementById('final-preview');
        const previewArea = document.getElementById('preview-area');
        const uploadForm = document.getElementById('upload-form');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');

        // Event Listener für Drag-and-Drop
        dropArea.addEventListener('click', () => fileInput.click());
        dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropArea.style.borderColor = '#4CAF50';
        });
        dropArea.addEventListener('dragleave', () => {
            dropArea.style.borderColor = '#ccc';
        });
        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            const file = e.dataTransfer.files[0];
            handleFile(file);
        });

        // Event Listener für die Dateiauswahl
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            handleFile(file);
        });

        // Funktion zur Bearbeitung und Vorschau des Bildes
        function handleFile(file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = new Image();
                img.onload = function() {
                    // Setze das Canvas auf die Größe 100x100
                    canvas.width = 100;
                    canvas.height = 100;

                    // Zeichne das Bild auf das Canvas, wobei wir es rund und auf 100x100 skalieren
                    ctx.clearRect(0, 0, canvas.width, canvas.height); // Canvas leeren
                    ctx.beginPath();
                    ctx.arc(50, 50, 50, 0, Math.PI * 2, true); // Runde Form
                    ctx.closePath();
                    ctx.clip(); // Clip das Bild auf die runde Form
                    ctx.drawImage(img, 0, 0, 100, 100);

                    // Hole das Base64-encoded Bild von Canvas
                    const dataUrl = canvas.toDataURL('image/png');

                    // Zeige das Bild in der Vorschau an
                    imagePreview.src = dataUrl;
                    imagePreview.style.display = 'block';
                    finalPreview.src = dataUrl;
                    previewArea.style.display = 'block';
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        // Formular vor dem Absenden verhindern, damit es mit AJAX übermittelt wird
        uploadForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const imageData = finalPreview.src;
            const characterId = document.getElementById('character-select').value;

            if (!imageData) {
                alert('Kein Bild zum Hochladen ausgewählt.');
                return;
            }

            // Hier rufen wir die PHP-Funktion auf, die das Bild in die Datenbank hochlädt
            uploadImage(characterId, imageData);
        });

        // Funktion zum Hochladen des Bildes
        function uploadImage(characterId, imageData) {
            const formData = new FormData();
            formData.append('image', imageData);
            formData.append('character_id', characterId);

            // Sende das Bild an die PHP-Datei
            fetch('upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Bild erfolgreich hochgeladen!');
                } else {
                    alert('Fehler beim Hochladen des Bildes.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Fehler beim Hochladen des Bildes.');
            });
        }
    </script>
</body>
</html>
