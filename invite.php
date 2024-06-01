<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Generiere einen neuen Invite-Code für den aktuellen Server
if (isset($_GET['action']) && $_GET['action'] === 'generate_invite') {
    $serverId = isset($_GET['server_id']) ? $_GET['server_id'] : null;
    if ($serverId) {
        // Generiere einen eindeutigen Invite-Code
        $inviteCode = generateInviteCode();

        // Füge den Invite-Code in die Datenbank ein
        $stmt = $pdo->prepare('INSERT INTO invites (server_id, code) VALUES (?, ?)');
        $stmt->execute([$serverId, $inviteCode]);
    }

    // Weiterleitung zur Serverseite
    header("Location: chat.php?server_id=$serverId");
    exit();
}

// Funktion zum Generieren eines eindeutigen Invite-Codes
function generateInviteCode() {
    // Generiere einen zufälligen Code
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $codeLength = 8;
    $code = '';
    for ($i = 0; $i < $codeLength; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}
?>

<!-- In deiner HTML-Struktur -->
<!-- Zeige den Invite-Link für jeden Server an -->
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
            <?php
            // Prüfe, ob der Benutzer der Serverbesitzer ist
            if ($_SESSION['user_id'] === $server['owner_id']): ?>
                <a href="chat.php?action=generate_invite&server_id=<?= $server['id'] ?>" class="btn btn-primary btn-sm">Generate Invite</a>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
