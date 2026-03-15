<?php
class cours
{
    public $dbh;

    protected $id;
    protected $UID;
    protected $uid_assistant;
    protected $code_cours;
    protected $title;
    protected $theme;
    protected $date_cours;
    protected $olm;
    protected $lieu;
    protected $type_cours;
    protected $commentaire;
    protected $duree_heure;
    protected $qr_code;
    protected $status;
    protected $date_creation;

    public function __construct()
    {
        $this->dbh = database::sharedInstance();
    }

    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; return $this; }

    public function getUID() { return $this->UID; }
    public function setUID($value) { $this->UID = $value; return $this; }

        /**
     * Get the value of uid_assistant
     */ 
    public function getUid_assistant()
    {
        return $this->uid_assistant;
    }

    /**
     * Set the value of uid_assistant
     *
     * @return  self
     */ 
    public function setUid_assistant($uid_assistant)
    {
        $this->uid_assistant = $uid_assistant;

        return $this;
    }
    
    public function getCodeCours() { return $this->code_cours; }
    public function setCodeCours($value) { $this->code_cours = $value; return $this; }

    public function getTitle() { return $this->title; }
    public function setTitle($value) { $this->title = $value; return $this; }

    public function getTheme() { return $this->theme; }
    public function setTheme($value) { $this->theme = $value; return $this; }

    public function getDateCours() { return $this->date_cours; }
    public function setDateCours($value) { $this->date_cours = $value; return $this; }

    public function getOlm() { return $this->olm; }
    public function setOlm($value) { $this->olm = $value; return $this; }

    public function getLieu() { return $this->lieu; }
    public function setLieu($value) { $this->lieu = $value; return $this; }

    public function getTypeCours() { return $this->type_cours; }
    public function setTypeCours($value) { $this->type_cours = $value; return $this; }

    public function getCommentaire() { return $this->commentaire; }
    public function setCommentaire($value) { $this->commentaire = $value; return $this; }

    public function getDureeHeure() { return $this->duree_heure; }
    public function setDureeHeure($value) { $this->duree_heure = $value; return $this; }

    public function getQrCode() { return $this->qr_code; }
    public function setQrCode($value) { $this->qr_code = $value; return $this; }

    public function getStatus() { return $this->status; }
    public function setStatus($value) { $this->status = $value; return $this; }

    public function getDateCreation() { return $this->date_creation; }
    public function setDateCreation($value) { $this->date_creation = $value; return $this; }

    public function create()
    {
        $sets = [];
        if (!is_null($this->UID)) {
            $sets[] = "`UID` = '$this->UID'";
        }
        if (!is_null($this->uid_assistant)) {
            $sets[] = "`uid_assistant` = '$this->uid_assistant'";
        }
        if (!is_null($this->code_cours)) {
            $sets[] = "`code_cours` = '$this->code_cours'";
        }
        if (!is_null($this->title)) {
            $sets[] = "`title` = '$this->title'";
        }
        if (!is_null($this->theme)) {
            $sets[] = "`theme` = '$this->theme'";
        }
        if (!is_null($this->date_cours)) {
            $sets[] = "`date_cours` = '$this->date_cours'";
        }
        if (!is_null($this->olm)) {
            $sets[] = "`olm` = '$this->olm'";
        }
        if (!is_null($this->lieu)) {
            $sets[] = "`lieu` = '$this->lieu'";
        }
        if (!is_null($this->type_cours)) {
            $sets[] = "`type_cours` = '$this->type_cours'";
        }
        if (!is_null($this->commentaire)) {
            $sets[] = "`commentaire` = '$this->commentaire'";
        }
        if (!is_null($this->duree_heure)) {
            $sets[] = "`duree_heure` = '$this->duree_heure'";
        }
        if (!is_null($this->qr_code)) {
            $sets[] = "`qr_code` = '$this->qr_code'";
        }if (!is_null($this->status)) {
            $sets[] = "`status` = '$this->status'";
        }
        if (empty($sets)) return false;
        $sql = "INSERT INTO `cours` SET " . implode(', ', $sets);
        return $this->dbh->insert($sql);
    }

    public function read($id)
    {
        return $this->dbh->query("SELECT * FROM `cours` WHERE `id` = '$id'", 3);
    }

    public function readAll()
    {
        return $this->dbh->query("SELECT * FROM `cours` WHERE `deleted` = 0 ORDER BY status ASC", 2);
    }

    public function readByUser()
    {
        return $this->dbh->query("SELECT * FROM `cours` WHERE `UID` = '$this->UID' AND `deleted` = 0 ORDER BY status ASC", 2);
    }
    
    public function readByAssistant()
    {
        return $this->dbh->query("SELECT * FROM `cours` WHERE `uid_assistant` = '$this->uid_assistant' AND `deleted` = 0 ORDER BY status ASC", 2);
    }

    public function readByUserOrAssistant(){
        return $this->dbh->query(" SELECT * 
                                    FROM `cours` 
                                    WHERE `deleted` = 0
                                    AND (
                                        `UID` = '$this->UID' 
                                        OR `uid_assistant` = '$this->uid_assistant'
                                    )
                                    ORDER BY status ASC
        ", 2);
    }

    
    public function readCourseByStatus(){
        $status = intval($this->dbh->protect_entry($this->status));
        $sql = "SELECT * FROM cours 
                    WHERE date_cours = CURDATE()
                    AND status = '$status'
        ";
        return $this->dbh->query($sql, 2);
    }

    public function getAllCoursesByStatus($limit = null){
        $status = intval($this->dbh->protect_entry($this->status));
        $sql = "SELECT * 
                    FROM cours 
                    WHERE `status` = '$status'
                    LIMIT $limit
        ";
        return $this->dbh->query($sql, 2);
    }

    public function update()
    {
        $updates = [];
        if (!is_null($this->UID)) {
            $updates[] = "`UID` = '$this->UID'";
        }
        if (!is_null($this->uid_assistant)) {
            $updates[] = "`uid_assistant` = '$this->uid_assistant'";
        }
        if (!is_null($this->code_cours)) {
            $sets[] = "`code_cours` = '$this->code_cours'";
        }
        if (!is_null($this->title)) {
            $sets[] = "`title` = '$this->title'";
        }
        if (!is_null($this->theme)) {
            $updates[] = "`theme` = '$this->theme'";
        }
        if (!is_null($this->date_cours)) {
            $updates[] = "`date_cours` = '$this->date_cours'";
        }
        if (!is_null($this->olm)) {
            $updates[] = "`olm` = '$this->olm'";
        }
        if (!is_null($this->lieu)) {
            $updates[] = "`lieu` = '$this->lieu'";
        }
        if (!is_null($this->type_cours)) {
            $updates[] = "`type_cours` = '$this->type_cours'";
        }
        if (!is_null($this->commentaire)) {
            $updates[] = "`commentaire` = '$this->commentaire'";
        }
        if (!is_null($this->duree_heure)) {
            $updates[] = "`duree_heure` = '$this->duree_heure'";
        }
        if (!is_null($this->qr_code)) {
            $updates[] = "`qr_code` = '$this->qr_code'";
        }
        if (!is_null($this->status)) {
            $updates[] = "`status` = '$this->status'";
        }
        if (!is_null($this->date_creation)) {
            $updates[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($updates)) return false;
        $sql = "UPDATE `cours` SET " . implode(', ', $updates) . " WHERE `id` = '$this->id'";
        return $this->dbh->exec($sql);
    }

    public function delete()
    {
        return $this->dbh->exec("DELETE FROM `cours` WHERE `id` = '$this->id'");
    }

    public function countPending(){
        return (int) $this->dbh->query(
            "SELECT COUNT(*) FROM cours WHERE status = 1",1
        );
    }

    public function countSessionsProgrammed() {
        return $this->dbh->query(
            "SELECT COUNT(*) FROM cours WHERE status = 2 AND date_cours >= CURDATE()", 1
        );
    }

    public function getStatusInfo() {
        $status = $this->dbh->protect_entry($this->status);
        $sql = "SELECT * FROM cours_status WHERE id = '$status'";
        return $this->dbh->query($sql, 3);
    }

    public function getNextStatus() {
        $status = $this->dbh->protect_entry($this->status);
        $sql = "SELECT cs2.* 
                FROM cours_status cs1
                LEFT JOIN cours_status cs2 ON cs1.next_status = cs2.id
                WHERE cs1.id = '$status'";
        return $this->dbh->query($sql, 3);
    }

    public function changeStatus($newStatusId, $userId = null, $details = null) {
        // Vérifie la transition
        $current = $this->getStatusInfo();
        if (!$current) return false;

        if ($current['next_status'] != $newStatusId) {
            return false; // Transition non autorisée
        }

        $this->status = $newStatusId;
        $this->update();

        // Journalisation
        $log = new cours_logs();
        $log->setIdCours($this->id)
            ->setUserId($userId)
            ->setAction("STATUS_CHANGE")
            ->setDetails("Passage de {$current['code']} à {$newStatusId}" . ($details ? " ($details)" : ""))
            ->setCreatedAt(date("Y-m-d H:i:s"))
            ->create();

        return true;
    }

}
