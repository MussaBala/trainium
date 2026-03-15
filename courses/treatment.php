<?php

session_start();

require_once("../__class/autoload.class.php" );

$object = new objects();

define( "ACTION", $_GET['action'] );

if (ACTION == "add-course" ){
    $res = $object->createNewCourse($_FILES, $_POST);
    echo json_encode($res);
}

if (ACTION == "validate-cours") {
    $res = $object->courseValidation($_POST);
    echo json_encode($res);
}

if (ACTION == "edit-cours") {
    $res = $object->courseEdition($_FILES, $_POST);
    echo json_encode($res);
}

if (ACTION == "delete-file") {
    $res = $object->courseFileDelete($_POST['file_id']);
    echo json_encode($res);
}

if (ACTION == "generate-qr") {
    $coursId = intval($_POST['cours_id'] ?? 0);
    if (!$coursId) {
        echo json_encode(['success' => false, 'message' => 'Cours ID manquant']);
        exit;
    }
    $res = $object->generateCoursQrCode($coursId);
    echo json_encode($res);
}

if (ACTION == "get-photos") {
    $coursId = intval($_GET['cours_id'] ?? 0);
    $page = intval($_GET['page'] ?? 1);
    $limit = intval($_GET['limit'] ?? 8);
    $offset = ($page - 1) * $limit;

    if (!$coursId) {
        echo json_encode(['success' => false, 'message' => 'Cours ID manquant']);
        exit;
    }
    $res = $object->getPhotosByCours($coursId, $limit, $offset);
    echo json_encode($res);
}

if (ACTION == "add-files") {
    $res = $object->addFiles($_FILES, $_POST);
    echo json_encode($res);
}

if (ACTION == "eval-course") {
    $res = $object->courseEvaluation($_POST);
    echo json_encode($res);
}

if (ACTION == "get-evaluation-results") {
    $res = $object->courseEvaluationResults($id, $limit, $offset);
    echo json_encode($res);
}

if (ACTION == "validate-courseval") {
    $res = $object->validateCourseEvaluation($_POST);
    echo json_encode($res);
}
