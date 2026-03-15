<?php

define('APP_STARTED', true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/__class/autoload.class.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/config/config.php');
session_start();


$logger = new activities_log();
$logger->log([
    'user_id' => $_SESSION['user']['id'] ?? null,
    'type' => 'USER_ACTION',
    'label' => 'Deconnexion utilisateur',
    'target' => 'users',
    'message' => 'Déconnexion réussie avec email ' . $_SESSION['user']['email'],
]);

session_destroy();

//echo '<div class="well">Vous avez fermer votre session</div>';
header('Refresh: 0; URL =./login.php');
?>