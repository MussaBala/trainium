<?php
class destinataires_sms
{
    public $dbh;

    protected $id_sms;
    protected $UID;
    protected $date_creation;

    public function __construct()
    {
        $this->dbh = database::sharedInstance();
    }

    public function getIdSms() { return $this->id_sms; }
    public function setIdSms($value) { $this->id_sms = $value; return $this; }

    public function getUID() { return $this->UID; }
    public function setUID($value) { $this->UID = $value; return $this; }

    public function getDateCreation() { return $this->date_creation; }
    public function setDateCreation($value) { $this->date_creation = $value; return $this; }

    public function create()
    {
        $sets = [];
        if (!is_null($this->id_sms)) {
            $sets[] = "`id_sms` = '$this->id_sms'";
        }
        if (!is_null($this->UID)) {
            $sets[] = "`UID` = '$this->UID'";
        }
        if (!is_null($this->date_creation)) {
            $sets[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($sets)) return false;
        $sql = "INSERT INTO `destinataires_sms` SET " . implode(', ', $sets);
        return $this->dbh->exec($sql);
    }

    public function read($id)
    {
        return $this->dbh->query("SELECT * FROM `destinataires_sms` WHERE `id_sms` = '$id'", 3);
    }

    public function update()
    {
        $updates = [];
        if (!is_null($this->UID)) {
            $updates[] = "`UID` = '$this->UID'";
        }
        if (!is_null($this->date_creation)) {
            $updates[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($updates)) return false;
        $sql = "UPDATE `destinataires_sms` SET " . implode(', ', $updates) . " WHERE `id_sms` = '$this->id_sms'";
        return $this->dbh->exec($sql);
    }

    public function delete()
    {
        return $this->dbh->exec("DELETE FROM `destinataires_sms` WHERE `id_sms` = '$this->id_sms'");
    }
}
