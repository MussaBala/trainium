<?php

class evaluation_submissions{
    public $dbh;
    protected $id;
    protected $id_cours;
    protected $userId;
    protected $token;
    protected $notes_json;
    protected $note_globale;
    protected $points_forts;
    protected $points_ameliorations;
    protected $ip_address;
    protected $user_agent;
    protected $hash_unique;
    protected $date_soumission;

    public function __construct(){
        $this->dbh = database::sharedInstance();
    }

    


    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

        /**
     * Get the value of userId
     */ 
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the value of userId
     *
     * @return  self
     */ 
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get the value of id_cours
     */ 
    public function getId_cours()
    {
        return $this->id_cours;
    }

    /**
     * Set the value of id_cours
     *
     * @return  self
     */ 
    public function setId_cours($id_cours)
    {
        $this->id_cours = $id_cours;

        return $this;
    }

    /**
     * Get the value of token
     */ 
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the value of token
     *
     * @return  self
     */ 
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get the value of notes_json
     */ 
    public function getNotes_json()
    {
        return $this->notes_json;
    }

    /**
     * Set the value of notes_json
     *
     * @return  self
     */ 
    public function setNotes_json($notes_json)
    {
        $this->notes_json = $notes_json;

        return $this;
    }

    /**
     * Get the value of note_globale
     */ 
    public function getNote_globale()
    {
        return $this->note_globale;
    }

    /**
     * Set the value of note_globale
     *
     * @return  self
     */ 
    public function setNote_globale($note_globale)
    {
        $this->note_globale = $note_globale;

        return $this;
    }

    /**
     * Get the value of points_forts
     */ 
    public function getPoints_forts()
    {
        return $this->points_forts;
    }

    /**
     * Set the value of points_forts
     *
     * @return  self
     */ 
    public function setPoints_forts($points_forts)
    {
        $this->points_forts = $points_forts;

        return $this;
    }

    /**
     * Get the value of points_ameliorations
     */ 
    public function getPoints_ameliorations()
    {
        return $this->points_ameliorations;
    }

    /**
     * Set the value of points_ameliorations
     *
     * @return  self
     */ 
    public function setPoints_ameliorations($points_ameliorations)
    {
        $this->points_ameliorations = $points_ameliorations;

        return $this;
    }

    /**
     * Get the value of ip_address
     */ 
    public function getIp_address()
    {
        return $this->ip_address;
    }

    /**
     * Set the value of ip_address
     *
     * @return  self
     */ 
    public function setIp_address($ip_address)
    {
        $this->ip_address = $ip_address;

        return $this;
    }

    /**
     * Get the value of user_agent
     */ 
    public function getUser_agent()
    {
        return $this->user_agent;
    }

    /**
     * Set the value of user_agent
     *
     * @return  self
     */ 
    public function setUser_agent($user_agent)
    {
        $this->user_agent = $user_agent;

        return $this;
    }

    /**
     * Get the value of hash_unique
     */ 
    public function getHash_unique()
    {
        return $this->hash_unique;
    }

    /**
     * Set the value of hash_unique
     *
     * @return  self
     */ 
    public function setHash_unique($hash_unique)
    {
        $this->hash_unique = $hash_unique;

        return $this;
    }

    /**
     * Get the value of date_soumission
     */ 
    public function getDate_soumission()
    {
        return $this->date_soumission;
    }

    /**
     * Set the value of date_soumission
     *
     * @return  self
     */ 
    public function setDate_soumission($date_soumission)
    {
        $this->date_soumission = $date_soumission;

        return $this;
    }

    public function create()
    {
        $sets = [];
        if (!is_null($this->id_cours)) {
            $sets[] = "`id_cours` = '$this->id_cours'";
        }
        if (!is_null($this->userId)) {
            $sets[] = "`user_id` = '$this->userId'";
        }
        if (!is_null($this->token)) {
            $sets[] = "`token` = '$this->token'";
        }
        if (!is_null($this->notes_json)) {
            $sets[] = "`notes_json` = '$this->notes_json'";
        }
        if (!is_null($this->note_globale)) {
            $sets[] = "`note_globale` = '$this->note_globale'";
        }
        if (!is_null($this->points_forts)) {
            $sets[] = "`points_forts` = '$this->points_forts'";
        }
        if (!is_null($this->points_ameliorations)) {
            $sets[] = "`points_ameliorations` = '$this->points_ameliorations'";
        }
        if (!is_null($this->ip_address)) {
            $sets[] = "`ip_address` = '$this->ip_address'";
        }
        if (!is_null($this->user_agent)) {
            $sets[] = "`user_agent` = '$this->user_agent'";
        }
        if (!is_null($this->hash_unique)) {
            $sets[] = "`hash_unique` = '$this->hash_unique'";
        }
        if (!is_null($this->date_soumission)) {
            $sets[] = "`date_soumission` = '$this->date_soumission'";
        }
        if (empty($sets)) return false;
        $sql = "INSERT INTO `evaluation_submissions` SET " . implode(', ', $sets);
        return $this->dbh->insert($sql);
    }


    public function read($id_cours = null){
        if (is_null($id_cours)) {
            return $this->dbh->query("SELECT * FROM `evaluation_submissions`", 2);
        } else {
            return $this->dbh->query("SELECT * FROM `evaluation_submissions` WHERE `id_cours` = '$id_cours'", 2);
        }
    }

    public function readEvalByAssist(){
        return $this->dbh->query("SELECT * FROM  `evaluation_submissions` WHERE `user_id` = '$this->userId'", 3);
    }

    public function getHash(){
        return $this->dbh->query(" SELECT *
                    FROM `evaluation_submissions`
                    WHERE `hash_unique` = '$this->hash_unique'
        ", 3);
    }


    public function update(){
        $updates = [];
        if (!is_null($this->id_cours)) {
            $updates[] = "`id_cours` = '$this->id_cours'";
        }
        if (!is_null($this->userId)) {
            $updates[] = "`user_id` = '$this->userId'";
        }
        if (!is_null($this->token)) {
            $updates[] = "`token` = '$this->token'";
        }
        if (!is_null($this->notes_json)) {
            $updates[] = "`notes_json` = '$this->notes_json'";
        }
        if (!is_null($this->note_globale)) {
            $updates[] = "`note_globale` = '$this->note_globale'";
        }
        if (!is_null($this->points_forts)) {
            $updates[] = "`points_forts` = '$this->points_forts'";
        }
        if (!is_null($this->points_ameliorations)) {
            $updates[] = "`points_ameliorations` = '$this->points_ameliorations'";
        }
        if (!is_null($this->ip_address)) {
            $updates[] = "`ip_address` = '$this->ip_address'";
        }
        if (!is_null($this->user_agent)) {
            $updates[] = "`user_agent` = '$this->user_agent'";
        }
        if (!is_null($this->hash_unique)) {
            $updates[] = "`hash_unique` = '$this->hash_unique'";
        }
        if (!is_null($this->date_soumission)) {
            $updates[] = "`date_soumission` = '$this->date_soumission'";
        }
        if (empty($updates)) return false;
        $sql = "UPDATE `evaluation_submissions` SET " . implode(', ', $updates) . " WHERE `id_cours` = '$this->id_cours'";
        return $this->dbh->exec($sql);
    }

}
