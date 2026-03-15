<?php

class app_tokens{
    public $dbh;

    protected $id;
    protected $token;
    protected $type;
    protected $entity_id;
    protected $entity_table;
    protected $expires_at;
    protected $used;
    protected $created_at;
    protected $created_by;
    protected $date_creation;

    public function __construct()
    {
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
     * Get the value of type
     */ 
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return  self
     */ 
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of entity_id
     */ 
    public function getEntityId()
    {
        return $this->entity_id;
    }

    /**
     * Set the value of entity_id
     *
     * @return  self
     */ 
    public function setEntityId($entity_id)
    {
        $this->entity_id = $entity_id;

        return $this;
    }

    /**
     * Get the value of entity_table
     */ 
    public function getEntityTable()
    {
        return $this->entity_table;
    }

    /**
     * Set the value of entity_table
     *
     * @return  self
     */ 
    public function setEntityTable($entity_table)
    {
        $this->entity_table = $entity_table;

        return $this;
    }

    /**
     * Get the value of expires_at
     */ 
    public function getExpiresAt()
    {
        return $this->expires_at;
    }

    /**
     * Set the value of expires_at
     *
     * @return  self
     */ 
    public function setExpiresAt($expires_at)
    {
        $this->expires_at = $expires_at;

        return $this;
    }

    /**
     * Get the value of used
     */ 
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * Set the value of used
     *
     * @return  self
     */ 
    public function setUsed($used)
    {
        $this->used = $used;

        return $this;
    }

    /**
     * Get the value of created_at
     */ 
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     *
     * @return  self
     */ 
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Get the value of created_by
     */ 
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * Set the value of created_by
     *
     * @return  self
     */ 
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;

        return $this;
    }

    /**
     * Get the value of date_creation
     */ 
    public function getDate_creation()
    {
        return $this->date_creation;
    }

    /**
     * Set the value of date_creation
     *
     * @return  self
     */ 
    public function setDate_creation($date_creation)
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function create() {
        $sql = "INSERT INTO `app_tokens`
                SET `token` = '$this->token',
                    `type` = '$this->type',
                    `entity_id` = '$this->entity_id',
                    `entity_table` = '$this->entity_table',
                    `expires_at` = '$this->expires_at',
                    `created_by` = '$this->created_by'
        ";
        $this->dbh->insert($sql);
        return $this->token;
    }

    public function validate() {
        $sql = "SELECT * 
                FROM `app_tokens` 
                WHERE `token` = '$this->token' AND type = '$this->type' 
                AND `used` = 0 AND `expires_at` >= NOW() 
                LIMIT 1";
        return $this->dbh->query($sql, 3);
    }

    public function read(){
        $sql = "SELECT * 
                FROM `app_tokens` 
                WHERE `token` = '$this->token' AND type = '$this->type' 
                LIMIT 1";
        return $this->dbh->query($sql, 3);
    }

    public function markUsed($token) {
        $sql = "UPDATE `app_tokens` 
                SET `used` = 1 
                WHERE `token` = '$this->token'
        ";
        return $this->dbh->exec($sql);
    }
}