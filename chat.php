<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$serverId = isset($_GET['server_id']) ? $_GET['server_id'] : null;
$channelId = isset($_GET['channel_id']) ? $_GET['channel_id'] : null;

if ($serverId) {
    // Kanäle des Servers abrufen
    $stmt = $pdo->prepare('SELECT * FROM channels WHERE server_id = ?');
    $stmt->execute([$serverId]);
    $channels = $stmt->fetchAll();
} else {
    echo "Please select a server.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];

    // Prüfen, ob der Benutzer stummgeschaltet ist
    $stmt = $pdo->prepare('SELECT is_muted FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $isMuted = $stmt->fetchColumn();

    if ($isMuted) {
        echo "You are muted and cannot send messages.";
        exit();
    }

    // Prüfen, ob die Nachricht ein Befehl ist
    if (strpos($message, '/') === 0) {
        $response = processCommand($pdo, $_SESSION['user_id'], $message);
        echo $response;
        exit();
    }

    // Nachricht in die Datenbank einfügen
    if ($channelId) {
        $stmt = $pdo->prepare('INSERT INTO messages (channel_id, user_id, message) VALUES (?, ?, ?)');
        $stmt->execute([$channelId, $_SESSION['user_id'], $message]);
    }
}

// Nachrichten des aktuellen Kanals abrufen
if ($channelId) {
    $stmt = $pdo->prepare('SELECT messages.*, users.username FROM messages JOIN users ON messages.user_id = users.id WHERE channel_id = ? ORDER BY messages.created_at DESC');
    $stmt->execute([$channelId]);
    $messages = $stmt->fetchAll();
} else {
    $messages = [];
}

// Funktion zum Anzeigen der Fehlermeldung
function showError($message) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>';
}

// Funktion zum Anzeigen der Erfolgsmeldung
function showSuccess($message) {
    echo '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twemoji/13.1.0/twemoji.min.js" integrity="sha512-RQw3/wmAmUaFxxIIJzrqH8VrDBFnK2wzFznCV1mwNThxUh7ikZIiVgHvF81+ixwPG7i0abflOlU3S8FDuue4Rg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.2/emojionearea.min.css" integrity="sha512-ExjxAPLbJB/xE0nQqMGBZGrkYXNKXc2NCNBrL5r+lfN7NeAeEqafQH2RxyyPq67lPCocQnt9Y5xwJ+U+kmX0Xw==" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.2/emojionearea.min.js" integrity="sha512-JeyXo+zZ/1xSffOHfG2rvIbj+uPO5t34QfA53z8F5UG+YXxMldXuIY2lc3tNUX7NtxNyvI7V9NTKtHLh6Lo+VQ==" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            $("#message").emojioneArea();
        });
    </script>
    
    <script>
        var pushToTalkKey = 'Control'; // Beispiel: "Control" Taste
        var isPushToTalkActive = false;

        document.addEventListener('keydown', function(event) {
          if (event.key === pushToTalkKey && !isPushToTalkActive) {
            // Starte das Senden von Audiodaten
            isPushToTalkActive = true;
            startSendingAudio();
          }
        });

        document.addEventListener('keyup', function(event) {
          if (event.key === pushToTalkKey && isPushToTalkActive) {
            // Stoppe das Senden von Audiodaten
            isPushToTalkActive = false;
            stopSendingAudio();
          }
        });

        function startSendingAudio() {
          // Code zum Senden von Audiodaten
        }

        function stopSendingAudio() {
          // Code zum Stoppen des Sendens von Audiodaten
        }

        $(document).ready(function() {
            // Zugriff auf das Mikrofon erhalten und Lautstärke darstellen
            navigator.mediaDevices.getUserMedia({ audio: true })
              .then(function(stream) {
                var audioContext = new AudioContext();
                var audioInput = audioContext.createMediaStreamSource(stream);
                var analyser = audioContext.createAnalyser();
                audioInput.connect(analyser);
                
                var canvas = document.getElementById("volume-meter");
                var canvasCtx = canvas.getContext("2d");
                var bufferLength = analyser.frequencyBinCount;
                var dataArray = new Uint8Array(bufferLength);

                function draw() {
                  requestAnimationFrame(draw);
                  
                  analyser.getByteFrequencyData(dataArray);
                  var volume = dataArray.reduce((a, b) => a + b, 0) / bufferLength;
                  
                  canvasCtx.clearRect(0, 0, canvas.width, canvas.height);
                  canvasCtx.fillStyle = 'green';
                  canvasCtx.fillRect(0, 0, volume * canvas.width, canvas.height);
                }
                
                draw();
              })
              .catch(function(err) {
                console.log('The following getUserMedia error occurred: ' + err);
              });
        });
        
        // Funktion zum Analysieren und Ersetzen von Emoji-Zeichen
        function parseEmojis() {
            var messages = document.getElementsByClassName('message-text');
            Array.from(messages).forEach(function(message) {
                twemoji.parse(message);
            });
        }

        // Rufe die Funktion parseEmojis() auf, wenn die Seite geladen wurde
        document.addEventListener('DOMContentLoaded', function() {
            parseEmojis();
        });
    </script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <h2>Servers</h2>
                <ul class="list-group">
                      <?php
                    // Server des Benutzers abrufen
                    $stmt = $pdo->prepare('SELECT * FROM servers JOIN server_members ON servers.id = server_members.server_id WHERE server_members.user_id = ?');
                    $stmt->execute([$_SESSION['user_id']]);
                    $servers = $stmt->fetchAll();
                    foreach ($servers as $server): ?>
                        <li class="list-group-item">
                            <a href="chat.php?server_id=<?= $server['id'] ?>"><?= htmlspecialchars($server['name']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-md-3">
                <h2>Channels</h2>
                <ul class="list-group">
                    <?php foreach ($channels as $channel): ?>
                        <li class="list-group-item">
                            <a href="chat.php?server_id=<?= $serverId ?>&channel_id=<?= $channel['id'] ?>"><?= htmlspecialchars($channel['name']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-md-6">
                <h2>Chat</h2>
               <div id="messages" class="border p-3" style="height: 400px; overflow-y: scroll;">
    <?php foreach ($messages as $message): ?>
        <div>
            <strong><?= htmlspecialchars($message['username']) ?>:</strong>
            <p class="message-text"><?= htmlspecialchars($message['message']) ?></p>
            <span><?= $message['created_at'] ?></span>
        </div>
    <?php endforeach; ?>
</div>
                <form method="post" class="mt-3">
                    <div class="form-group">
                        <textarea id="message" name="message" class="form-control" required placeholder="Enter your message"></textarea>
                    </div>
                    <div class="form-group">
                        <canvas id="volume-meter" width="300" height="50"></canvas>
                    </div>
                    <button type="submit" class="btn btn-primary">Send</button>
                </form>

                <h2>Create Channel</h2>
                <form method="post">
                    <div class="form-group">
                        <label for="channel_name">Channel Name</label>
                        <input type="text" class="form-control" id="channel_name" name="channel_name" required>
                    </div>
                    <input type="hidden" name="server_id" value="<?= $serverId ?>">
                    <button type="submit" class="btn btn-primary">Create Channel</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
