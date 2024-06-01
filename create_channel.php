<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $channelName = $_POST['channel_name'];
    $serverId = $_POST['server_id'];

    // Überprüfen, ob der Kanalname leer ist
    if (empty($channelName)) {
        $error = "Channel name cannot be empty.";
    } else {
        // Kanal in die Datenbank einfügen
        $stmt = $pdo->prepare('INSERT INTO channels (server_id, name) VALUES (?, ?)');
        $stmt->execute([$serverId, $channelName]);
        $success = "Channel created successfully.";
    }
}

// Server des Benutzers abrufen
$stmt = $pdo->prepare('SELECT * FROM servers JOIN server_members ON servers.id = server_members.server_id WHERE server_members.user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$servers = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Channel</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Create Channel</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="server_id">Select Server</label>
                <select class="form-control" id="server_id" name="server_id" required>
                    <?php foreach ($servers as $server): ?>
                        <option value="<?= $server['id'] ?>"><?= htmlspecialchars($server['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="channel_name">Channel Name</label>
                <input type="text" class="form-control" id="channel_name" name="channel_name" required>
            </div>
            <button type="submit" class="btn btn-primary">Create Channel</button>
        </form>
    </div>
</body>
</html>
