<?php
class grades
{
    public $dbh;

    protected $id;
    protected $id_gradeCategory;
    protected $code;
    protected $libelle;
    protected $description;
    protected $date_creation;

    public function __construct()
    {
        $this->dbh = database::sharedInstance();
    }

    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; return $this; }

    public function getCode() { return $this->code; }
    public function setCode($value) { $this->code = $value; return $this; }

    public function getLibelle() { return $this->libelle; }
    public function setLibelle($value) { $this->libelle = $value; return $this; }

    public function getDescription() { return $this->description; }
    public function setDescription($value) { $this->description = $value; return $this; }

    public function getIdGradeCategory() { return $this->id_gradeCategory; }
    public function setIdGradeCategory($value) { $this->id_gradeCategory = $value; return $this; }

    public function getDateCreation() { return $this->date_creation; }
    public function setDateCreation($value) { $this->date_creation = $value; return $this; }

    public function create()
    {
        $sets = [];
        if (!is_null($this->code)) {
            $sets[] = "`code` = '$this->code'";
        }
        if (!is_null($this->libelle)) {
            $sets[] = "`libelle` = '$this->libelle'";
        }
        if (!is_null($this->description)) {
            $sets[] = "`description` = '$this->description'";
        }
        if (!is_null($this->id_gradeCategory)) {
            $sets[] = "`id_gradeCategory` = '$this->id_gradeCategory'";
        }
        if (!is_null($this->date_creation)) {
            $sets[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($sets)) return false;
        $sql = "INSERT INTO `grades` SET " . implode(', ', $sets);
        return $this->dbh->exec($sql);
    }

    public function read($id)
    {
        return $this->dbh->query("SELECT * FROM `grades` WHERE `id` = '$id'", 3);
    }

    public function readAll(){
        return $this->dbh->query("SELECT * FROM `grades`", 2);
    }

    public function readGradeByCode(){
        return $this->dbh->query("SELECT * FROM `grades` WHERE `code` = '$this->code'", 3);
    }

    public function update()
    {
        $updates = [];
        if (!is_null($this->code)) {
            $updates[] = "`code` = '$this->code'";
        }
        if (!is_null($this->libelle)) {
            $updates[] = "`libelle` = '$this->libelle'";
        }
        if (!is_null($this->description)) {
            $updates[] = "`description` = '$this->description'";
        }
        if (!is_null($this->id_gradeCategory)) {
            $updates[] = "`id_gradeCategory` = '$this->id_gradeCategory'";
        }
        if (!is_null($this->date_creation)) {
            $updates[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($updates)) return false;
        $sql = "UPDATE `grades` SET " . implode(', ', $updates) . " WHERE `id` = '$this->id'";
        return $this->dbh->exec($sql);
    }

    public function delete()
    {
        return $this->dbh->exec("DELETE FROM `grades` WHERE `id` = '$this->id'");
    }
}
