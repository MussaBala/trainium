<?php
// ClassGenerator.php - version orientée objet avec INSERT INTO SET et UPDATE filtrant les NULL

class ClassGenerator
{
    protected PDO $dbh;
    protected string $outputDir;

    public function __construct(PDO $dbh)
    {
        $this->dbh = $dbh;
        $this->outputDir = __DIR__ . '/__class/';

        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir);
        }
    }

    public function generateAll(): void
    {
        $tables = $this->dbh->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $this->generateClass($table);
        }
    }

    protected function camelize(string $string, bool $capitalizeFirstChar = true): string
    {
        $str = str_replace('_', '', ucwords($string, '_'));
        return $capitalizeFirstChar ? $str : lcfirst($str);
    }

    protected function getTableFields(string $table): array
    {
        $stmt = $this->dbh->prepare("DESCRIBE `$table`");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generateClass(string $table): void
    {
        $className = ucfirst($this->camelize($table));
        $fields = $this->getTableFields($table);
        $primaryKey = $fields[0]['Field'];

        $code = "<?php\n";
        $code .= "class $className\n{\n";
        $code .= "    public \$dbh;\n\n";

        foreach ($fields as $field) {
            $code .= "    protected \${$field['Field']};\n";
        }

        $code .= "\n    public function __construct()\n    {\n";
        $code .= "        \$this->dbh = database::sharedInstance();\n    }\n\n";

        foreach ($fields as $field) {
            $camel = $this->camelize($field['Field'], false);
            $ucCamel = ucfirst($camel);
            $code .= "    public function get$ucCamel() { return \$this->{$field['Field']}; }\n";
            $code .= "    public function set$ucCamel(\$value) { \$this->{$field['Field']} = \$value; return \$this; }\n\n";
        }

        // CREATE (INSERT INTO ... SET ...)
        $code .= "    public function create()\n    {\n";
        $code .= "        \$sets = [];\n";
        foreach ($fields as $field) {
            if (strtolower($field['Extra']) !== 'auto_increment') {
                $fieldName = $field['Field'];
                $code .= "        if (!is_null(\$this->$fieldName)) {\n";
                $code .= "            \$sets[] = \"`$fieldName` = '\$this->$fieldName'\";\n";
                $code .= "        }\n";
            }
        }
        $code .= "        if (empty(\$sets)) return false;\n";
        $code .= "        \$sql = \"INSERT INTO `$table` SET \" . implode(', ', \$sets);\n";
        $code .= "        return \$this->dbh->exec(\$sql);\n    }\n\n";

        // READ
        $code .= "    public function read(\$id)\n    {\n";
        $code .= "        return \$this->dbh->query(\"SELECT * FROM `$table` WHERE `$primaryKey` = '\$id'\", 3);\n    }\n\n";

        // UPDATE (ignore nulls)
        $code .= "    public function update()\n    {\n";
        $code .= "        \$updates = [];\n";
        foreach ($fields as $field) {
            if ($field['Field'] !== $primaryKey) {
                $fieldName = $field['Field'];
                $code .= "        if (!is_null(\$this->$fieldName)) {\n";
                $code .= "            \$updates[] = \"`$fieldName` = '\$this->$fieldName'\";\n";
                $code .= "        }\n";
            }
        }
        $code .= "        if (empty(\$updates)) return false;\n";
        $code .= "        \$sql = \"UPDATE `$table` SET \" . implode(', ', \$updates) . \" WHERE `$primaryKey` = '\$this->$primaryKey'\";\n";
        $code .= "        return \$this->dbh->exec(\$sql);\n    }\n\n";

        // DELETE
        $code .= "    public function delete()\n    {\n";
        $code .= "        return \$this->dbh->exec(\"DELETE FROM `$table` WHERE `$primaryKey` = '\$this->$primaryKey'\");\n    }\n";

        $code .= "}\n";

        $filename = strtolower($table) . '.class.php';
        file_put_contents($this->outputDir . $filename, $code);
        echo "Classe pour la table '$table' générée dans __class/$filename.\n";
    }
}

// Exécution du script POO
try {
    $dbh = new PDO('mysql:host=localhost;dbname=inf_trainers;charset=utf8', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $generator = new ClassGenerator($dbh);
    $generator->generateAll();

} catch (PDOException $e) {
    echo "Erreur de connexion: " . $e->getMessage();
}
