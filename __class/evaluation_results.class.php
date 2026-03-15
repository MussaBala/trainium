<?php
class evaluation_results {
    public $dbh;

    protected $id;
    protected $id_cours;
    protected $user_id; // ex: l’assistant évalué ou le formateur principal évalué
    protected $moyenne_globale;
    protected $total_participants;
    protected $created_at;

    public function __construct(){
        $this->dbh = database::sharedInstance();
    }

    // === GETTERS & SETTERS ===
    public function getId(){ return $this->id; }
    public function setId($v){ $this->id = $v; return $this; }

    public function getId_cours(){ return $this->id_cours; }
    public function setId_cours($v){ $this->id_cours = $v; return $this; }

    public function getUser_id(){ return $this->user_id; }
    public function setUser_id($v){ $this->user_id = $v; return $this; }

    public function getMoyenne_globale(){ return $this->moyenne_globale; }
    public function setMoyenne_globale($v){ $this->moyenne_globale = $v; return $this; }

    public function getTotal_participants(){ return $this->total_participants; }
    public function setTotal_participants($v){ $this->total_participants = $v; return $this; }

    public function getCreated_at(){ return $this->created_at; }
    public function setCreated_at($v){ $this->created_at = $v; return $this; }

    // === CRUD ===
    public function create(){
        $sql = "INSERT INTO evaluation_results (id_cours, user_id, moyenne_globale, total_participants) 
                VALUES ('$this->id_cours', '$this->user_id', '$this->moyenne_globale', '$this->total_participants')";
        return $this->dbh->insert($sql);
    }

    public function readByCourse($id_cours){
        return $this->dbh->query("SELECT * FROM evaluation_results WHERE id_cours = '$id_cours'", 2);
    }

    public function readForUser($id_cours, $user_id){
        return $this->dbh->query("SELECT * FROM evaluation_results WHERE id_cours = '$id_cours' AND user_id = '$user_id'", 3);
    }

    public function update(){
        $sql = "UPDATE evaluation_results 
                SET moyenne_globale = '$this->moyenne_globale', total_participants = '$this->total_participants' 
                WHERE id_cours = '$this->id_cours' AND user_id = '$this->user_id'";
        return $this->dbh->exec($sql);
    }
}
