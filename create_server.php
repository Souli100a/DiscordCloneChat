<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $serverName = $_POST['server_name'];

    // Server erstellen
    $stmt = $pdo->prepare('INSERT INTO servers (name, owner_id) VALUES (?, ?)');
    $stmt->execute([$serverName, $_SESSION['user_id']]);
    $serverId = $pdo->lastInsertId();

    // Den Ersteller als Mitglied hinzufügen
    $stmt = $pdo->prepare('INSERT INTO server_members (server_id, user_id) VALUES (?, ?)');
    $stmt->execute([$serverId, $_SESSION['user_id']]);

    // Standardkanal erstellen
    $stmt = $pdo->prepare('INSERT INTO channels (name, server_id) VALUES (?, ?)');
    $stmt->execute(['general', $serverId]);

    header('Location: chat.php?server_id=' . $serverId);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Server</title>
</head>
<body>
    <form method="post">
        <input type="text" name="server_name" required placeholder="Server Name">
        <button type="submit">Create Server</button>
    </form>
</body>
</html>
