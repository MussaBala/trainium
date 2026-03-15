<?php

class activities_log
{
    public $dbh;

    public function __construct()
    {
        $this->dbh = database::sharedInstance();
    }


    public function log(array $data): bool{
        $sets = [];

        // Traitement des données
        $UID        = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $type       = strtoupper($data['type'] ?? 'INFO');
        $label      = $data['label'] ?? 'Action';
        $target     = $data['target'] ?? 'unknown';
        $message    = $data['message'] ?? null;
        $ip         = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $agent      = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $page       = $_SERVER['REQUEST_URI'] ?? 'unknown';
        $method     = $_SERVER['REQUEST_METHOD'] ?? 'unknown';

        if (!is_null($UID)) {
            $sets[] = "`user_id` = $UID";
        }

        $sets[] = "`action_type` = '" . $this->dbh->protect_entry($type) . "'";
        $sets[] = "`action_label` = '" . $this->dbh->protect_entry($label) . "'";
        $sets[] = "`action_target` = '" . $this->dbh->protect_entry($target) . "'";
        $sets[] = "`message` = '" . $this->dbh->protect_entry($message) . "'";
        $sets[] = "`ip_address` = '" . $this->dbh->protect_entry($ip) . "'";
        $sets[] = "`user_agent` = '" . $this->dbh->protect_entry($agent) . "'";
        $sets[] = "`page_url` = '" . $this->dbh->protect_entry($page) . "'";
        $sets[] = "`http_method` = '" . $this->dbh->protect_entry($method) . "'";
        $sets[] = "`created_at` = NOW()";

        // Génération de la requête
        $sql = "INSERT INTO `activities_log` SET " . implode(', ', $sets);
        return $this->dbh->insert($sql) > 0;
    }

    public function readLogByUser($UID){
        $userId = (int) $this->dbh->protect_entry($UID);

        $sql = "SELECT * FROM activities_log 
                WHERE user_id = '$userId'
                ORDER BY created_at DESC";

        return $this->dbh->query($sql, 2);
    }

    public function getRecentLogs($limit = 10) {
        $sql = "SELECT a.*, u.email AS user_email 
                FROM activities_log a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.created_at DESC
                LIMIT = '$limit'";
        $stmt = $this->dbh->query($sql, 2);
        return $stmt;
    }

}
