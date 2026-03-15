<?php
if (!(isset($_SESSION))) {
    session_start();
}

require_once("autoload.class.php");

error_reporting(E_ALL);
ini_set("display_errors", "On");


class objects
{
    private $dbh;
    private $return;

    /**
     * @return mixed
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * @param $data
     * @internal param mixed $return
     */
    public function setReturn($data)
    {
        $return = ($data !== false) ? ['status' => true, 'result' => $data] : ['status' => false, 'result' => false];

        $this->return = $return;
    }

    public function __construct()
    {
        $this->dbh = database::sharedInstance();
    }

    public function generateAndHashPassword(int $length = 12): array
    {
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()-_=+';

        $all = $lower . $upper . $numbers . $symbols;

        $password = [
            $lower[random_int(0, strlen($lower) - 1)],
            $upper[random_int(0, strlen($upper) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
            $symbols[random_int(0, strlen($symbols) - 1)],
        ];

        for ($i = 4; $i < $length; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        shuffle($password);
        $plainPassword = implode('', $password);
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        return [
            'plain' => $plainPassword,
            'hashed' => $hashedPassword
        ];
    }

    public function generateMatricule(){}

    function formatOLM($rawOlm) {
        $label = ucwords(str_replace('_', ' ', $rawOlm));
        return 'JCI ' . $label;
    }

    function generateLogin($_array = []){
        if (!isset($_array['nom']) || !isset($_array['prenoms'])) {
            throw new InvalidArgumentException("Les clés 'nom' et 'prenoms' sont requises.");
        }

        $nom = trim(strtolower($_array['nom']));
        $prenoms = trim($_array['prenoms']);
        $prenomsArray = preg_split('/\s+/', $prenoms);
        $dernierPrenom = strtolower(end($prenomsArray));
        return $dernierPrenom . '.' . $nom;
    }

    public function accountRegister($_array = []){
        $acc = new accounts();
        $logger = new activities_log();

        $acc->setGrade($_array['grade']);
        $acc->setNom($_array['nom']);
        $acc->setPrenom($_array['prenoms']);
        $acc->setEmail($_array['email']);
        $acc->setTelephone($_array['contact']);
        $acc->setOLM($_array['olm']);

        $res = $acc->create();

        if ($res !== 0) {
            $logger->log([
                'user_id' => null,
                'type' => 'USER_ACTION',
                'label' => 'Création de compte',
                'target' => 'accounts',
                'message' => "Nouvelle inscription de {$_array['nom']} {$_array['prenoms']} ({$_array['email']})"
            ]);

            return ['success' => true, 'message' => "✅ Votre Inscription a été enregistrée, Vous recevrez un mail après validation !"];
        } else {
            $logger->log([
                'user_id' => null,
                'type' => 'ERROR',
                'label' => 'Création de compte',
                'target' => 'accounts',
                'message' => "Échec de l'inscription de {$_array['nom']} {$_array['prenoms']} ({$_array['email']})"
            ]);

            return ['success' => false, 'message' => "❌ Échec de l'inscription, Veuillez reprendre ou contacter l'INF JCI-CI !"];
        }
    }

    public function getAllAccounts(){
        $acc = new accounts();
        return $acc->readAll();
    }

    public function getAccountById($id){
        $acc = new accounts();
        $acc->setId($id);
        return $res = $acc->read();
    }

    public function accountValidation(array $_array = []){
        $acc = new accounts();
        $utils = new utils();
        $logger = new activities_log();

        $idAcc = $_array['id'] ?? null;
        $action = $_array['action'] ?? null;

        if (!$idAcc || !in_array($action, ['valider', 'refuser'])) {
            return ['success' => false, 'message' => "Paramètres invalides."];
        }

        $account = $this->getAccountById($idAcc);
        if (!$account) {
            return ['success' => false, 'message' => "Compte introuvable."];
        }

        if ($action === 'valider') {
            $_login = [
                'nom' => $account['nom'],
                'prenoms' => $account['prenom']
            ];

            $login = $this->generateLogin($_login);
            $passwords = $utils->generateAndHashPassword();
            $hashed = $passwords['hashed'];
            $plain = $passwords['plain'];

            $_user = [
                'login' => $login,
                'email' => $account['email'],
                'password' => $hashed,
                'actif' => 1,
                'statut' => 1
            ];
            $user = $this->createNewUser($_user);

            $gc = new group_content_users();
            $gc->setGroupId(3);
            $gc->setUID($user['UID']);
            $gc->create();

            if (is_array($user) && isset($user['UID'])) {
                $acc->setId($idAcc);
                $acc->setUID($user['UID']);
                $acc->setValidate(1);
                $res = $acc->update();

                if (!$res) {
                    return ['success' => false, 'message' => "Échec de la validation du compte."];
                }

                $message = "Bonjour {$account['prenom']} {$account['nom']},\n\n";
                $message .= "Votre compte a été validé avec succès.\n";
                $message .= "Identifiants de connexion :\n";
                $message .= "Login : $login\n";
                $message .= "Email : {$account['email']}\n";
                $message .= "Mot de passe : $plain\n\n";
                $message .= "Merci de vous connecter à la plateforme INF TRAINERS.";

                $utils->sendSimpleMail(
                    $account['email'],
                    'Validation de votre compte INF TRAINERS',
                    nl2br($message)
                );

                $logger->log([
                    'user_id' => $user['UID'],
                    'type' => 'USER_ACTION',
                    'label' => 'Validation de compte',
                    'target' => 'accounts',
                    'message' => "Compte validé pour l'email : " . $account['email']
                ]);

                return ['success' => true, 'message' => "Compte validé et utilisateur notifié."];
            } else {
                return ['success' => false, 'message' => "Échec de création de l'utilisateur."];
            }
        }

        if ($action === 'refuser') {
            $acc->setId($idAcc);
            $acc->setValidate(-1);
            $res = $acc->update();

            $logger->log([
                'user_id' => null,
                'type' => 'USER_ACTION',
                'label' => 'Refus de compte',
                'target' => 'accounts',
                'message' => 'Compte refusé pour l\'ID : ' . $idAcc
            ]);

            if (!$res) {
                return ['success' => false, 'message' => "Échec du refus du compte."];
            }

            return ['success' => true, 'message' => "Compte refusé"];
        }

        return ['success' => false, 'message' => "Action inconnue."];
    }

    public function createNewUser($_array = []){
        $user = new User();
        if (!(isset($_array['password']))) {
            $password = $this->generateAndHashPassword();
            $pass = $password['hashed'];
        } else {
            $pass = $_array['password'];
        }

        $user->setLogin($_array['login']);
        $user->setEmail($_array['email']);
        $user->setPassword($pass);
        $user->setEmail($_array['email']);
        if (isset($_array['actif'])) {
            $user->setActif($_array['actif']);
        }
        $user->setStatut(1);

        $res = $user->create();
        if ($res > 0) {
            $tab = [];
            $tab['UID'] = $res;
            $tab['password'] = $pass;
            return $tab;
        } else {
            return "erreur d'enregistrement";
        }
    }

    public function userConnexion($_array = []){
        $user = new User();
        $logger = new activities_log();

        $email = $_array['email'];
        $user->setEmail($email);
        $res1 = $user->readUserByEmail();
        $UID = $res1['id'];
        $user->setId($UID);

        if ($res1 == null) {
            return ['success' => false, 'message' => "Vous n'avez pas de compte, Veuillez vous inscrire."];
        }

        $Respass = $res1['password'];
        $password = $_array['password'];
        $user->setLogin($res1['login']);
        $hashed = password_verify($password, $Respass);

        if ($hashed == true) {
            $acc = new Accounts();
            $acc->setUID($UID);
            $acc_infos = $acc->readByUser();

            $formateur = new Formateurs();
            if (is_null($acc_infos)) {
                $tabForm = [];
            } else {
                $formateur->setIdAccount($acc_infos['id']);
                $tabForm = $formateur->readByAcc();
            }

            $group = new group_content_users();
            $group->setUID($UID);
            $res = $group->readByUser();
            $role = $res['code'];

            $_SESSION['user'] = $res1;
            $_SESSION['user']['role'] = $role;
            $_SESSION['account'] = $acc_infos;
            $_SESSION['formateur'] = $tabForm;

            $user->setLast_connexion(date("Y-m-d H:i:s"));
            $user->update();

            $logger->log([
                'user_id' => $UID ?? null,
                'type' => 'USER_ACTION',
                'label' => 'Connexion utilisateur',
                'target' => 'users',
                'message' => 'Connexion réussie avec email ' . $email,
            ]);

            return ['success' => true, 'message' => "✅ Vous êtes connectés !"];
        } else {
            $logger->log([
                'user_id' => $UID ?? null,
                'type' => 'USER_ACTION',
                'label' => 'Connexion utilisateur',
                'target' => 'users',
                'message' => 'Echec Connexion' . $email,
            ]);

            return ['success' => false, 'message' => "❌ Identifiant ou Mot de passe incorrect"];
        }
    }

    public function getAllGrades(){
        $grade = new grades();
        return $grade->readAll();
    }

    public function getGradeByCode($code){
        $grade = new grades();
        $grade->setCode($code);
        return $grade->readGradeByCode();
    }
}
