<?php
require 'db.php';

$channelId = isset($_GET['channel_id']) ? $_GET['channel_id'] : null;

if ($channelId) {
    $stmt = $pdo->prepare('SELECT messages.*, users.username FROM messages JOIN users ON messages.user_id = users.id WHERE channel_id = ? ORDER BY messages.created_at DESC');
    $stmt->execute([$channelId]);
    $messages = $stmt->fetchAll();

    foreach ($messages as $message) {
        echo "<div>
                <strong>" . htmlspecialchars($message['username']) . ":</strong>
                <p>" . htmlspecialchars($message['message']) . "</p>
                <span>" . $message['created_at'] . "</span>
              </div>";
    }
}
?>
