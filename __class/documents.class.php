<?php
class documents
{
    public $dbh;

    protected $id;
    protected $id_cours;
    protected $nom_fichier;
    protected $url_fichier;
    protected $type_document;
    protected $deleted;
    protected $date_creation;

    public function __construct()
    {
        $this->dbh = database::sharedInstance();
    }

    public function getId() { return $this->id; }
    public function setId($value) { $this->id = $value; return $this; }

    public function getIdCours() { return $this->id_cours; }
    public function setIdCours($value) { $this->id_cours = $value; return $this; }

    public function getNomFichier() { return $this->nom_fichier; }
    public function setNomFichier($value) { $this->nom_fichier = $value; return $this; }

    public function getUrlFichier() { return $this->url_fichier; }
    public function setUrlFichier($value) { $this->url_fichier = $value; return $this; }

    public function getTypeDocument() { return $this->type_document; }
    public function setTypeDocument($value) { $this->type_document = $value; return $this; }

    public function getDateCreation() { return $this->date_creation; }
    public function setDateCreation($value) { $this->date_creation = $value; return $this; }

        /**
     * Get the value of deleted
     */ 
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set the value of deleted
     *
     * @return  self
     */ 
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function create()
    {
        $sets = [];
        if (!is_null($this->id_cours)) {
            $sets[] = "`id_cours` = '$this->id_cours'";
        }
        if (!is_null($this->nom_fichier)) {
            $sets[] = "`nom_fichier` = '$this->nom_fichier'";
        }
        if (!is_null($this->url_fichier)) {
            $sets[] = "`url_fichier` = '$this->url_fichier'";
        }
        if (!is_null($this->type_document)) {
            $sets[] = "`type_document` = '$this->type_document'";
        }
        if (!is_null($this->deleted)) {
            $sets[] = "`deleted` = '0'";
        }
        if (!is_null($this->date_creation)) {
            $sets[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($sets)) return false;
        $sql = "INSERT INTO `documents` SET " . implode(', ', $sets);
        return $this->dbh->insert($sql);
    }

    public function read($id_cours)
    {
        return $this->dbh->query("SELECT * FROM `documents` WHERE `id_cours` = '$id_cours' AND `deleted` = 0", 2);
    }

    public function getDocsByType($limit, $offset){
        return $this->dbh->query("SELECT * 
                                    FROM `documents` 
                                    WHERE `id_cours` = '$this->id_cours'
                                    AND `type_document` = '$this->type_document'
                                    AND `deleted` = 0
                                    ORDER BY date_creation DESC
                                    LIMIT $offset, $limit
                                ", 2);
    }

    public function readByFile($idFile)
    {
        return $this->dbh->query("SELECT * FROM `documents` WHERE `id` = '$idFile'", 3);
    }

    public function update()
    {
        $updates = [];
        if (!is_null($this->id_cours)) {
            $updates[] = "`id_cours` = '$this->id_cours'";
        }
        if (!is_null($this->nom_fichier)) {
            $updates[] = "`nom_fichier` = '$this->nom_fichier'";
        }
        if (!is_null($this->url_fichier)) {
            $updates[] = "`url_fichier` = '$this->url_fichier'";
        }
        if (!is_null($this->type_document)) {
            $updates[] = "`type_document` = '$this->type_document'";
        }
        if (!is_null($this->deleted)) {
            $updates[] = "`deleted` = '$this->deleted'";
        }
        if (!is_null($this->date_creation)) {
            $updates[] = "`date_creation` = '$this->date_creation'";
        }
        if (empty($updates)) return false;
        $sql = "UPDATE `documents` SET " . implode(', ', $updates) . " WHERE `id` = '$this->id'";
        return $this->dbh->exec($sql);
    }

    public function delete()
    {
        return $this->dbh->exec("DELETE FROM `documents` WHERE `id` = '$this->id'");
    }

}
