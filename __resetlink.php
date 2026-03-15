<?php
if (!defined('APP_STARTED')) define('APP_STARTED', true);
require_once(__DIR__ . '/__class/autoload.class.php');
require_once(__DIR__ . '/config/config.php');

error_reporting(E_ALL);
 ini_set("display_errors", 1);

$obj      = new objects();
$pr       = new password_resets();
$userObj  = new user();
$acc      = new accounts();
$utils    = new utils();

// Log file setup
$logDir  = __DIR__ . '/__logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/relance_' . date('Y-m-d') . '.log';
$log     = fopen($logFile, 'a');

function logMsg($msg) {
    global $log;
    echo $msg . "\n";
    fwrite($log, "[" . date('Y-m-d H:i:s') . "] $msg\n");
}

logMsg("=== RELANCE VALIDATION TRAINIUM ===");
logMsg("Début : " . date('Y-m-d H:i:s') . "\n");

// Récupération des comptes concernés
$records = $obj->getExpiredUnvalidatedAccounts();

if (empty($records)) {
    logMsg("🔕 Aucun compte à relancer.");
    fclose($log);
    exit;
}

foreach ($records as $record) {
    $userId  = $record['user_id'];
    $email   = trim($record['email']);
    $login   = trim($record['login']);
    $account = $obj->getAccountByUID($userId);

    $prenom = htmlspecialchars($account['prenom']);
    $nom    = htmlspecialchars($account['nom']);

    logMsg("📨 Traitement : $prenom $nom <$email>");

    // 1. Génération du token
    $token     = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));

    // 2. Invalider l'ancien token
    if (!$obj->invalidateOldToken($userId)) {
        logMsg("❌ Échec : Invalidation de l'ancien token.");
        continue;
    }

    // 3. Enregistrement du nouveau token
    $pr->setUserId($userId);
    $pr->setToken($token);
    $pr->setExpiresAt($expiresAt);
    if (!$pr->create()) {
        logMsg("❌ Échec : Insertion du nouveau token.");
        continue;
    }

    // 4. Préparation de l'email
    $link    = BASE_URL . "reset-password.php?token=$token";
    $subject = 'INF TRAINERS | Définir votre mot de passe';
    $message = <<<HTML
                Bonjour <strong>$prenom $nom</strong>,<br><br>
                Votre inscription a bien été enregistrée. Toutefois, votre précédent lien d'activation avait expiré.<br><br>
                <a href="$link" target="_blank">Cliquez ici pour définir votre mot de passe</a><br><br>
                Ce lien est valable 24h.<br><br>
                Merci pour votre engagement à la JCI Côte d'Ivoire.<br><br>
                <strong>INF JCI-CI</strong>
                HTML;

    $sent = $utils->sendSMTPMail($email, $subject, $message);

    if ($sent) {
        logMsg("✅ Email envoyé avec succès.");
    } else {
        logMsg("❌ Échec : envoi email.");
    }

    usleep(300000); // 0.3 seconde entre chaque envoi
}

logMsg("\n✅ Fin de la relance à " . date('H:i:s') . ".");
fclose($log);
