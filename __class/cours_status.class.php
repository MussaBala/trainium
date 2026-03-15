<?php
class cours_status
{
    public $dbh;

    protected $id;
    protected $code;
    protected $libelle;
    protected $prev_status;
    protected $next_status;
    protected $date_creation;

    public function __construct()
    {
        $this->dbh = database::sharedInstance();
    }

    /* ===================== GETTERS & SETTERS ===================== */
    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; return $this; }

    public function getCode() { return $this->code; }
    public function setCode($value) { $this->code = $value; return $this; }

    public function getLibelle() { return $this->libelle; }
    public function setLibelle($value) { $this->libelle = $value; return $this; }

    public function getPrevStatus() { return $this->prev_status; }
    public function setPrevStatus($value) { $this->prev_status = $value; return $this; }

    public function getNextStatus() { return $this->next_status; }
    public function setNextStatus($value) { $this->next_status = $value; return $this; }

    public function getDateCreation() { return $this->date_creation; }
    public function setDateCreation($value) { $this->date_creation = $value; return $this; }

    /* ===================== CRUD METHODS ===================== */

    public function create()
    {
        $sets = [];
        if (!is_null($this->code)) {
            $sets[] = "`code` = '" . addslashes($this->code) . "'";
        }
        if (!is_null($this->libelle)) {
            $sets[] = "`libelle` = '" . addslashes($this->libelle) . "'";
        }
        if (!is_null($this->prev_status)) {
            $sets[] = "`prev_status` = '" . intval($this->prev_status) . "'";
        }
        if (!is_null($this->next_status)) {
            $sets[] = "`next_status` = '" . intval($this->next_status) . "'";
        }
        if (empty($sets)) return false;

        $sql = "INSERT INTO `cours_status` SET " . implode(', ', $sets);
        return $this->dbh->insert($sql);
    }

    public function read($id)
    {
        return $this->dbh->query("SELECT * FROM `cours_status` WHERE `id` = '" . intval($id) . "'", 3);
    }

    public function readAll()
    {
        return $this->dbh->query("SELECT * FROM `cours_status` ORDER BY id ASC", 2);
    }

    public function readByCode($code)
    {
        return $this->dbh->query("SELECT * FROM `cours_status` WHERE `code` = '" . addslashes($code) . "'", 3);
    }

    public function update()
    {
        $updates = [];
        if (!is_null($this->code)) {
            $updates[] = "`code` = '" . addslashes($this->code) . "'";
        }
        if (!is_null($this->libelle)) {
            $updates[] = "`libelle` = '" . addslashes($this->libelle) . "'";
        }
        if (!is_null($this->prev_status)) {
            $updates[] = "`prev_status` = '" . intval($this->prev_status) . "'";
        }
        if (!is_null($this->next_status)) {
            $updates[] = "`next_status` = '" . intval($this->next_status) . "'";
        }
        if (empty($updates)) return false;

        $sql = "UPDATE `cours_status` SET " . implode(', ', $updates) . " WHERE `id` = '" . intval($this->id) . "'";
        return $this->dbh->exec($sql);
    }

    public function delete()
    {
        return $this->dbh->exec("DELETE FROM `cours_status` WHERE `id` = '" . intval($this->id) . "'");
    }

    /* ===================== BUSINESS HELPERS ===================== */

    // Obtenir le statut suivant possible
    public function getNext()
    {
        if (is_null($this->next_status)) return null;
        return $this->read($this->next_status);
    }

    // Obtenir le statut précédent possible
    public function getPrev()
    {
        if (is_null($this->prev_status)) return null;
        return $this->read($this->prev_status);
    }
}
