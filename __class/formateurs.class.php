<?php
class formateurs
{
    public $dbh;

    protected $id;
    protected $id_account;
    protected $matricule;
    protected $date_inscription;
    protected $date_creation;

    public function __construct()
    {
        $this->dbh = database::sharedInstance();
    }

    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; return $this; }

    public function getIdAccount() { return $this->id_account; }
    public function setIdAccount($value) { $this->id_account = $value; return $this; }

    public function getMatricule() { return $this->matricule; }
    public function setMatricule($value) { $this->matricule = $value; return $this; }

    public function getDateInscription() { return $this->date_inscription; }
    public function setDateInscription($value) { $this->date_inscription = $value; return $this; }

    public function getDateCreation() { return $this->date_creation; }
    public function setDateCreation($value) { $this->date_creation = $value; return $this; }

    public function create()
    {
        $sets = [];
        if (!is_null($this->id_account)) {
            $sets[] = "`id_account` = '$this->id_account'";
        }
        if (!is_null($this->matricule)) {
            $sets[] = "`matricule` = '$this->matricule'";
        }
        if (!is_null($this->date_inscription)) {
            $sets[] = "`date_inscription` = '$this->date_inscription'";
        }
        if (!is_null($this->date_creation)) {
            $sets[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($sets)) return false;
        $sql = "INSERT INTO `formateurs` SET " . implode(', ', $sets);
        return $this->dbh->exec($sql);
    }

    public function read($id)
    {
        return $this->dbh->query("SELECT * FROM `formateurs` WHERE `id` = '$id'", 3);
    }

    public function readByAcc()
    {
        return $this->dbh->query(
                "SELECT * 
                    FROM `formateurs` 
                    WHERE `id_account` = '$this->id_account'
                ", 
        3);
    }


    public function update()
    {
        $updates = [];
        if (!is_null($this->id_account)) {
            $updates[] = "`id_account` = '$this->id_account'";
        }
        if (!is_null($this->matricule)) {
            $updates[] = "`matricule` = '$this->matricule'";
        }
        if (!is_null($this->date_inscription)) {
            $updates[] = "`date_inscription` = '$this->date_inscription'";
        }
        if (!is_null($this->date_creation)) {
            $updates[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($updates)) return false;
        $sql = "UPDATE `formateurs` SET " . implode(', ', $updates) . " WHERE `id` = '$this->id'";
        return $this->dbh->exec($sql);
    }

    public function delete()
    {
        return $this->dbh->exec("DELETE FROM `formateurs` WHERE `id` = '$this->id'");
    }
}
