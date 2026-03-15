<?php

class password_resets{
    public $dbh;

    private $id;
    private $user_id;
    private $token;
    private $expires_at;
    private $used;
    private $created_at;

    public function __construct()
    {
        $this->dbh = database::sharedInstance();
    }

    // ========== GETTERS ==========
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getToken() { return $this->token; }
    public function getExpiresAt() { return $this->expires_at; }
    public function getUsed() { return $this->used; }
    public function getCreatedAt() { return $this->created_at; }

    // ========== SETTERS ==========
    public function setId($id) { $this->id = $id; }
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function setToken($token) { $this->token = $token; }
    public function setExpiresAt($expires_at) { $this->expires_at = $expires_at; }
    public function setUsed($used) { $this->used = $used; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }

    // ========== Create token ==========
    public function create()
    {
        $sql = "INSERT INTO `password_resets`
                    SET `user_id` = '$this->user_id',
                        `token` = '$this->token',
                        `expires_at` = '$this->expires_at'
                ";
        return $this->dbh->insert($sql, $log=true);
        
    }

    // ========== Read by token ==========
    public function readByToken()
    {
        $sql = "SELECT * 
                FROM `password_resets` 
                WHERE `token` = '$this->token' LIMIT 1
                ";
        return $this->dbh->query($sql, 3);
    }

    public function readTokenByUID()
    {
        $sql = "SELECT * 
                FROM `password_resets` 
                WHERE `user_id` = '$this->user_id' LIMIT 1
                ";
        return $this->dbh->query($sql, 3);
    }

    // ========== Mark as used ==========
    public function markAsUsed()
    {
        $sql = "UPDATE `password_resets` SET `used` = 1 WHERE `token` = '$this->token'";
        return $this->dbh->exec($sql);
    }

    // ========== Delete expired tokens ==========
    public function deleteExpired()
    {
        $sql = "DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1";
        return $this->dbh->exec($sql);
    }

    // ========== Helper: Check if valid ==========
    public function isValidToken($token)
    {
        $sql = "SELECT * FROM password_resets 
                WHERE token = '$this->token' AND used = 0 AND expires_at > NOW() 
                LIMIT 1";
        return $this->dbh->query($sql, 3);
    }

    public function getOldLink(){
        $sql = "SELECT pr.user_id, u.email, u.login, pr.token
                FROM password_resets pr
                JOIN users u ON u.id = pr.user_id
                JOIN accounts acc ON acc.UID = u.id
                WHERE pr.used = 0
                AND pr.expires_at < NOW()
                AND acc.validate = 0";
        return $this->dbh->query($sql,2);
    }
}
