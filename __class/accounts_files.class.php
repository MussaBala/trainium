<?php
class accounts_files
{
    private $dbh;
    private $id;
    private $account_id;
    private $file_name;
    private $file_path;
    private $file_type;
    private $file_size;
    private $uploaded_by;
    private $uploaded_at;

    public function __construct()
    {
        $this->dbh = database::sharedInstance();
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getAccountId() { return $this->account_id; }
    public function setAccountId($account_id) { $this->account_id = $account_id; }

    public function getFileName() { return $this->file_name; }
    public function setFileName($file_name) { $this->file_name = $file_name; }

    public function getFilePath() { return $this->file_path; }
    public function setFilePath($file_path) { $this->file_path = $file_path; }

    public function getFileType() { return $this->file_type; }
    public function setFileType($file_type) { $this->file_type = $file_type; }

    public function getFileSize() { return $this->file_size; }
    public function setFileSize($file_size) { $this->file_size = $file_size; }

    public function getUploadedBy() { return $this->uploaded_by; }
    public function setUploadedBy($uploaded_by) { $this->uploaded_by = $uploaded_by; }

    public function getUploadedAt() { return $this->uploaded_at; }
    public function setUploadedAt($uploaded_at) { $this->uploaded_at = $uploaded_at; }

    public function create()
    {
        $sets = [];
        if (!is_null($this->account_id)) {
            $sets[] = "`account_id` = '$this->account_id'";
        }
        if (!is_null($this->file_name)) {
            $sets[] = "`file_name` = '$this->file_name'";
        }
        if (!is_null($this->file_path)) {
            $sets[] = "`file_path` = '$this->file_path'";
        }
        if (!is_null($this->file_type)) {
            $sets[] = "`file_type` = '$this->file_type'";
        }
        if (!is_null($this->file_size)) {
            $sets[] = "`file_size` = '$this->file_size'";
        }
        if (!is_null($this->uploaded_by)) {
            $sets[] = "`uploaded_by` = '$this->uploaded_by'";
        }
        if (!is_null($this->uploaded_at)) {
            $sets[] = "`uploaded_at` = '$this->uploaded_at'";
        }

        if (empty($sets)) return false;
        $sql = "INSERT INTO `accounts_files` SET " . implode(', ', $sets);
        return $this->dbh->insert($sql);
    }

    public function readAllByAccount($account_id)
    {
        return $this->dbh->query("SELECT * FROM `accounts_files` WHERE `account_id` = '$account_id' ORDER BY `uploaded_at` DESC", 2);
    }

    public function delete()
    {
        return $this->dbh->exec("DELETE FROM `accounts_files` WHERE `id` = '$this->id'");
    }
}
