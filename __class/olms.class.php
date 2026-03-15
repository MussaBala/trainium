<?php
class olms
{
    public $dbh;

    protected $id;
    protected $code;
    protected $nom;
    protected $pays;
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

    public function getNom() { return $this->nom; }
    public function setNom($value) { $this->nom = $value; return $this; }

    public function getPays() { return $this->pays; }
    public function setPays($value) { $this->pays = $value; return $this; }

    public function getDateCreation() { return $this->date_creation; }
    public function setDateCreation($value) { $this->date_creation = $value; return $this; }

    /* ===================== CRUD METHODS ===================== */

    public function create()
    {
        $sets = [];
        if (!is_null($this->code))     $sets[] = "`code` = '" . addslashes($this->code) . "'";
        if (!is_null($this->nom))  $sets[] = "`nom` = '" . addslashes($this->nom) . "'";
        if (!is_null($this->pays))     $sets[] = "`pays` = '" . addslashes($this->pays) . "'";
        if (empty($sets)) return false;

        $sql = "INSERT INTO `olms` SET " . implode(', ', $sets);
        return $this->dbh->insert($sql);
    }

    public function read($id)
    {
        return $this->dbh->query(
            "SELECT * FROM `olms` WHERE `id` = '" . intval($id) . "'",
            3
        );
    }

    public function readAll()
    {
        return $this->dbh->query(
            "SELECT * FROM `olms` ORDER BY code ASC",
            2
        );
    }

    public function readAllNationalOlms(){
        $pays = $this->dbh->protect_entry("Côte d'ivoire");
        return $this->dbh->query(
            "SELECT * FROM `olms` WHERE `pays` = '$pays' ORDER BY code ASC",
            2
        );
    }

    public function readByCode()
    {
        return $this->dbh->query(
            "SELECT * FROM `olms` WHERE `code` = '" . addslashes($this->code) . "'",
            3
        );
    }

    public function update()
    {
        $updates = [];
        if (!is_null($this->code))     $updates[] = "`code` = '" . addslashes($this->code) . "'";
        if (!is_null($this->nom))  $updates[] = "`nom` = '" . addslashes($this->nom) . "'";
        if (!is_null($this->pays))     $updates[] = "`pays` = '" . addslashes($this->pays) . "'";
        if (empty($updates)) return false;

        $sql = "UPDATE `olms` SET " . implode(', ', $updates) .
               " WHERE `id` = '" . intval($this->id) . "'";
        return $this->dbh->exec($sql);
    }

    public function delete()
    {
        return $this->dbh->exec(
            "DELETE FROM `olms` WHERE `id` = '" . intval($this->id) . "'"
        );
    }

    /* ===================== BUSINESS HELPERS ===================== */

    // Pour alimenter un <select>
    public function getOptionsForSelect()
    {
        $rows = $this->readAll();
        $options = [];
        foreach ($rows as $r) {
            $label = $r['nom'];
            if (!empty($r['pays'])) $label .= ' - ' . $r['pays'];
            $options[$r['id']] = $label;
        }
        return $options;
    }
}
