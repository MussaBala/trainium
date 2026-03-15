<?php

session_start();

require_once("../__class/autoload.class.php" );

$object = new objects();

define( "ACTION", $_GET['action'] );

if (ACTION == "send-notif" ){
    $res = $object->adminSendNotifications($_POST);
    echo json_encode($res);
}

