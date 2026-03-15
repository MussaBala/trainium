<?php

session_start();

require_once("../__class/autoload.class.php" );

$object = new objects();

define( "ACTION", $_GET['action'] );

/*if (ACTION == "user-record" ){
    $res = $object->createNewUser($_POST);
    echo json_encode($res);
}*/

if (ACTION == "user-login" ){
    $res = $object->userConnexion($_POST);
    echo json_encode($res);
}

if (ACTION == "register-form") {
    $res = $object->accountRegister($_POST);
    echo json_encode($res);
}

if (ACTION == "reset-password") {
    $res = $object->resetPassword($_POST);
    echo json_encode($res);
}

if (ACTION == "forgot-password") {
    $res = $object->forgotenPassword($_POST);
    echo json_encode($res);
}
