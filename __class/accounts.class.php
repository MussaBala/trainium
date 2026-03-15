<?php
class accounts
{
    public $dbh;

    protected $id;
    protected $UID;
    protected $grade;
    protected $nom;
    protected $prenom;
    protected $email;
    protected $telephone;
    protected $olm;
    protected $date_deb_formateur;
    protected $validate;
    protected $avatar;
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
     * Get the value of grade
     */ 
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * Set the value of grade
     *
     * @return  self
     */ 
    public function setGrade($grade)
    {
        $this->grade = $grade;
    }

    
    public function getNom() { return $this->nom; }
    public function setNom($value) { $this->nom = $value; return $this; }

    public function getPrenom() { return $this->prenom; }
    public function setPrenom($value) { $this->prenom = $value; return $this; }

    public function getEmail() { return $this->email; }
    public function setEmail($value) { $this->email = $value; return $this; }

    public function getTelephone() { return $this->telephone; }
    public function setTelephone($value) { $this->telephone = $value; return $this; }

        /**
     * Get the value of olm
     */ 
    public function getOlm()
    {
        return $this->olm;
    }

    /**
     * Set the value of olm
     *
     * @return  self
     */ 
    public function setOlm($olm)
    {
        $this->olm = $olm;

        return $this;
    }   
    
    /**
     * Get the value of validate
     */ 
    public function getValidate()
    {
        return $this->validate;
    }

    /**
     * Get the value of date_deb_formateur
     */ 
    public function getDate_deb_formateur()
    {
        return $this->date_deb_formateur;
    }

    /**
     * Set the value of date_deb_formateur
     *
     * @return  self
     */ 
    public function setDate_deb_formateur($date_deb_formateur)
    {
        $this->date_deb_formateur = $date_deb_formateur;

        return $this;
    }

    /**
     * Set the value of validate
     *
     * @return  self
     */ 
    public function setValidate($validate)
    {
        $this->validate = $validate;

        return $this;
    }

    
    public function getAvatar() { return $this->avatar; }
    public function setAvatar($value) { $this->avatar = $value; return $this; }

    public function getDateCreation() { return $this->date_creation; }
    public function setDateCreation($value) { $this->date_creation = $value; return $this; }

