<?php
class conditions_grade
{
    public $dbh;

    protected $id;
    protected $id_grade;
    protected $critere;
    protected $obligatoire;
    protected $date_creation;

    public function __construct()
    {
        $this->dbh = database::sharedInstance();
    }

    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; return $this; }

    public function getIdGrade() { return $this->id_grade; }
    public function setIdGrade($value) { $this->id_grade = $value; return $this; }

    public function getCritere() { return $this->critere; }
    public function setCritere($value) { $this->critere = $value; return $this; }

    public function getObligatoire() { return $this->obligatoire; }
    public function setObligatoire($value) { $this->obligatoire = $value; return $this; }

    public function getDateCreation() { return $this->date_creation; }
    public function setDateCreation($value) { $this->date_creation = $value; return $this; }

    public function create()
    {
        $sets = [];
        if (!is_null($this->id_grade)) {
            $sets[] = "`id_grade` = '$this->id_grade'";
        }
        if (!is_null($this->critere)) {
            $sets[] = "`critere` = '$this->critere'";
        }
        if (!is_null($this->obligatoire)) {
            $sets[] = "`obligatoire` = '$this->obligatoire'";
        }
        if (!is_null($this->date_creation)) {
            $sets[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($sets)) return false;
        $sql = "INSERT INTO `conditions_grade` SET " . implode(', ', $sets);
        return $this->dbh->exec($sql);
    }

    public function read($id)
    {
        return $this->dbh->query("SELECT * FROM `conditions_grade` WHERE `id` = '$id'", 3);
    }

    public function update()
    {
        $updates = [];
        if (!is_null($this->id_grade)) {
            $updates[] = "`id_grade` = '$this->id_grade'";
        }
        if (!is_null($this->critere)) {
            $updates[] = "`critere` = '$this->critere'";
        }
        if (!is_null($this->obligatoire)) {
            $updates[] = "`obligatoire` = '$this->obligatoire'";
        }
        if (!is_null($this->date_creation)) {
            $updates[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($updates)) return false;
        $sql = "UPDATE `conditions_grade` SET " . implode(', ', $updates) . " WHERE `id` = '$this->id'";
        return $this->dbh->exec($sql);
    }

    public function delete()
    {
        return $this->dbh->exec("DELETE FROM `conditions_grade` WHERE `id` = '$this->id'");
    }
}
