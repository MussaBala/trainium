<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../__class/autoload.class.php';

$raw = file_get_contents("php://input");
$subscription = json_decode($raw, true);

if (!$subscription) {
    file_put_contents("php://stderr", "❌ Subscription vide: $raw\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Abonnement invalide']);
    exit;
}

$userId = intval($_SESSION['user']['id'] ?? 0);
if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

$subJson = $db->protect_entry(json_encode($subscription)); // ⚠️ échappe bien la chaîne !

$sql = "INSERT INTO push_subscriptions (user_id, subscription_json) 
        VALUES ('$userId', '$subJson')
        ON DUPLICATE KEY UPDATE subscription_json = '$subJson'";

$res = $db->exec($sql);

echo json_encode(['success' => (bool)$res]);