    public function create()
    {
        $sets = [];
        if (!is_null($this->UID)) {
            $this->UID = $this->dbh->protect_entry($this->UID);
            $sets[] = "`UID` = '$this->UID'";
        }
        if (!is_null($this->grade)) {
            $this->grade = $this->dbh->protect_entry($this->grade);
            $sets[] = "`grade` = '$this->grade'";
        }
        if (!is_null($this->nom)) {
            $this->nom = $this->dbh->protect_entry($this->nom);
            $sets[] = "`nom` = '$this->nom'";
        }
        if (!is_null($this->prenom)) {
            $this->prenom = $this->dbh->protect_entry($this->prenom);
            $sets[] = "`prenom` = '$this->prenom'";
        }
        if (!is_null($this->email)) {
            $this->email = $this->dbh->protect_entry($this->email);
            $sets[] = "`email` = '$this->email'";
        }
        if (!is_null($this->telephone)) {
            $this->telephone = $this->dbh->protect_entry($this->telephone);
            $sets[] = "`telephone` = '$this->telephone'";
        }
        if (!is_null($this->olm)) {
            $this->olm = $this->dbh->protect_entry($this->olm);
            $sets[] = "`olm` = '$this->olm'";
        }
        if (!is_null($this->date_deb_formateur)) {
            $sets[] = "`date_deb_formateur` = '$this->date_deb_formateur'";
        }
        if (!is_null($this->avatar)) {
            $sets[] = "`date_deb_formateur` = '$this->avatar'";
        }
        if (!is_null($this->date_creation)) {
            $sets[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($sets)) return false;
        $sql = "INSERT INTO `accounts` SET " . implode(', ', $sets);
        return $this->dbh->insert($sql);
    }

    public function read()
    {
        return $this->dbh->query("SELECT * FROM `accounts` WHERE `id` = '$this->id'", 3);
    }

    public function readAll(){
        return $this->dbh->query("SELECT * FROM `accounts`", 2);
    }

    public function readByUser()
    {
        $UID = $this->dbh->protect_entry($this->UID);
        return $this->dbh->query(
                "SELECT * 
                    FROM `accounts` 
                    WHERE `UID` = '$UID'
                ", 
        3);
    }

    public function readByEmail()
    {
        $email = $this->dbh->protect_entry($this->email);
        return $this->dbh->query(
                "SELECT * 
                    FROM `accounts` 
                    WHERE `email` = '$email'
                ", 
        3);
    }
    
    public function readByGrade(){
        $grade = $this->dbh->protect_entry($this->grade);
        return $this->dbh->query(
                "SELECT * 
                    FROM `accounts` 
                    WHERE `grade` = '$grade'
                    AND `validate` = 1
                    ORDER BY `nom` ASC
                ", 
        2);
    }

    public function update()
    {
        $updates = [];
        if (!is_null($this->UID)) {
            $this->UID = $this->dbh->protect_entry($this->UID);
            $updates[] = "`UID` = '$this->UID'";
        }
        if (!is_null($this->grade)) {
            $this->grade = $this->dbh->protect_entry($this->grade);
            $updates[] = "`grade` = '$this->grade'";
        }
        if (!is_null($this->nom)) {
            $this->nom = $this->dbh->protect_entry($this->nom);
            $updates[] = "`nom` = '$this->nom'";
        }
        if (!is_null($this->prenom)) {
            $this->prenom = $this->dbh->protect_entry($this->prenom);
            $updates[] = "`prenom` = '$this->prenom'";
        }
        if (!is_null($this->email)) {
            $this->email = $this->dbh->protect_entry($this->email);
            $updates[] = "`email` = '$this->email'";
        }
        if (!is_null($this->telephone)) {
            $this->telephone = $this->dbh->protect_entry($this->telephone);
            $updates[] = "`telephone` = '$this->telephone'";
        }
        if (!is_null($this->olm)) {
            $this->olm = $this->dbh->protect_entry($this->olm);
            $updates[] = "`olm` = '$this->olm'";
        }
        if (!is_null($this->date_deb_formateur)) {
            $updates[] = "`date_deb_formateur` = '$this->date_deb_formateur'";
        }
        if (!is_null($this->validate)) {
            $updates[] = "`validate` = '$this->validate'";
        }
        if (!is_null($this->avatar)) {
            $updates[] = "`avatar` = '$this->avatar'";
        }
        if (!is_null($this->date_creation)) {
            $updates[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($updates)) return false;
        $sql = "UPDATE `accounts` SET " . implode(', ', $updates) . " WHERE `id` = '$this->id'";
        return $this->dbh->exec($sql);
    }

    public function updateByUser()
    {
        $updates = [];
        if (!is_null($this->grade)) {
            $this->grade = $this->dbh->protect_entry($this->grade);
            $updates[] = "`grade` = '$this->grade'";
        }
        if (!is_null($this->nom)) {
            $this->nom = $this->dbh->protect_entry($this->nom);
            $updates[] = "`nom` = '$this->nom'";
        }
        if (!is_null($this->prenom)) {
            $this->prenom = $this->dbh->protect_entry($this->prenom);
            $updates[] = "`prenom` = '$this->prenom'";
        }
        if (!is_null($this->email)) {
            $this->email = $this->dbh->protect_entry($this->email);
            $updates[] = "`email` = '$this->email'";
        }
        if (!is_null($this->telephone)) {
            $this->telephone = $this->dbh->protect_entry($this->telephone);
            $updates[] = "`telephone` = '$this->telephone'";
        }
        if (!is_null($this->olm)) {
            $this->olm = $this->dbh->protect_entry($this->olm);
            $updates[] = "`olm` = '$this->olm'";
        }
        if (!is_null($this->date_deb_formateur)) {
            $updates[] = "`date_deb_formateur` = '$this->date_deb_formateur'";
        }
        if (!is_null($this->validate)) {
            $updates[] = "`validate` = '$this->validate'";
        }
        if (!is_null($this->avatar)) {
            $updates[] = "`avatar` = '$this->avatar'";
        }
        if (!is_null($this->date_creation)) {
            $updates[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($updates)) return false;
        $sql = "UPDATE `accounts` SET " . implode(', ', $updates) . " WHERE `UID` = '$this->UID'";
        return $this->dbh->exec($sql);
    }


    public function delete()
    {
        return $this->dbh->exec("DELETE FROM `accounts` WHERE `id` = '$this->id'");
    }

    /**
     * Incrémente les compteurs (UPSERT) dans accounts_stats
     */
    public function incrementStats(int $uid, int $deltaCours = 0, int $deltaHeures = 0) {
        $uid = (int)$uid;
        $deltaCours  = (int)$deltaCours;
        $deltaHeures = (int)$deltaHeures;

        // On utilise un upsert : si la ligne n'existe pas -> insert ; sinon on incrémente
        $sql = "INSERT INTO accounts_stats (uid, total_cours, total_heures, updated_at)
                    VALUES ('$uid', '$deltaCours', '$deltaHeures', NOW())
                    ON DUPLICATE KEY UPDATE
                        total_cours  = total_cours  + VALUES(total_cours),
                        total_heures = total_heures + VALUES(total_heures),
                        updated_at   = NOW()
        ";
        return $this->dbh->exec($sql);
    }

}
