<?php

$role = $_SESSION['user']['role'];


switch ($role) {
    case 'SYSADMIN':
        include_once('sysadmin_dash.php');
        break;
    
    case 'ADMIN':
        include_once('sysadmin_dash.php');
        break;

    case 'FORM':
        include_once('formateur_dash.php');
        break;
    
}