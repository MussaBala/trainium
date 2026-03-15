<?php
class group_content_users
{
    public $dbh;

    protected $id;
    protected $UID;
    protected $group_id;
    protected $date_creation;

    public function __construct()
    {
        $this->dbh = database::sharedInstance();
    }

    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; return $this; }

    public function getUID() { return $this->UID; }
    public function setUID($value) { $this->UID = $value; return $this; }

    public function getGroupId() { return $this->group_id; }
    public function setGroupId($value) { $this->group_id = $value; return $this; }

    public function getDateCreation() { return $this->date_creation; }
    public function setDateCreation($value) { $this->date_creation = $value; return $this; }

    public function create()
    {
        $sets = [];
        if (!is_null($this->UID)) {
            $sets[] = "`UID` = '$this->UID'";
        }
        if (!is_null($this->group_id)) {
            $sets[] = "`group_id` = '$this->group_id'";
        }
        if (!is_null($this->date_creation)) {
            $sets[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($sets)) return false;
        $sql = "INSERT INTO `group_content_users` SET " . implode(', ', $sets);
        return $this->dbh->exec($sql);
    }

    public function read()
    {
        return $this->dbh->query("SELECT * FROM `group_content_users` WHERE `id` = '$this->id'", 3);
    }

    public function readByRole()
    {
        return $this->dbh->query("SELECT * FROM `group_content_users` WHERE `group_id` = '$this->group_id'", 2);
    }

    public function readByUser()
    {
        return $this->dbh->query(
            "SELECT *
                FROM `group_content_users` AS gc
                JOIN `groupes` AS g ON gc.group_id = g.id
                WHERE gc.UID = '$this->UID';
            ", 
        3);
    }
    

    public function update()
    {
        $updates = [];
        if (!is_null($this->UID)) {
            $updates[] = "`UID` = '$this->UID'";
        }
        if (!is_null($this->group_id)) {
            $updates[] = "`group_id` = '$this->group_id'";
        }
        if (!is_null($this->date_creation)) {
            $updates[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($updates)) return false;
        $sql = "UPDATE `group_content_users` SET " . implode(', ', $updates) . " WHERE `id` = '$this->id'";
        return $this->dbh->exec($sql);
    }

    public function delete()
    {
        return $this->dbh->exec("DELETE FROM `group_content_users` WHERE `id` = '$this->id'");
    }
}
