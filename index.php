<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
session_start();

if ( !(isset($_SESSION)) || empty($_SESSION) ) {
    header('Refresh: 0; URL =./auth/login.php');
}

define('APP_STARTED', true);
define ('USER', $_SESSION['user']);
define ('ACCOUNT', $_SESSION['account']);

require_once __DIR__ . '/config/config.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/__class/autoload.class.php");

$page = $_GET['page'] ?? 'main';


ob_start();
switch($page){
    case 'dashboard':
        require './main/dashboard.php';
        break;

    case 'sendNotif':
        require './main/send_notif.php';
        break;

    case 'list-user':
        require './users/list_user.php';
        break;

    case 'user-detail':
    case 'my-profile':
        require './users/user_details.php';
        break;

    case 'edit-user':
        require './users/edit_profile.php';
        break;

    case 'add-user':
        require './users/add_user.php';
        break;

    case 'forgot-password':
        require './auth/recup_password.php';
        break;

    case 'add-course':
        require './courses/add_course.php';
        break;

    case 'this-course':
        require './courses/course_details.php';
        break;

    case 'edit-course':
        require './courses/course_edit.php';
        break;

    case 'my-courses':
        require './courses/my_courses.php';
        break;

    case 'courses-list':
        require './courses/courses_list.php';
        break;

    case 'all-notifications':
        require './api/notifications.php';
        break;

    default:
        require './main/dashboard.php';
        break;

}
$content = ob_get_clean();
require './assets/default.php';
