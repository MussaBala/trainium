<?php
class notifications
{
    protected $dbh;

    protected $id;
    protected $user_id;
    protected $title;
    protected $message;
    protected $url;
    protected $type;
    protected $is_read;
    protected $created_at;

    public function __construct()
    {
        $this->dbh = database::sharedInstance();
    }

    // === GETTERS & SETTERS ===
    public function getId() { return $this->id; }
    public function setId($v) { $this->id = $v; return $this; }

    public function getUserId() { return $this->user_id; }
    public function setUserId($v) { $this->user_id = $v; return $this; }

    public function getTitle() { return $this->title; }
    public function setTitle($v) { $this->title = $v; return $this; }

    public function getMessage() { return $this->message; }
    public function setMessage($v) { $this->message = $v; return $this; }

    public function getUrl() { return $this->url; }
    public function setUrl($v) { $this->url = $v; return $this; }

    public function getType() { return $this->type; }
    public function setType($v) { $this->type = $v; return $this; }

    public function getIsRead() { return $this->is_read; }
    public function setIsRead($v) { $this->is_read = $v; return $this; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($v) { $this->created_at = $v; return $this; }

    // === HELPERS ===

    /** Création rapide sans instancier setters */
    public function createForUser($userId, $title, $message, $url = '#', $type = 'INFO')
    {
        $this->setUserId($userId)
             ->setTitle($title)
             ->setMessage($message)
             ->setUrl($url)
             ->setType($type);

        return $this->create();
    }

    /** Formater un enregistrement pour l’API (JSON ready) */
    protected function formatRow($row)
    {
        return [
            'id'         => (int)$row['id'],
            'user_id'    => (int)$row['user_id'],
            'title'      => $row['title'],
            'message'    => $row['message'],
            'url'        => $row['url'],
            'type'       => $row['type'],
            'is_read'    => (int)$row['is_read'],
            'created_at' => $row['created_at'],
            'time_ago'   => $this->timeAgo($row['created_at'])
        ];
    }

    /** Convertir date → "il y a 2h" */
    protected function timeAgo($datetime)
    {
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60) return "à l’instant";
        if ($diff < 3600) return floor($diff / 60) . " min";
        if ($diff < 86400) return floor($diff / 3600) . " h";
        if ($diff < 604800) return floor($diff / 86400) . " j";
        return date("d/m/Y", $time);
    }

    // === CRUD ===

    public function create()
    {
        $title   = $this->dbh->protect_entry($this->title);
        $message = $this->dbh->protect_entry($this->message);
        $url     = $this->dbh->protect_entry($this->url ?? '#');
        $type    = $this->dbh->protect_entry($this->type ?? 'INFO');
        $userId  = intval($this->user_id);

        $sql = "INSERT INTO notifications (user_id, title, message, url, type, is_read, created_at) 
                VALUES ('$userId', '$title', '$message', '$url', '$type', 0, NOW())";
        return $this->dbh->insert($sql);
    }

    public function read($id, $userId = null)
    {
        $id = intval($id);
        $row = $this->dbh->query("SELECT * FROM notifications WHERE id = '$id'", 3);

        // sécurité : vérifier que la notif appartient au user
        if ($userId && $row && $row['user_id'] != $userId) {
            return null;
        }
        return $row ? $this->formatRow($row) : null;
    }

    public function readAllByUser($userId, $limit = 10)
    {
        $userId = intval($userId);
        $rows = $this->dbh->query(
            "SELECT * FROM notifications 
             WHERE user_id = '$userId' 
             ORDER BY is_read ASC, created_at DESC 
             LIMIT $limit", 2
        );

        if (!$rows) return [];
        return array_map([$this, 'formatRow'], $rows);
    }

    public function countUnread($userId)
    {
        $userId = intval($userId);
        $res = $this->dbh->query(
            "SELECT COUNT(*) AS nb FROM notifications 
             WHERE user_id = '$userId' AND is_read = 0",
            3
        );
        return (int)($res['nb'] ?? 0);
    }

    /** Marquer UNE notif comme lue */
    public function markAsRead($id, $userId = null)
    {
        $id = intval($id);
        if ($userId) {
            return $this->dbh->exec("UPDATE notifications SET is_read = 1 WHERE id = '$id' AND user_id = '".intval($userId)."'");
        }
        return $this->dbh->exec("UPDATE notifications SET is_read = 1 WHERE id = '$id'");
    }

    /** Marquer TOUTES les notifs d’un user comme lues */
    public function markAllReadByUser($userId)
    {
        $userId = intval($userId);
        return $this->dbh->exec("UPDATE notifications SET is_read = 1 WHERE user_id = '$userId'");
    }

    public function delete($id, $userId = null)
    {
        $id = intval($id);
        if ($userId) {
            return $this->dbh->exec("DELETE FROM notifications WHERE id = '$id' AND user_id = '".intval($userId)."'");
        }
        return $this->dbh->exec("DELETE FROM notifications WHERE id = '$id'");
    }
}
