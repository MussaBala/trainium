<?php
class notifier {
    /**
     * Envoie une notification à un ou plusieurs utilisateurs
     */
    public static function send($userIds, $title, $message, $url = "#", $type = "INFO") {
        if (!is_array($userIds)) {
            $userIds = [$userIds]; // on force en tableau
        }

        foreach ($userIds as $uid) {
            $notif = new notifications();
            $notif->setUserId($uid)
                  ->setTitle($title)
                  ->setMessage($message)
                  ->setUrl($url)
                  ->setType($type)
                  ->create();
        }
    }

    /**
     * Récupère tous les admins INF (ex: rôle = 'INF')
     */
    public static function getAllInfAdmins() {
        $user = new user(); // suppose que tu as une classe users()
        return $res = $user->readByRole(1); // doit renvoyer un array d'IDs

    }
}
