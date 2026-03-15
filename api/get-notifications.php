<?php
session_start();
require_once __DIR__ . "/../__class/autoload.class.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

$userId = intval($_SESSION['user']['id']);
$notif  = new notifications();

$action = $_POST['action'] ?? $_GET['action'] ?? null;

try {
    switch ($action) {
        case 'fetch': // récupérer les notifications récentes
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            $list  = $notif->readAllByUser($userId, $limit);
            $count = $notif->countUnread($userId);
            echo json_encode([
                'success' => true,
                'notifications' => $list ?: [],
                'unread_count'  => $count ?: 0
            ]);
            break;

        case 'markAsRead': // marquer UNE notification comme lue
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $res = $notif->markAsRead($id);
                echo json_encode(['success' => (bool)$res, 'id' => $id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'ID invalide']);
            }
            break;

        case 'markAllRead': // marquer toutes comme lues
            $res = $notif->markAllReadByUser($userId);
            echo json_encode(['success' => (bool)$res]);
            break;

        case 'delete': // supprimer UNE notification
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $res = $notif->delete($id);
                echo json_encode(['success' => (bool)$res, 'id' => $id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'ID invalide']);
            }
            break;

        case 'countUnread': // juste le compteur
            $count = $notif->countUnread($userId);
            echo json_encode(['success' => true, 'unread_count' => $count ?: 0]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action invalide']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
