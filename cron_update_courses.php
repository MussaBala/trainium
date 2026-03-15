<?php
// auto_update_status.php
require_once( __DIR__  . "/__class/autoload.class.php");

$obj = new objects();
$result = $obj->autoUpdateCourseStatusByDate();

$logFile = __DIR__ . "/__log/cron_status.log";

file_put_contents(
    $logFile,
    "[" . date('Y-m-d H:i:s') . "] " . $result['message'] . PHP_EOL .
    json_encode($result['details'], JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL,
    FILE_APPEND
);
