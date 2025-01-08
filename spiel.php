<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Abenteuer starten</title>
  <link rel="stylesheet" href="styles1.css">
</head>
<body>
  <div class="clouds">
    <div class="clouds-1"></div>
    <div class="clouds-2"></div>
    <div class="clouds-3"></div>
  </div>

  <header>
    <h1>Dein Abenteuer beginnt!</h1>
  </header>

  <main>
    <section>
      <h2>Dein Abenteuer mit Eno geht jetzt los!</h2>
      <p id="intro-text">Jetzt kannst du in die Welt des Trading Card Games eintauchen und deine Reise starten.</p>
    </section>

    <!-- Video -->
    <video id="story-video" controls>
      <source src="storyline.mp4" type="video/mp4">
      Dein Browser unterstützt das Video-Tag nicht.
    </video>

    <!-- Überspringen-Button -->
    <button id="skip-button" onclick="skipVideo()">Überspringen</button>
  </main>

  <footer>
    <p>&copy; 2025 Trading Card Game - Alle Rechte vorbehalten.</p>
  </footer>

  <script>
    const video = document.getElementById('story-video');
    const skipButton = document.getElementById('skip-button');

    // Video anzeigen
    document.querySelector('section').style.display = 'none'; // Text-Section ausblenden
    video.style.display = 'block'; // Video sichtbar machen

    // Funktion für das Überspringen des Videos
    function skipVideo() {
      video.pause();
      video.currentTime = video.duration; // Video bis zum Ende abspielen
      window.location.href = 'level.php'; // Weiterleitung zur nächsten Seite
    }

    // Automatisch nach 5 Sekunden weiterleiten (optional)
    setTimeout(function() {
      skipVideo();
    }, 5000);
  </script>
</body>
</html>
