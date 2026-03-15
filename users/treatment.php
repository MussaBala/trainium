<?php

session_start();

require_once("../__class/autoload.class.php" );

$object = new objects();

define( "ACTION", $_GET['action'] );

/*if (ACTION == "user-record" ){
    $res = $object->createNewUser($_POST);
    echo json_encode($res);
}*/

if (ACTION == "account-validation" ){
    $res = $object->accountValidation($_POST);
    echo json_encode($res);
}

if (ACTION == "update-account" ){
    $res = $object->updateAccount($_FILES, $_POST);
    echo json_encode($res);
}

if (ACTION == "update-login" ){
    $res = $object->updateLoginCredentials($_POST);
    echo json_encode($res);
}

