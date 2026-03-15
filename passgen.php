<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/__class/autoload.class.php");

$obj = new objects();

$pass = $obj->generateAndHashPassword();

var_dump($pass);