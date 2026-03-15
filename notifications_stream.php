<?php
// notifications_stream.php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

require_once( __DIR__  . "/__class/autoload.class.php");

session_start();

// ⚡ On récupère l'ID utilisateur connecté depuis ta session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo "event: error\n";
    echo 'data: {"error":"Utilisateur non connecté"}' . "\n\n";
    flush();
    exit;
}

$objects = new objects();

// Boucle infinie SSE
while (true) {
    $notifications = $objects->getUserNotifications($userId, 5);
    $unreadCount   = $objects->getUnreadCount($userId);

    $data = [
        "unread" => $unreadCount,
        "notifications" => $notifications
    ];

    echo "event: message\n";
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();

    // Pause 5s avant le prochain push
    sleep(5);
}
