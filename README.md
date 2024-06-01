<br>Create a Databse names Chat<br>
<br>Use the chat.sql and add the tables to your database<br>
<br>edit your database connection infos and safe <br>(Database infos here: db.php)<br>
<br>and you can use the chat!<br>
<br>You can Create Server and Channel.<br>

<br><br>
1.) Chat with Server and Channel<br>
2.) Register and Login system<br>
3.) Use the command /mute {username} can you mute a user in the chat<br>
4.) Create server and Channel for your Server<br>
5.) Create Invite key for your server and share with user<br>

<br>Commands in version 1
/mute username<br>
/news this is a test news

<br><br>
<code>
function processCommand($pdo, $userId, $message) {
    global $channelId;
    if (strpos($message, '/news') === 0) {
        // Nachricht nach dem Befehl /news fett markieren
        $newsMessage = '<b>' . htmlspecialchars(substr($message, 6)) . '</b>';
        // Nachricht in die Datenbank einfügen
        $stmt = $pdo->prepare('INSERT INTO messages (channel_id, user_id, message) VALUES (?, ?, ?)');
        $stmt->execute([$channelId, $userId, $newsMessage]);
        return 'News message sent!';
    } elseif (strpos($message, '/mute') === 0) {
        // Benutzer stummschalten
        $parts = explode(' ', $message);
        if (count($parts) > 1) {
            $usernameToMute = $parts[1];
            $stmt = $pdo->prepare('UPDATE users SET is_muted = 1 WHERE username = ?');
            $stmt->execute([$usernameToMute]);
            return $usernameToMute . ' has been muted.';
        } else {
            return 'Please specify a username to mute.';
        }
    }

    // Andere Befehle hier hinzufügen

    return 'Command not recognized.';
}
</code>
