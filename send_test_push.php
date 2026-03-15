<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include_once($_SERVER['DOCUMENT_ROOT'] . '/__class/autoload.class.php');
if (!defined('APP_STARTED')) define('APP_STARTED', true);
include_once($_SERVER['DOCUMENT_ROOT'] . '/config/config.php');

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

// Charger la DB
$db = database::sharedInstance();

// Récupérer un abonnement (dernier inscrit)
$subscriptionRow = $db->query("SELECT subscription_json FROM push_subscriptions ORDER BY created_at DESC LIMIT 1", 3);
var_dump($subscriptionRow);
if (!$subscriptionRow) {
    die("❌ Aucun abonnement trouvé en BD");
}

// Décoder l'abonnement
$subData = json_decode($subscriptionRow['subscription_json'], true);
if (!$subData) {
    die("❌ Erreur de parsing JSON de la subscription");
}

$subscription = Subscription::create($subData);

// Config VAPID (tu dois avoir défini ces constantes dans config.php)
$auth = [
    'VAPID' => [
        'subject' => 'mailto:admin@trainium.pro',
        'publicKey' => VAPID_PUBLIC_KEY,
        'privateKey' => VAPID_PRIVATE_KEY,
    ],
];

$webPush = new WebPush($auth);

// Envoyer la notif
$report = $webPush->sendOneNotification(
    $subscription,
    json_encode([
        'title' => 'Test Notification 🚀',
        'body'  => 'Ceci est une notification de test envoyée depuis PHP',
        'url'   => 'https://trainium.pro/'
    ])
);

// Vérifier le résultat
foreach ($webPush->flush() as $report) {
    $endpoint = $report->getRequest()->getUri()->__toString();
    if ($report->isSuccess()) {
        echo "✅ Notification envoyée avec succès à {$endpoint}\n";
    } else {
        echo "❌ Erreur envoi à {$endpoint}: {$report->getReason()}\n";
    }
}
