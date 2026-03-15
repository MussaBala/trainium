<?php

class cours_logs {
    public $dbh;
    protected $id;
    protected $id_cours;
    protected $user_id;
    protected $action;
    protected $details;
    protected $created_at;

    public function __construct()
    {
        $this->dbh = database::sharedInstance();
    }

    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; return $this; }

    public function getIdCours() { return $this->id_cours; }
    public function setIdCours($value) { $this->id_cours = $value; return $this; }

    public function getUserId() { return $this->user_id; }
    public function setUserId($value) { $this->user_id = $value; return $this; }

    public function getAction() { return $this->action; }
    public function setAction($value) { $this->action = $value; return $this; }

    public function getDetails() { return $this->details; }
    public function setDetails($value) { $this->details = $value; return $this; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($value) { $this->created_at = $value; return $this; }


    public function create()
    {
        $sets = [];
        if (!is_null($this->id_cours)) {
            $sets[] = "`id_cours` = '$this->id_cours'";
        }
        if (!is_null($this->user_id)) {
            $sets[] = "`user_id` = '$this->user_id'";
        }
        if (!is_null($this->action)) {
            $sets[] = "`action` = '$this->action'";
        }
        if (!is_null($this->details)) {
            $sets[] = "`details` = '$this->details'";
        }
        if (!is_null($this->created_at)) {
            $sets[] = "`created_at` = '$this->created_at'";
        }
        if (empty($sets)) return false;
        $sql = "INSERT INTO `cours_logs` SET " . implode(', ', $sets);
        return $this->dbh->insert($sql);
    }

    public function getLogsByCours() {
        $id_cours = $this->dbh->protect_entry($this->id_cours);
        $sql = "SELECT 
                    cl.*, 
                    a.nom, 
                    a.prenom, 
                    a.email
                FROM 
                    cours_logs cl
                LEFT JOIN 
                    accounts a ON cl.user_id = a.UID
                WHERE 
                    cl.id_cours = '$id_cours'
                ORDER BY 
                    cl.created_at DESC;
                ";
        return $this->dbh->query($sql, 2);
    }
}
