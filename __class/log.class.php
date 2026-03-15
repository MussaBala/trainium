<?php

/**
 * Document    : __class/log.php
 * Created on  : 20110414
 * Last update : 20130906
 *
 * @author     : Stéphane KOEBERLE
 * @copyright  : Link To Business
 * @version    : 3.0
 */

class log
{
    private $id;
    private $type;
    private $session;
    private $action;
    private $value;
    private $detail;
    private $datetime;
    private $date;
    private $time;
    private $content;

    /**
     * Construction de l'objet log
     * @param string $type Type du log : ACCESS|DB|DEBUG|ERROR|EXEC
     * @param string $content Contenu du log à écrire
     */
    public function __construct(string $type, string $content)
    {
        $this->type = strtoupper($type);
        $this->content = $content;
        $this->datetime = date("Ymd-H:i:s");
        $this->date = date("Ymd");
        $this->time = date("H:i:s");

        // Écriture dans le fichier log
        $this->write_log_file();

        // Si erreur, on pourrait aussi envoyer un e-mail à l'administrateur (optionnel)
        // if ($this->type === 'ERROR') $this->send_log_by_email();
    }

    /**
     * Écriture du log dans un fichier texte
     */
    private function write_log_file(): void
    {
        // Nettoyer le contenu pour une seule ligne lisible
        $search = ["<br>", "<br/>", "<br />", "\r\n", "\r", "\n"];
        $this->content = str_replace($search, ' ', $this->content);

        $logLine = ($this->type === 'ERROR')
            ? "[{$this->date}-{$this->time}] [{$_SERVER['REQUEST_URI']}] {$this->content}\r\n"
            : "[{$this->date}-{$this->time}] {$this->content}\r\n";

        // Dossier du jour
        $logDir = $_SERVER['DOCUMENT_ROOT'] . "/__log/{$this->date}";
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $filePath = "{$logDir}/{$this->date}-{$this->type}-log.txt";
        file_put_contents($filePath, $logLine, FILE_APPEND);
    }

    /**
     * Enregistre le log dans la base de données
     */
    private function write_log_db(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $dbh = database::sharedInstance();
        $this->content = $dbh->protect_entry($this->content);
        $this->session = serialize($_SESSION);

        $this->id = $dbh->insert(
            "INSERT INTO `log`
             SET `content` = '$this->content',
                 `date` = NOW()",
            false
        );
    }

    /**
     * Envoi du log par email si une erreur est survenue
     */
    /*private function send_log_by_email(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        define('__BR__', "<br>");

        $subject = ($_SESSION['system']['system_name'] ?? 'INF Trainers') . " : une erreur est survenue !";
        $message = "Bonjour," . __BR__ . __BR__
            . "Voici le détail de l'erreur :" . __BR__ . __BR__
            . "Date : <b>{$this->datetime}</b>" . __BR__
            . "Utilisateur : <b>" . ($_SESSION['user']['UID'] ?? 'inconnu') . "</b>" . __BR__
            . "Session ID : <b>" . session_id() . "</b>" . __BR__
            . "Action : <b>{$this->action}</b>" . __BR__
            . "Valeur : <b>{$this->value}</b>" . __BR__
            . "Détail : <b>" . stripslashes($this->detail) . "</b>" . __BR__ . __BR__
            . "Erreur enregistrée sous l'ID <b>{$this->id}</b> dans la table <b>log</b>." . __BR__ . __BR__
            . "Bonne journée.";

        new email(null, $message, $subject, $message, false);
    }*/
}
