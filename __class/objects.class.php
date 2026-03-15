<?php
if (!(isset($_SESSION))) {
    session_start();
}

require_once("autoload.class.php");
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

error_reporting(E_ALL);
ini_set("display_errors", "On");

class objects{
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

    /**
     * Envoie une notification à un utilisateur
     * - interne (DB)
     * - mail (optionnel)
     * - push PWA (si abonné)
     */
    public function notifyUser($userId, $title, $message, $url = '#', $type = 'INFO', $sendEmail = false) {
        // 1️⃣ Sauvegarde en base (notification interne)
        $notif = new notifications();
        $notif->setUserId($userId)
              ->setTitle($title)
              ->setMessage($message)
              ->setUrl($url)
              ->setType($type)
              ->create();

        
        // 2️⃣ Envoi email si demandé
        if ($sendEmail) {
            $acc = new accounts();
            $acc->setId($userId);
            $user = $acc->read();

            if ($user && !empty($user['email'])) {
                $utils = new utils();
                $utils->sendSMTPMail(
                    $user['email'],
                    $title,
                    nl2br($message . "<br><a href='" . BASE_URL . $url . "'>Voir plus</a>")
                );
            }
        }

        // 3️⃣ Envoi push PWA si abonnement
        $subs = $this->dbh->query("SELECT subscription_json FROM push_subscriptions WHERE user_id = '$userId'", 2);

        if (!empty($subs)) {
            $auth = [
                'VAPID' => [
                    'subject'    => 'mailto:mansahpro@gmail.com',
                    'publicKey'  => VAPID_PUBLIC_KEY,
                    'privateKey' => VAPID_PRIVATE_KEY,
                ],
            ];

            $webPush = new WebPush($auth);

            foreach ($subs as $sub) {
                $subscription = Subscription::create(json_decode($sub['subscription_json'], true));
                $webPush->sendOneNotification(
                    $subscription,
                    json_encode([
                        "title" => $title,
                        "body"  => $message,
                        "url"   => $url,
                    ])
                );
            }

            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) {
                    error_log("✅ Push envoyé à {$userId}");
                } else {
                    error_log("❌ Erreur Push pour {$userId}: " . $report->getReason());
                }
            }
        }
    }

    public function notifyMany($userIds, $title, $message, $url = '#', $type = 'INFO', $sendEmail = false) {
        foreach ($userIds as $uid) {
            $this->notifyUser($uid, $title, $message, $url, $type, $sendEmail);
        }
    }

    public function generateAndHashPassword(int $length = 12): array
    {
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()-_=+';

        $all = $lower . $upper . $numbers . $symbols;

        // Construire un mot de passe robuste avec un minimum de diversité
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

        // Hash sécurisé
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        return [
            'plain' => $plainPassword,
            'hashed' => $hashedPassword
        ];
    }

    public function generateToken(){
        return $token = bin2hex(random_bytes(32));
    }

    public function getToken($token){
        $appToken = new app_tokens();
        $appToken->setToken($token);
        $appToken->setType('course_evaluation');
        return $appToken->validate();
    }

    public function verifyHash($hash){
        $eval = new evaluation_submissions();
        $eval->setHash_unique($hash);
        return $eval->getHash();
    }

    public function generateCoursQrCode($coursId){
        $course = new cours();
        $tokens = new app_tokens(); 
        $cours = $course->read($coursId);
        $accountId = $_SESSION['account']['id'];

        if (!$cours) {
            echo json_encode(['success' => false, 'message' => 'Cours introuvable']);
            exit;
        }

        // Génération du token
        $jeton = $this->generateToken();
        $evaluationLink = BASE_URL . "evaluation.php?token={$jeton}";
        $expiresAt = date('Y-m-d H:i:s', strtotime('+48 hours'));
        $userId = $_SESSION['user']['id'];

        // 1️⃣ Génération du token (48h de validité)
        $tokens->setToken($this->dbh->protect_entry($jeton));
        $tokens->setType($this->dbh->protect_entry('course_evaluation')); // type
        $tokens->setEntityId($coursId); // entity_id
        $tokens->setEntityTable($this->dbh->protect_entry('cours')); // entity_table
        $tokens->setExpiresAt($expiresAt) ; // expiration
        $tokens->SetCreatedBy($userId); // created_by

        $token = $tokens->create();

        // 2️⃣ Génération du QR code
        require_once($_SERVER['DOCUMENT_ROOT'] . '/assets/vendors/phpqrcode/qrlib.php');
        // Dossier où stocker le QR code
        $dirPath = '__files/' . basename($cours['UID']) . '/docs/cours/' . basename($coursId) . '/';
        $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $dirPath;
        // Création récursive du dossier si besoin
        if (!is_dir($baseDir)) {
            if (!mkdir($baseDir, 0775, true)) {
                die("❌ Erreur : impossible de créer le dossier $baseDir");
            }
        }

        // Vérification des droits d'écriture
        if (!is_writable($baseDir)) {
            die("❌ Erreur : le dossier $baseDir n'est pas accessible en écriture.");
        }

        // Lien à encoder
        $evaluationLink = BASE_URL . "evaluation.php?token={$token}";

        // Chemin absolu et web du fichier QR code
        $qrCodeFileName = 'qr_evaluation.png';
        $qrCodeAbsolutePath = $baseDir . $qrCodeFileName;
        $qrCodeWebPath = '/' . $dirPath . $qrCodeFileName;

        // Si un QR code existe déjà, on le supprime
        if (file_exists($qrCodeAbsolutePath)) {
            unlink($qrCodeAbsolutePath);
        }

        // Génération du QR code
        QRcode::png($evaluationLink, $qrCodeAbsolutePath, QR_ECLEVEL_H, 6);

        // 🔹 Compression du PNG pour optimiser le poids
        $image = imagecreatefrompng($qrCodeAbsolutePath);
        imagepng($image, $qrCodeAbsolutePath, 9); // Qualité max mais PNG compressé
        imagedestroy($image);
        $attachments[] = $qrCodeAbsolutePath;

        //ENREGISTREMENT DANS LA TABLE COURS
        $course->setId($coursId);
        $course->setQrCode($qrCodeWebPath);
        $course->update();

        
        return [
            'success' => true,
            'qr_path' => $qrCodeWebPath
        ];

    }

    public function getPhotosByCours($coursId, $limit, $offset){
        $docs = new documents();
        $docs->setIdCours($coursId);
        $docs->setTypeDocument('photo');
        $photos = $docs->getDocsByType($limit, $offset);

        echo json_encode([
            'success' => true,
            'photos' => $photos
        ]);

    }

    public function generateMatricule(){
    }

    function generateCoursCode($uid) {
        $date = date('Ym'); // AAAAMM
        $random = strtoupper(substr(bin2hex(random_bytes(2)), 0, 2)); // 2 caractères aléatoires
        return "C$uid-$date-$random";
    }

    public function changeCourseStatus(int $coursId, int $userId, string $direction = 'next', string $details = ''){
        $cours       = new cours();
        $coursStatus = new cours_status();
        $log         = new cours_logs();

        // 1. Charger le cours
        $course = $cours->read($coursId);
        if (!$course) {
            return ['success' => false, 'message' => "❌ Cours introuvable."];
        }

        $currentStatusId = intval($course['status']);

        // 2. Charger le statut actuel
        $statusObj = $coursStatus->read($currentStatusId);
        if (!$statusObj) {
            return ['success' => false, 'message' => "❌ Statut actuel invalide."];
        }

        // 3. Déterminer le statut cible selon la direction
        if ($direction === 'next') {
            $targetStatusId = $statusObj['next_status'];
            $directionText  = "→ statut suivant";
        } elseif ($direction === 'prev') {
            $targetStatusId = $statusObj['prev_status'];
            $directionText  = "← retour statut précédent";
        } else {
            return ['success' => false, 'message' => "⚠️ Direction invalide (valeurs possibles : 'next', 'prev')."];
        }

        if (is_null($targetStatusId)) {
            return ['success' => false, 'message' => "⚠️ Aucun $directionText possible depuis ce statut."];
        }

        $targetStatus = $coursStatus->read($targetStatusId);
        if (!$targetStatus) {
            return ['success' => false, 'message' => "❌ Impossible de charger le statut cible."];
        }

        // 4. Mettre à jour le statut du cours
        $cours->setId($coursId);
        $cours->setStatus($targetStatus['id']);
        $update = $cours->update();

        if (!$update) {
            return ['success' => false, 'message' => "❌ Impossible de mettre à jour le statut du cours."];
        }

        // 5. Journalisation
        $log->setIdCours($coursId);
        $log->setUserId($userId);
        $log->setAction("Changement de statut");
        $details = $this->dbh->protect_entry("Passage de « {$statusObj['libelle']} » $directionText → « {$targetStatus['libelle']} ». $details");
        $log->setDetails($details);
        $log->create();

        return [
            'success' => true,
            'message' => "✅ Statut du cours mis à jour ({$statusObj['libelle']} $directionText → {$targetStatus['libelle']}).",
            'new_status' => $targetStatus
        ];
    }

    public function autoUpdateCourseStatusByDate() {
        $cours       = new cours();
        $statusModel = new cours_status();

        // Récupération des statuts clés
        $approvedInf = $statusModel->readByCode('APPROVED_INF');
        $pending     = $statusModel->readByCode('PENDING');
        $evalClosed  = $statusModel->readByCode('EVAL_CLOSED');
        $evalReview  = $statusModel->readByCode('EVAL_REVIEW');

        if (!$approvedInf || !$pending || !$evalClosed || !$evalReview) {
            return [
                'success' => false,
                'message' => '❌ Impossible de trouver les statuts requis.',
                'updated' => 0,
                'failed'  => 0,
                'details' => []
            ];
        }

        $approvedId = intval($approvedInf['id']); 
        $pendingId  = intval($pending['id']);     
        $evalClosedId = intval($evalClosed['id']);     

        $systemUserId = 0; // changements faits par le système
        $updated = 0;
        $failed  = 0;
        $details = [];

        $today   = new DateTime("today");
        $now     = new DateTime();

        /* --- CAS 1 : Le jour du cours => APPROVED_INF → PENDING --- */
        $cours->setStatus($approvedId);
        $coursToPending = $cours->readCourseByStatus();

        foreach ($coursToPending as $c) {
            $dateCours = new DateTime($c['date_cours']);

            if ($dateCours->format('Y-m-d') === $today->format('Y-m-d')) {
                $result = $this->changeCourseStatus(
                    intval($c['id']), 
                    $systemUserId, 
                    'next',
                    "Mise à jour automatique par le système (début du cours)"
                );

                $details[] = [
                    'cours_id'   => $c['id'],
                    'code'       => $c['code_cours'],
                    'title'      => $c['title'],
                    'from'       => $approvedInf['libelle'],
                    'to'         => $pending['libelle'],
                    'success'    => $result['success'],
                    'message'    => $result['message']
                ];

                if ($result['success']) $updated++; else $failed++;
            }
        }

        /* --- CAS 2 : Le lendemain à 10h => PENDING → EVAL_CLOSED --- */
        $cours->setStatus($pendingId);
        $coursToClose = $cours->readCourseByStatus();

        foreach ($coursToClose as $c) {
            $dateCours  = new DateTime($c['date_cours']);
            $closeDate  = (clone $dateCours)->modify('+1 day')->setTime(10, 0);

            if ($now >= $closeDate) {
                $result = $this->changeCourseStatus(
                    intval($c['id']), 
                    $systemUserId, 
                    'next',
                    "Clôture automatique des évaluations (EVAL_CLOSED)"
                );

                $details[] = [
                    'cours_id'   => $c['id'],
                    'code'       => $c['code_cours'],
                    'title'      => $c['title'],
                    'from'       => $pending['libelle'],
                    'to'         => $evalClosed['libelle'],
                    'success'    => $result['success'],
                    'message'    => $result['message']
                ];

                if ($result['success']) {
                    $updated++;
                    // 🔔 Notif au formateur : il doit déposer ses photos
                    $this->notifyUser(
                        $c['UID'],
                        "Clôture des évaluations 📸",
                        "Les évaluations du cours « {$c['title']} » sont clôturées. 
                        Merci d’ajouter au moins 4 photos dans les 24h pour permettre la validation.",
                        "index.php?page=this-course&id={$c['id']}&tab=photos",
                        "WARNING",
                        true
                    );
                } else {
                    $failed++;
                }
            }
        }

        return [
            'success' => ($failed === 0),
            'message' => "✅ $updated cours mis à jour, ❌ $failed échecs.",
            'updated' => $updated,
            'failed'  => $failed,
            'details' => $details
        ];
    }

    public function getLatestCoursesByStatus($status, $limit){
        $cours = new cours();
        $coursStatus = new cours_status();
        
        $status= $coursStatus->dbh->protect_entry($status);
        $res = $coursStatus->readByCode($status);
        $cours->setStatus($res['id']);

        return $cours->getAllCoursesByStatus($limit);
    }

    public function getAllCourseStatus(){
        $status = new cours_status();
        return $status->readAll();
    }

    function getAllOlms($country = null){
        $olms = new olms();
        if ($country == null) {
            return $olms->readAll();
        }else {
            return $olms->readAllNationalOlms();
        }
    }

    function formatOLM($input) {
        $input = trim($input);

        // Séparer nom et pays
        $parts = explode('/', $input);
        $nomPart = trim($parts[0]); // ex: "JCI Ouaga Etoile"
        $paysPart = isset($parts[1]) ? trim($parts[1]) : '';

        // Génération du code à partir du nom
        $code = preg_replace('/^JCI\s+/i', '', $nomPart); // retire JCI
        $code = iconv('UTF-8', 'ASCII//TRANSLIT', $code); // supprime accents
        $code = strtolower($code);
        $code = preg_replace('/[^a-z0-9]+/', '_', $code); // espaces/symboles → _
        $code = trim($code, '_'); // nettoyer _ en trop

        return [
            'code' => $code,   // "ouaga_etoile"
            'nom'  => $nomPart, // "JCI Ouaga Etoile"
            'pays' => $paysPart // "Burkina Faso"
        ];
    }

    function getOlmByCode($code){
        $olm = new olms();
        $olm->setCode($code);
        return $olm->readByCode();
    }

    function generateLogin($_array = []){
        if (!isset($_array['nom']) || !isset($_array['prenoms'])) {
            throw new InvalidArgumentException("Les clés 'nom' et 'prenoms' sont requises.");
        }

        // Nettoyage
        $nom = trim(strtolower($_array['nom']));
        $prenoms = trim($_array['prenoms']);

        // Découper les prénoms
        $prenomsArray = preg_split('/\s+/', $prenoms);
        $dernierPrenom = strtolower(end($prenomsArray));
        $login = $dernierPrenom . '.' . $nom;

        // Générer le login
        return $login;
    }

    public function testSMTPMail(): array{
        $utils = new utils();

        $to = 'mansahpro@gmail.com'; // 🔁 Remplace par une adresse email valide
        $subject = 'Test SMTP depuis INF TRAINERS';
        $body = "Bonjour,\nCeci est un test d'envoi de mail via SMTP avec PHPMailer.";

        $success = $utils->sendSMTPMail($to, $subject, $body);

        if ($success) {
            return ['success' => true, 'message' => "✅ Email envoyé avec succès à $to"];
        } else {
            return ['success' => false, 'message' => "❌ Échec de l'envoi de l'email à $to"];
        }
    }

    public function accountRegister($_array = []){
        $acc = new accounts();
        $email = trim($_array['email']);
        $acc->setEmail($email);

        $existing = $acc->readByEmail();

        if ($existing) {
            if ($existing['validate'] == 1) {
                return ['success' => false, 'message' => "❌ Vous disposez déjà d'un compte validé. Veuillez vous connecter."];
            }
            if ($existing['validate'] == 0) {
                if (is_null($existing['UID'])) {
                    return [
                        'success' => false,
                        'message' => "Votre compte est en attente de validation. Vous recevrez un mail quand l'INF procédera à sa validation."
                    ];
                }

                $user_id = $existing['UID'];
                $pr = new password_resets();
                $pr->setUserId($user_id);
                $lastToken = $pr->readTokenByUID();

                if ($lastToken && strtotime($lastToken['expires_at']) > time() && $lastToken['used'] == 0) {
                    return [
                        'success' => false,
                        'message' => "⚠️ Une demande d'activation a déjà été envoyée. Veuillez vérifier vos mails (y compris les spams)."
                    ];
                }

                if ($lastToken && strtotime($lastToken['expires_at']) < time() && $lastToken['used'] == 0) {
                    return [
                        'success' => false,
                        'message' => "⚠️ Une demande d'activation a déjà été envoyée mais a expiré. Veuillez contacter l'INF."
                    ];
                }
            }
        }

        // Création du compte
        $acc->setGrade($_array['grade']);
        $acc->setNom($_array['nom']);
        $acc->setPrenom($_array['prenoms']);
        $acc->setTelephone($_array['contact']);
        $acc->setOLM($_array['olm']);
        $acc->setDate_deb_formateur($_array['date_debut']);

        $res = $acc->create();
        if ($res !== 0) {
            $logger = new activities_log();
            $logger->log([
                'type' => 'USER_ACTION',
                'label' => 'Demande d\'inscription',
                'target' => 'account',
                'message' => "Inscription de {$acc->getNom()} {$acc->getPrenom()} via le formulaire.",
            ]);

            // 🔔 Notif pour les 3 admins (interne + mail)
            $admins = [1, 2, 3]; 
            $this->notifyMany(
                $admins,
                "Nouvelle inscription",
                "Un nouveau compte a été créé par {$acc->getNom()} {$acc->getPrenom()} et attend validation.",
                "?page=list-user",
                "INFO",
                true // => avec mail
            );

            return ['success' => true, 'message' => "✅ Votre inscription a été enregistrée. Vous recevrez un mail après validation de l'INF!"];
        }else {
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

    public function getAccountByUID($uid){
        $acc = new accounts();
        $acc->setUID($uid);
        return $res = $acc->readByUser();
    }

    public function resetPassword($_array = []) {
        $utils = new utils();
        $logger = new activities_log();
        $token = $_array['token'] ?? null;
        $plainPassword = $_array['password'] ?? null;

        if (!$token || !$plainPassword) {
            return ['success' => false, 'message' => 'Token ou mot de passe manquant.'];
        }

        // Vérifier que le token est valide et non expiré
        $reset = new password_resets();
        $resetData = $this->findByToken($token);

        if (!$resetData || $resetData['used'] == 1 || strtotime($resetData['expires_at']) < time()) {
            return ['success' => false, 'message' => 'Lien expiré ou déjà utilisé.'];
        }

        $userId = (int) $resetData['user_id'];

        // Générer un mot de passe hashé
        $hashed = password_hash($plainPassword, PASSWORD_DEFAULT);

        // Mise à jour du mot de passe dans la table users
        $user = new user();
        $user->setId($userId);
        $user->setPassword($hashed);
        $user->setStatut(1);
        $resUser = $user->update();

        if (!$resUser) {
            return ['success' => false, 'message' => "Erreur lors de la mise à jour du mot de passe."];
        }

        // Marquer le token comme utilisé
        $reset->setId($resetData['id']);
        $reset->setUsed(1);
        $reset->setToken($token);
        $reset->markAsUsed();

        // (Optionnel) Mise à jour du compte pour indiquer que le profil est activé
        $acc = new accounts();
        $acc->setUID($userId);
        $acc->setValidate(1);
        $acc->updateByUser();

        // Log de l'activité
        $logger->log([
            'user_id' => $userId,
            'type' => 'USER_ACTION',
            'label' => 'Changement de mot de passe',
            'target' => 'users',
            'message' => "Nouveau mot de passe défini via lien sécurisé."
        ]);

        return ['success' => true, 'message' => 'Mot de passe mis à jour avec succès.'];
    }

    public function findByToken($token){
        $pr = new password_resets();
        $pr->setToken($token);
        return $pr->readByToken();
    }

    public function forgotenPassword($_array = []){
        $pr = new password_resets();
        $acc = new accounts();
        $user = new user();
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            echo json_encode([
                'success' => false,
                'message' => "Veuillez renseigner votre adresse email."
            ]);
            exit;
        }

        // Vérifier si l'email est associé à un compte validé
        $acc->setEmail($email);
        $account = $acc->readByEmail($email);

        if (!$account || $account['validate'] != 1) {
            echo json_encode([
                'success' => false,
                'message' => "Aucun compte validé trouvé avec cette adresse email."
            ]);
            exit;
        }

        // Récupérer l'utilisateur lié
        $user = $user->read($account['UID']);
        if (!$user) {
            echo json_encode([
                'success' => false,
                'message' => "Utilisateur introuvable pour ce compte."
            ]);
            exit;
        }

        $userId = $user['id'];
        // Invalider anciens tokens
        $this->invalidateOldToken($userId);

        // Générer un nouveau token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));

        // Enregistrement du nouveau token
        $pr->setUserId($userId);
        $pr->setToken($token);
        $pr->setExpiresAt($expiresAt);
        $pr->create();

        // Envoi de l'email
        $link = BASE_URL . "reset-password.php?token=$token";
        $message = "Bonjour {$account['prenom']} {$account['nom']},\n\n";
        $message .= "Vous avez demandé une réinitialisation de mot de passe.\n\n";
        $message .= "Cliquez sur le lien ci-dessous pour choisir un nouveau mot de passe :\n$link\n\n";
        $message .= "Ce lien expirera dans 24 heures.\n\n";
        $message .= "Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.\n\n";
        $message .= "-- INF JCI-CI";

        $utils = new utils();
        $mailSent = $utils->sendSMTPMail(
            $account['email'],
            "🔐 Réinitialisation de mot de passe - INF TRAINERS",
            nl2br($message)
        );

        // 📘 Log d'activité
        $logger = new activities_log();
        $logger->log([
            'type'    => 'SECURITY',
            'label'   => 'Demande de réinitialisation',
            'target'  => 'users',
            'user_id' => $userId,
            'message' => "Demande de réinitialisation du mot de passe pour l'utilisateur {$user['login']}."
        ]);

        echo json_encode([
            'success' => $mailSent,
            'message' => $mailSent 
                ? "📩 Un email a été envoyé à {$account['email']} avec les instructions pour réinitialiser votre mot de passe." 
                : "❌ Échec de l'envoi du mail. Veuillez réessayer plus tard."
        ]);
        exit;

    }

    public function findTokenByUID($uid){
        $pr = new password_resets();
        $pr->setUserId($uid);
        return $pr->readTokenByUID();
    }

    // === LOGIQUE MÉTIER notificationsS ===

    public function sendNotifications($userId, $title, $message, $url = '#', $type = 'INFO')
    {
        $notif = new notifications();
        $notif->setUserId($userId)
              ->setTitle($title)
              ->setMessage($message)
              ->setUrl($url)
              ->setType($type);
        return $notif->create();
    }

    public function getUserNotifications($userId, $limit = 10)
    {
        $notif = new notifications();
        return $notif->readAllByUser($userId, $limit);
    }

    public function getUnreadCount($userId)
    {
        $notif = new notifications();
        return $notif->countUnread($userId);
    }

    public function markAllUserNotificationssRead($userId)
    {
        $notif = new notifications();
        return $notif->markAllReadByUser($userId);
    }

    public function adminSendNotifications($_array = []){
        $title   = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $url     = trim($_POST['url'] ?? '#');
        $sendEmail = !empty($_POST['send_email']);

        if ($title && $message) {
            $obj = new objects();
            $res = $obj->notifyAllUsers($title, $message, $url, "INFO", $sendEmail);

            echo json_encode(['success' => true, 'message' => "✅ Notification envoyée à tous les utilisateurs"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants']);
        }
    }

    public function notifyAllUsers($title, $message, $url = "#", $type = "INFO", $sendEmail = false)
    {
        $dbh = database::sharedInstance();

        // 1. Récupérer tous les utilisateurs
        $users = $dbh->query("SELECT id, UID, email FROM accounts WHERE validate = 1", 2);

        // 2. Récupérer les abonnements push
        $subs = $dbh->query("SELECT user_id, subscription_json FROM push_subscriptions", 2);

        // Table d'association [userId => subscription]
        $subscriptions = [];
        foreach ($subs as $s) {
            $subscriptions[$s['user_id']] = $s['subscription_json'];
        }

        // --- Envoi interne
        foreach ($users as $u) {
            $notif = new notifications();
            $notif->setUserId($u['UID'])
                ->setTitle($title)
                ->setMessage($message)
                ->setUrl($url)
                ->setType($type)
                ->create();

            // --- Envoi email si demandé
            if ($sendEmail && !empty($u['email'])) {
                $utils = new utils();
                $mailBody = "<h3>$title</h3><p>$message</p><p><a href='$url'>Voir plus</a></p>";
                $utils->sendSMTPMail($u['email'], "Nouvelle notification", $mailBody);
            }
        }

        // --- Envoi Push
        if (!empty($subscriptions)) {
            $auth = [
                'VAPID' => [
                    'subject'    => 'mailto:mansahpro@gmail.com',
                    'publicKey'  => VAPID_PUBLIC_KEY,
                    'privateKey' => VAPID_PRIVATE_KEY,
                ],
            ];
            $webPush = new \Minishlink\WebPush\WebPush($auth);

            foreach ($subscriptions as $userId => $subJson) {
                $subscription = \Minishlink\WebPush\Subscription::create(json_decode($subJson, true));
                $webPush->queueNotification($subscription, json_encode([
                    "title" => $title,
                    "body"  => $message,
                    "url"   => $url
                ]));
            }

            foreach ($webPush->flush() as $report) {
                if (!$report->isSuccess()) {
                    error_log("Erreur push : " . $report->getReason());
                }
            }
        }

        return true;
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
            // Génération du login
            $_login = [
                'nom' => $account['nom'],
                'prenoms' => $account['prenom']
            ];

            $login = $this->generateLogin($_login);

            // Génération du mot de passe
            $passwords = $utils->generateAndHashPassword();
            $hashed = $passwords['hashed'];
            $plain = $passwords['plain'];

            // Création du compte utilisateur
            $_user = [
                'login' => $login,
                'email' => $account['email'],
                'password' => $hashed,
                'actif' => 1,
                'statut' => 0
            ];
            $user = $this->createNewUser($_user);

            //ajout dans le groupe_content_users
            $gc = new group_content_users();
            $gc->setGroupId(3);
            $gc->setUID($user['UID']);
            $gc->create();

            if (is_array($user) && isset($user['UID'])) {
                $acc->setId($idAcc);
                $acc->setUID($user['UID']);
                $acc->setValidate(0);
                $res = $acc->update();

                if (!$res) {
                    return ['success' => false, 'message' => "Échec de la validation du compte."];
                }

                // generation de token et de lien de validation
                $pr = new password_resets();
                $token = $this->generateToken();
                $pr->setUserId($user['UID']);
                $pr->setToken($token);
                $pr->setExpiresAt(date('Y-m-d H:i:s', strtotime('+1 day')));
                $pr->create();

                $link = BASE_URL . "reset-password.php?token=$token";

                // Envoi de norif (interne + mail)
                $this->notifyUser(
                    $user['UID'],
                    "Validation de votre compte",
                    "Votre compte a été validé ✅. Cliquez sur le lien reçu par mail pour définir votre mot de passe.",
                    "{$link}",
                    "SUCCESS",
                    true // on envoie aussi le mail déjà prévu
                );

                $logger->log([
                    'user_id' => $_SESSION['user']['id'],
                    'type' => 'USER_ACTION',
                    'label' => 'Validation de compte',
                    'target' => 'users',
                    'message' => "Le compte {$account['email']} a été validé par l'utilisateur " . $_SESSION['account']['nom'] .' '.$_SESSION['account']['prenom'],
                ]);
                return ['success' => true, 'message' => "Compte validé et utilisateur notifié."];
            } else {
                $this->notifyUser(
                    $user['UID'],
                    "Refus de votre inscription",
                    "Votre inscription a été refusée par l'INF.",
                    "#",
                    "ERROR",
                    true
                );

                $logger->log([
                    'user_id' => $_SESSION['user']['id'],
                    'type' => 'USER_ACTION',
                    'label' => 'Validation de compte',
                    'target' => 'users',
                    'message' => "Le compte {$account['email']} n'a pu être validé par l'utilisateur " . $_SESSION['account']['nom'] .' '.$_SESSION['account']['prenom'],
                ]);
                return ['success' => false, 'message' => "Échec de création de l'utilisateur."];
            }
        }

        if ($action === 'refuser') {
            $acc->setId($idAcc);
            $acc->setValidate(-1);
            $res = $acc->update();

            if (!$res) {
                return ['success' => false, 'message' => "Échec du refus du compte."];
            }

            // (optionnel) envoi de mail de refus
                $logger->log([
                    'user_id' => $_SESSION['user']['id'],
                    'type' => 'USER_ACTION',
                    'label' => 'Validation de compte',
                    'target' => 'users',
                    'message' => "Le compte {$account['email']} n'a pu être validé par l'utilisateur " . $_SESSION['account']['nom'] .' '.$_SESSION['account']['prenom'],
                ]);
                return ['success' => true, 'message' => "Compte refusé"];
        }

        return ['success' => false, 'message' => "Action inconnue."];
    }

    public function updateAccount($files, $_array = []){
        //    return $files;
        $acc = new accounts();
        $logger = new activities_log();

        // Vérification des données essentielles
        $accountId = intval($_array['id'] ?? 0);
        if (!$accountId) {
            return ['success' => false, 'message' => "Identifiant du compte manquant."];
        }

        // Lecture du compte
        $acc->setId($accountId);
        $existing = $acc->read();
        if (!$existing) {
            return ['success' => false, 'message' => "Compte introuvable."];
        }

        // Mise à jour des champs
        $acc->setNom(trim($_array['nom'] ?? ''));
        $acc->setPrenom(trim($_array['prenom'] ?? ''));
        $acc->setEmail(trim($_array['email'] ?? ''));
        $acc->setTelephone(trim($_array['telephone'] ?? ''));
        $acc->setGrade(trim($_array['grade'] ?? ''));
        $acc->setOLM(trim($_array['olm'] ?? ''));
        $acc->setDate_deb_formateur(trim($_array['date_deb_formateur'] ?? ''));

        // Gérer l'image de profil si fournie
        if (isset($files['avatar']) && $files['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . "/../__files/{$accountId}/profile/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileInfo = pathinfo($files['avatar']['name']);
            $extension = strtolower($fileInfo['extension']);
            $allowed = ['jpg', 'jpeg', 'png'];

            if (!in_array($extension, $allowed)) {
                return ['success' => false, 'message' => "Format de fichier non autorisé."];
            }

            $fileName = 'profile_' . $accountId . '.' . $extension;
            $destination = $uploadDir . $fileName;

            if (move_uploaded_file($files['avatar']['tmp_name'], $destination)) {
                $acc->setAvatar($fileName);
            } else {
                return ['success' => false, 'message' => "Échec de l'enregistrement de la photo de profil."];
            }
        }

        $result = $acc->update();
        if ($result) {
            $nomComplet = $_array['nom'].' '.$_array['prenom'];
            $_SESSION['account'] = $this->getAccountById($accountId);
            $logger->log([
                'user_id' => $_SESSION['user']['id'] ?? null,
                'type' => 'USER_ACTION',
                'label' => 'Modification de profil',
                'target' => 'accounts',
                'message' => "Mise à jour du profil de l'utilisateur $nomComplet #$accountId.",
            ]);
            return ['success' => true, 'message' => "Profil mis à jour avec succès."];
        } else {
            return ['success' => false, 'message' => "Aucune modification effectuée ou erreur de traitement."];
        }
    }

    public function createNewUser($_array = []){
        $user = new user();
        $logger = new activities_log();

        if (!(isset($_array['password']))) {
            $password = $this->generateAndHashPassword();
            $pass = $password['hashed'];
        }else {
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
        if ( $res > 0) {
            $tab = [];
            $tab['UID'] = $res;
            $tab['password'] = $pass;
            $logger->log([
                'user_id' => $_SESSION['user']['id'],
                'type' => 'USER_ACTION',
                'label' => 'Création d\'utilisateur',
                'target' => 'users',
                'message' => "L'utilisateur {$_array['login']} a été crée par l'utilisateur " . $_SESSION['account']['nom'] .' '.$_SESSION['account']['prenom'],
            ]);

            return $tab;
        } else {
            $logger->log([
                'user_id' => $_SESSION['user']['id'],
                'type' => 'USER_ACTION',
                'label' => 'Création d\'utilisateur',
                'target' => 'users',
                'message' => "L'utilisateur {$_array['login']} n'a pu être créer par l'utilisateur " . $_SESSION['account']['nom'] .' '.$_SESSION['account']['prenom'],
            ]);
            return "erreur d'enregistrement";
        }
        
    }

    public function userConnexion($_array = []){
        $user = new user();
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
            
            //RECUPERATION DES INFOS ACCOUNTS
            $acc = new accounts();
            $acc->setUID($UID);
            $acc_infos = $acc->readByUser();

            // RECUPERATION DES INFOS EN TANT QUE FORMATEUR
            $formateur = new formateurs();
            if (is_null($acc_infos)) {
                $tabForm = [];
            }else {
                $formateur->setIdAccount($acc_infos['id']);
                $tabForm = $formateur->readByAcc();
            }
            
            //recuperation du role via group_content_user
            $group = new group_content_users() ;
            $group->setUID($UID);
            $res = $group->readByUser();
            $role = $res['code'];

            $_SESSION['user'] = $res1;
            $_SESSION['user']['role'] = $role;
            $_SESSION['account'] = $acc_infos;
            $_SESSION['formateur'] = $tabForm; 

            $user->setLast_connexion( date("Y-m-d H:i:s") );
            $user->update();


            $logger->log([
                'user_id' => $UID ?? null,
                'type' => 'USER_ACTION',
                'label' => 'Connexion utilisateur',
                'target' => 'users',
                'message' => 'Connexion réussie avec email ' . $email,
            ]);

            return ['success' => true, 'message' => "✅ Vous êtes connectés !"];
        }else {
            $logger->log([
                'user_id' => $UID ?? null,
                'type' => 'USER_ACTION',
                'label' => 'Connexion utilisateur',
                'target' => 'users',
                'message' => 'Echec Connexion ' . $email,
            ]);
            return ['success' => false, 'message' => "❌ Identifiant ou Mot de passe incorrect"];
        }

    }

    public function updateLoginCredentials($data){

        $userId = intval($data['user_id']);
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';
        $login = trim($data['login'] ?? '');

        if (empty($userId) || empty($currentPassword) || empty($newPassword)) {
            return ['success' => false, 'message' => 'Tous les champs sont requis.'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'Les mots de passe ne correspondent pas.'];
        }

        // Récupérer l'utilisateur
        $userClass = new user();
        $userData = $userClass->read($userId);

        if (!$userData) {
            return ['success' => false, 'message' => 'Utilisateur introuvable.'];
        }

        $user = $userData;

        // Vérification du mot de passe actuel
        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Mot de passe actuel incorrect.'];
        }

        // Mise à jour
        $userClass->setId($userId);
        $userClass->setLogin($login);
        $userClass->setPassword(password_hash($newPassword, PASSWORD_DEFAULT));

        if ($userClass->update()) {
            return ['success' => true, 'message' => '✅ Identifiants mis à jour avec succès.'];
        }

        return ['success' => false, 'message' => 'Échec de la mise à jour.'];
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

    public function getExpiredUnvalidatedAccounts()
    {
        $pr = new password_resets();
        return $pr->getOldLink();
    }

    public function invalidateOldToken($user_id)
    {
        $sql = "UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0";
        return $this->dbh->exec($sql, [$user_id]);
    }

    public function getUserLogs($UID){
        $logs = new activities_log();
        return $logs->readLogByUser($UID);
    }

    public function getAllFormateursByGrade($grade){
        $acc = new accounts();
        $acc->setGrade($grade);
        return $acc->readByGrade();
    }

    public function createNewCourse($_files, $_array = []){
        $cours = new cours();
        $logger = new activities_log();
        $log = new cours_logs();
        $docs = new documents();
        $utils = new utils();
        $user = new user();

        $uid = intval($_array['uid']);
        $assistant = $this->dbh->protect_entry($_array['id_assistant']); 
        $theme = $this->dbh->protect_entry($_array['theme']);
        $title = $this->dbh->protect_entry($_array['title']);
        $type_formation = $this->dbh->protect_entry($_array['type_formation']);
        $lieu = $this->dbh->protect_entry($_array['lieu']);
        $date = $this->dbh->protect_entry($_array['date_cours']);
        $olm = $this->dbh->protect_entry($_array['olm']);
        $duree = floatval($_array['duree_heure']);
        $code = $this->generateCoursCode($uid);

        // --- Gestion OLM personnalisé ou existant ---
        if ($olm === "autre") {
            $olm_autre = trim($_array['olm_autre'] ?? '');
            $olmInfo = $this->formatOLM($olm_autre);

            $nomOlm  = $olmInfo['nom'];
            $paysOlm = $olmInfo['pays'];
            $codeOlm = $olmInfo['code'];

            $olmObj = new olms();
            $existing = $olmObj->readByCode($codeOlm);

            if ($existing) {
                $olmCode = $existing['code'];
            } else {
                $olmObj->setCode($codeOlm);
                $olmObj->setNom($nomOlm);
                $olmObj->setPays($paysOlm);
                $olmObj->create();
                $olmCode = $codeOlm;
            }
        } else {
            $olmCode = $olm;
        }

        // --- Création du cours ---
        $cours->setUID($uid);
        $cours->setUid_assistant($assistant);
        $cours->setCodeCours($code);
        $cours->setTitle($title);
        $cours->setTheme($theme);
        $cours->setTypeCours($type_formation);
        $cours->setLieu($lieu);
        $cours->setDateCours($date);
        $cours->setDureeHeure($duree);
        $cours->setOlm($olmCode);

        // 👇 Ajout du statut initial
        $cours->setStatus(1); // 1 = DRAFT dans la table cours_status

        $coursId = $cours->create();
        if (!$coursId) {
            return ['success' => false, 'message' => "❌ Échec de création du cours, veuillez réessayer."];
        }

        // --- Gestion des fichiers uploadés ---
        $uploadDirRel = '/__files/' . basename($uid) . '/docs/cours/' . basename($coursId) . '/';
        $baseDir = $_SERVER['DOCUMENT_ROOT'] . $uploadDirRel;
        if (!file_exists($baseDir)) {
            mkdir($baseDir, 0775, true);
        }

        $allowedExt = ['pdf', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'];
        $maxSize = 5 * 1024 * 1024; // 5 Mo

        if (!empty($_files['documents']['name'][0])) {
            foreach ($_files['documents']['name'] as $index => $filename) {
                $tmpPath = $_files['documents']['tmp_name'][$index];
                $size = $_files['documents']['size'][$index];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowedExt)) continue;
                if ($size > $maxSize) continue;

                $safeName = $utils->slugify(pathinfo($filename, PATHINFO_FILENAME));
                $newFileName = date('Ymd_His') . "_$safeName.$ext";
                $targetPathAbs = $baseDir . $newFileName;
                $targetPathRel = $uploadDirRel . $newFileName;

                $typeDoc = in_array($ext, ['png', 'jpg', 'jpeg']) ? 'photo' : 'support';

                if (move_uploaded_file($tmpPath, $targetPathAbs)) {
                    $docs->setIdCours($coursId);
                    $docs->setNomFichier($newFileName);
                    $docs->setUrlFichier($targetPathRel);
                    $docs->setTypeDocument($typeDoc);
                    $docs->create();
                }
            }
        }

        // --- Journalisation ---
        $account = $this->getAccountByUID($uid);
        $nomComplet = $account['nom'] . ' ' . $account['prenom'];

        $logger->log([
            'user_id' => $uid,
            'type' => 'INFO',
            'label' => 'Création de cours',
            'target' => 'cours',
            'message' => "Création du cours #$code sur le thème \"$theme\" par le formateur $nomComplet (#" . $account['id'] . ").",
        ]);

        $log->setIdCours($coursId);
        $log->setUserId($uid);
        $log->setAction('création');
        $log->setDetails("Cours sur le thème \"$theme\" à $lieu déclaré pour le $date. Statut initial : Brouillon (DRAFT).");
        $log->create();

        if ($coursId) {
            // ✅ Notif pour INF
            $this->notifyMany(
                [1,2,3],
                "Nouveau cours en attente",
                "Un nouveau cours « {$title} » a été créé et attend validation.",
                "index.php?page=this-course&id={$coursId}",
                "INFO",
                true
            );

            // ✅ Notif assistant
            if (!empty($assistant)) {
                $this->notifyUser(
                    $assistant,
                    "Vous êtes assistant sur le cours « {$title} »",
                    "Vous avez été choisi comme assistant pour le cours « {$title} » par {$nomComplet}.",
                    "index.php?page=this-course&id={$coursId}",
                    "SUCCESS",
                    true
                );
            }
        }

        // --- notifications mail ADMIN ---
        // $link = BASE_URL . "index.php?page=this-course&id=$coursId";
        // $message = "Bonjour Cher Admin,\n\n";
        // $message .= "Le formateur $nomComplet vient de déclarer un nouveau cours.\n";
        // $message .= "Statut initial : Brouillon (DRAFT).\n";
        // $message .= "Cliquez sur le lien ci-dessous pour le valider :\n$link\n\n";
        // $message .= "Merci pour votre disponibilité.\n-- INF JCI-CI --";

        // $emails = [ADMIN1_EMAIL, ADMIN2_EMAIL, SYSADMIN_EMAIL];
        // $utils->sendSMTPMail(
        //     $emails,
        //     // SYSADMIN_EMAIL,
        //     'NOUVEAU COURS - Validation du cours #' . $code,
        //     nl2br($message)
        // );

        // // ✅ Notif s'il y a Assitant
        // if (isset($assistant)) {
        //     $link = BASE_URL . "index.php?page=this-course&id=$coursId";
        //     $message = "Bonjour Formateur,\n\n";
        //     $message .= "Le formateur $nomComplet vous a choisi comme Formateur Assistant pour son prochain cours.\n";
        //     $message .= "Cliquez sur le lien ci-dessous pour voir le détail :\n$link\n\n";
        //     $message .= "Merci pour votre disponibilité.\n-- INF JCI-CI --";

        //     $assist = $this->getAccountByUID($assistant);
        //     $mail = $assist['email'];
        //     $utils->sendSMTPMail(
        //         $mail,
        //         'NOUVEAU COURS - Formateur Assistant du cours #' . $code,
        //         nl2br($message)
        //     );
        // }


        return [
            'success' => true,
            'message' => "✅ Votre cours a été déclaré avec succès.<br>Statut actuel : Brouillon.<br>Vous recevrez un e-mail après sa validation par l'INF JCI-CI."
        ];
    }

    public function getCourse($idCours){
        $cours = new cours();
        $course = $cours->read($idCours);
        return $course;
    }

    public function getAllDocsByCours($idCours){
        $cours = new cours();
        $doc = new documents();

        return $doc->read($idCours);
    }

    public function courseValidation($_array = []) {
        $cours = new cours();
        $docs = new documents();
        $coursLog = new cours_logs();
        $utils = new utils();
        $tokens = new app_tokens(); // Nouvelle classe de gestion des tokens
        $jeton = $this->generateToken();

        $coursId = (int) $_array['cours_id'];
        $valide = (int) $_array['valide'];
        $userId = $_SESSION['user']['id'];

        // Récupération infos du cours
        $course = $cours->read($coursId);
        if (!$course) {
            return ['success' => false, 'message' => "Cours introuvable."];
        }

        $courseCreator = $course['UID'];
        $account = $this->getAccountByUID($course['UID']);
        $nomComplet = "{$account['prenom']} {$account['nom']}";


        // Préparation log
        $coursLog->setIdCours($coursId);
        $coursLog->setUserId($userId);

        $attachments = [];

        // --- Cas cours validé ---
        if ($valide === 1) {
            // Mise à jour du statut du cours
            $res = $this->changeCourseStatus($coursId, $userId);

            if ($res['success'] == false) {
                return ['success' => false, 'message' => $res['message']];
            }


            $coursLog->setAction($this->dbh->protect_entry('Validation'));
            $coursLog->setDetails($this->dbh->protect_entry("Cours validé par l'INF."));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+48 hours'));

            // 1️⃣ Génération du token (48h de validité)
            $tokens->setToken($this->dbh->protect_entry($jeton));
            $tokens->setType($this->dbh->protect_entry('course_evaluation')); // type
            $tokens->setEntityId($coursId); // entity_id
            $tokens->setEntityTable($this->dbh->protect_entry('cours')); // entity_table
            $tokens->setExpiresAt($expiresAt) ; // expiration
            $tokens->SetCreatedBy($userId); // created_by

            $token = $tokens->create();

            // 2️⃣ Génération du QR code
            require_once($_SERVER['DOCUMENT_ROOT'] . '/assets/vendors/phpqrcode/qrlib.php');
            // Dossier où stocker le QR code
            $dirPath = '__files/' . basename($courseCreator) . '/docs/cours/' . basename($coursId) . '/';
            $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $dirPath;
            // Création récursive du dossier si besoin
            if (!is_dir($baseDir)) {
                if (!mkdir($baseDir, 0775, true)) {
                    die("❌ Erreur : impossible de créer le dossier $baseDir");
                }
            }

            // Vérification des droits d'écriture
            if (!is_writable($baseDir)) {
                die("❌ Erreur : le dossier $baseDir n'est pas accessible en écriture.");
            }

            // Lien à encoder
            $evaluationLink = BASE_URL . "evaluation.php?token={$token}";

            // Chemin absolu et web du fichier QR code
            $qrCodeFileName = 'qr_evaluation.png';
            $qrCodeAbsolutePath = $baseDir . $qrCodeFileName;
            $qrCodeWebPath = '/' . $dirPath . $qrCodeFileName;

            // Si un QR code existe déjà, on le supprime
            if (file_exists($qrCodeAbsolutePath)) {
                unlink($qrCodeAbsolutePath);
            }

            // Génération du QR code
            QRcode::png($evaluationLink, $qrCodeAbsolutePath, QR_ECLEVEL_H, 6);

            // 🔹 Compression du PNG pour optimiser le poids
            $image = imagecreatefrompng($qrCodeAbsolutePath);
            imagepng($image, $qrCodeAbsolutePath, 9); // Qualité max mais PNG compressé
            imagedestroy($image);
            $attachments[] = $qrCodeAbsolutePath;


            //ENREGISTREMENT DANS LA BD
            $cours->setId($coursId);
            $cours->setQrCode($qrCodeWebPath);
            $cours->update();
            
            $docs->setIdCours($coursId);
            $docs->setNomFichier($qrCodeFileName);
            $docs->setUrlFichier($qrCodeWebPath);
            $docs->setTypeDocument('qr_code');
            $docs->create();


            // ✅ Notif au formateur
            $this->notifyUser(
                $course['UID'],
                "Cours validé ✅",
                "Votre cours « {$course['title']} » a été validé par l'INF.",
                "index.php?page=this-course&id={$coursId}",
                "SUCCESS",
                true
            );

        // --- Cas cours refusé ---
        } else {
            // Mise à jour du statut du cours
            $cours->setId($coursId);
            $cours->setStatus($valide);
            $res = $cours->update();

            if (!$res) {
                return ['success' => false, 'message' => "Échec lors de la mise à jour du cours."];
            }


            $motif = trim($_array['motif'] ?? '');
            if (empty($motif)) {
                return ['success' => false, 'message' => "Le motif de refus est requis."];
            }

            $coursLog->setAction($this->dbh->protect_entry('Refus'));
            $coursLog->setDetails($this->dbh->protect_entry($motif));

            // ✅ Notif au formateur
            $this->notifyUser(
                $course['UID'],
                "Cours refusé ❌",
                "Votre cours « {$course['title']} » a été refusé pour motif : {$motif}.",
                "index.php?page=this-course&id={$coursId}",
                "ERROR",
                true
            );
        }

        // 4️⃣ Enregistrement du log
        $coursLog->create();

        return ['success' => true, 'message' => "Traitement effectué avec succès."];
    }

    public function courseFileDelete($fileId){
        $docs = new documents();
        $file = $docs->readByFile($fileId);

        if ($file) {
            // Construire chemin absolu
            $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $file['url_fichier'];

            if (file_exists($absolutePath)) {
                unlink($absolutePath);
                $docs->setId($fileId);
                $docs->setDeleted(1);
                $docs->update();

                echo json_encode(['success' => true, 'message' => "Fichier supprimé."]);
            } else {
                echo json_encode(['success' => false, 'message' => "Fichier introuvable sur le serveur."]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => "Fichier inexistant en base."]);
        }
        exit;
    }

    public function courseEdition($_files, $_array = []) {
        $cours   = new cours();
        $logger  = new activities_log();
        $log     = new cours_logs();
        $acc     = new accounts();
        $utils   = new utils();
        $doc     = new documents();

        $coursId = $this->dbh->protect_entry($_array['cours_id']); 
        $userId  = $_SESSION['user']['id'];
        $olm = $_array['olm'];
        
        // Récupération du cours
        $currentCours = $cours->read($coursId);
        if (!$currentCours) {
            return ['success' => false, 'message' => "❌ Cours introuvable."];
        }

        // Gestion du champ OLM
        // Gestion OLM personnalisé
        if ($olm === "autre") {
            $olm_autre = trim($_array['olm_autre'] ?? '');
            $olmInfo = $this->formatOLM($olm_autre);


            $nomOlm  = $olmInfo['nom'];
            $paysOlm = $olmInfo['pays'];
            $codeOlm = $olmInfo['code'];


            // Vérifier si déjà existant
            $olmObj = new olms();
            $existing = $olmObj->readByCode($codeOlm);

            if ($existing) {
                $olmCode = $existing['code'];
            } else {
                // Créer la nouvelle OLM
                $olmObj->setCode($codeOlm);
                $olmObj->setNom($nomOlm);
                $olmObj->setPays($paysOlm);
                $olmObj->create();
                $olmCode = $codeOlm;
            }
        } else {
            // Cas standard : sélection d'une OLM existante
            $olmCode = $olm; // $olm contient le code de l'olm choisie dans le <select>
        }

        // Récupération des champs du formulaire
        $assistant      = $this->dbh->protect_entry($_array['id_assistant']); 
        $theme           = $this->dbh->protect_entry($_array['theme']);
        $title           = $this->dbh->protect_entry($_array['title']);
        $code            = $this->dbh->protect_entry($_array['code_cours']);
        $type_formation  = $this->dbh->protect_entry($_array['type_formation']);
        $lieu            = $this->dbh->protect_entry($_array['lieu']);
        $date            = $this->dbh->protect_entry($_array['date_cours']);
        $duree           = $this->dbh->protect_entry($_array['duree_heure']);

        // Vérifier s'il y a une modification dans les champs
        $champsModifies = (
            $theme          !== $currentCours['theme'] ||
            $assistant      !== $currentCours['uid_assistant'] ||
            $title          !== $currentCours['title'] ||
            $type_formation !== $currentCours['type_cours'] ||
            $lieu           !== $currentCours['lieu'] ||
            $date           !== $currentCours['date_cours'] ||
            $duree          !=  $currentCours['duree_heure'] ||
            $olm            !== $currentCours['olm']
        );

        // Vérifier si des fichiers ont été ajoutés
        $nouveauxFichiers = (!empty($_files['new_files']['name'][0]));

        // Vérifier si des fichiers ont été supprimés (champ file_delete_ids venant du formulaire)
        $fichiersSupprimes = (!empty($_array['file_delete_ids']));

        // Si aucun changement dans les champs et aucun ajout/suppression de fichiers
        if (!$champsModifies && !$nouveauxFichiers && !$fichiersSupprimes) {
            return [
                'success' => false,
                'message' => "ℹ️ Aucune modification détectée."
            ];
        }

        // Mise à jour des champs si modifiés
        if ($champsModifies) {
            $cours->setId($coursId);
            $cours->setTitle($title);
            $cours->setUid_assistant($assistant);
            $cours->setTypeCours($type_formation);
            $cours->setDateCours($date);
            $cours->setTheme($theme);
            $cours->setOlm($olm);
            $cours->setLieu($lieu);
            $cours->setDureeHeure($duree);
            $cours->update();
        }

        // Suppression de fichiers
        if ($fichiersSupprimes) {
            $ids = explode(',', $_array['file_delete_ids']);
            foreach ($ids as $fileId) {
                $file = $doc->readByFile($fileId);
                if ($file && file_exists($_SERVER['DOCUMENT_ROOT'] . $file['url_fichier'])) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . $file['url_fichier']);
                    $doc->setId($fileId);
                    $doc->setDeleted(1);
                    $doc->update();
                }
            }
        }

        // Ajout de nouveaux fichiers
        $uid = $currentCours['UID'];
        if ($nouveauxFichiers) {
            // $uploadDir = "__files/{$currentCours['account_id']}/docs/cours/{$coursId}/";
            $uploadDirRel = '/__files/' . basename($uid) . '/docs/cours/' . basename($coursId) . '/';
            $baseDir = $_SERVER['DOCUMENT_ROOT'] . $uploadDirRel;
            if (!file_exists($baseDir)) {
                mkdir($baseDir, 0775, true);
            }


            $allowedExt = ['pdf', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'];
            $maxSize = 5 * 1024 * 1024; // 5 Mo

            foreach ($_files['new_files']['name'] as $index => $filename) {
                $tmpPath = $_files['new_files']['tmp_name'][$index];
                $size = $_files['new_files']['size'][$index];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowedExt)) continue;
                if ($size > $maxSize) continue;

                $safeName = $utils->slugify(pathinfo($filename, PATHINFO_FILENAME));
                $newFileName = date('Ymd_His') . "_$safeName.$ext";
                $targetPathAbs = $baseDir . $newFileName;
                $targetPathRel = $uploadDirRel . $newFileName;

                $typeDoc = in_array($ext, ['png', 'jpg', 'jpeg']) ? 'photo' : 'support';

                if (move_uploaded_file($tmpPath, $targetPathAbs)) {
                    $doc->setIdCours($coursId);
                    $doc->setNomFichier($newFileName);
                    $doc->setUrlFichier($targetPathRel);
                    $doc->setTypeDocument($typeDoc);
                    $doc->create();
                }
            }
        }

        // Journalisation
        $account    = $this->getAccountByUID($userId);
        $nomComplet = $account['nom'] . ' ' . $account['prenom'];

        $logger->log([
            'user_id' => $userId,
            'type'    => 'INFO',
            'label'   => 'Modification de cours',
            'target'  => 'cours',
            'message' => "Modification du cours #$coursId sur le thème \"$theme\" par le formateur $nomComplet (#" . $account['id'] . ").",
        ]);

        $log->setIdCours($coursId);
        $log->setUserId($userId);
        $log->setAction('modification');
        $log->setDetails("Le cours a été modifié (thème : $theme).");
        $log->create();

        // Envoi d'email
        $link = BASE_URL . "index.php?page=this-course&id=$coursId";
        $message = "Bonjour Cher Admin,\n";
        $message .= "Le formateur {$account['prenom']} {$account['nom']} a apporté des modifications au cours {$code}.\n";
        $message .= "Cliquez sur le lien ci-dessous pour voir le détail:\n$link\n";
        $message .= "Merci pour votre disponibilité.\n-- INF JCI-CI --";

        $emails = [ADMIN1_EMAIL, ADMIN2_EMAIL, SYSADMIN_EMAIL];
        $utils->sendSMTPMail(
            $emails,
            'COURS MODIFIÉ - Validation du cours #' . $code,
            nl2br($message)
        );

        return [
            'success' => true,
            'message' => "✅ Les modifications ont été enregistrées avec succès."
        ];
    }

    public function addFiles($_files, $_array = []){
        $cours = new cours();
        $doc     = new documents();
        $utils = new utils();
        $res = 0;
        $coursId = (int) $_array['idCours'];
        $userId = (int) $_array['userId'];

        // $uploadDir = "__files/{$currentCours['account_id']}/docs/cours/{$coursId}/";
        $uploadDirRel = '/__files/' . basename($userId) . '/docs/cours/' . basename($coursId) . '/';
        $baseDir = $_SERVER['DOCUMENT_ROOT'] . $uploadDirRel;
        if (!file_exists($baseDir)) {
            mkdir($baseDir, 0775, true);
        }


        $allowedExt = ['pdf', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'];
        $maxSize = 5 * 1024 * 1024; // 5 Mo

        foreach ($_files['documents']['name'] as $index => $filename) {
            $tmpPath = $_files['documents']['tmp_name'][$index];
            $size = $_files['documents']['size'][$index];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt)) continue;
            if ($size > $maxSize) continue;

            $safeName = $utils->slugify(pathinfo($filename, PATHINFO_FILENAME));
            $newFileName = date('Ymd_His') . "_$safeName.$ext";
            $targetPathAbs = $baseDir . $newFileName;
            $targetPathRel = $uploadDirRel . $newFileName;

            $typeDoc = in_array($ext, ['png', 'jpg', 'jpeg']) ? 'photo' : 'support';

            if (move_uploaded_file($tmpPath, $targetPathAbs)) {
                $doc->setIdCours($coursId);
                $doc->setNomFichier($newFileName);
                $doc->setUrlFichier($targetPathRel);
                $doc->setTypeDocument($typeDoc);
                $res = $doc->create();
            }
        }

        if ($res != 0) {
            // Journalisation
            $log     = new cours_logs();
            $logger = new activities_log();
            $account    = $this->getAccountByUID($userId);
            $nomComplet = $account['nom'] . ' ' . $account['prenom'];

            $logger->log([
                'user_id' => $userId,
                'type'    => 'INFO',
                'label'   => 'Ajout de nouveaux fichiers',
                'target'  => 'cours',
                'message' => "Ajout de nouveaux fichiers au cours #$coursId par le formateur $nomComplet (#" . $account['id'] . ").",
            ]);

            $log->setIdCours($coursId);
            $log->setUserId($userId);
            $log->setAction('modification');
            $log->setDetails("De nouveaux fichiers ont été ajoutés au cours #$coursId).");
            $log->create();

            return [
                'success' => true,
                'message' => "✅ Les fichiers ont été enregistrés avec succès."
            ];
        }else {
            return [
                'success' => false,
                'message' => "Echec de l'ajout des fichiers, veuillez recommencer"
            ];
        }

    }


    public function getCoursByUser($user_id){
        $cours = new cours();
        $cours->setUID($user_id);
        $cours->setUid_assistant($user_id);
        return $cours->readByUserOrAssistant();
    }

    public function getCourseByAssistant($uid){
        $cours = new cours();
        $cours->setUid_assistant($uid);

        return $cours->readByAssistant();
    }
    

    public function getAllCourses(){
        $cours = new cours();
        return $cours->readAll();
    }

    public function getLogsByCoursId($coursId){
        $cours = new cours_logs();
        $cours->setIdCours($coursId);

        return $cours->getLogsByCours();

    }

    public function getAllStats() {
        $user = new user();
        $cours = new cours();
        $logs = new activities_log();

        return [
            'total_pending_accounts' => $user->countPending(),
            'total_validated_accounts' => $user->countValidated(),
            'cours_pending' => $cours->countPending(),
            'sessions_programmed' => $cours->countSessionsProgrammed(),
            'recent_logs' => $logs->getRecentLogs()
        ];
    }

    public function courseEvaluation($_array = []){
        $eval = new evaluation_submissions();

        $course = new cours();
        $cours  = $course->read($_array['id_cours']);

        // Cas 1 : Évaluation par le formateur principal pour l'assistant
        if (!empty($_array['assistant_id'])) {
            $assistantId = intval($_array['assistant_id']);
            $hashUnique  = sha1($_array['id_cours'].$assistantId);

            $eval->setHash_unique($hashUnique);

            // Vérifie unicité (pas deux évaluations de l'assistant pour le même cours)
            if ($eval->getHash() !== null) {
                return ["success" => false, "message" => "Vous avez déjà évalué cet assistant."];
            }

            $uidTarget = $assistantId; // la cible de l'évaluation = l'assistant
            $token     = null;
        }
        // Cas 2 : Évaluation classique (participants via token)
        else {
            $eval->setHash_unique($_array['hash']);

            // Vérifie unicité
            if ($eval->getHash() !== null) {
                return ["success" => false, "message" => "Vous avez déjà évalué ce cours."];
            }

            $uidTarget = $cours['UID']; // la cible = le formateur principal
            $token     = $_array['token'];
        }

        // Récupération et validation des notes
        $notes = $_array['notes'] ?? [];
        if (count($notes) === 0) {
            return ["success" => false, "message" => "Veuillez noter tous les critères."];
        }

        $totalMax     = count($notes) * 5;
        $totalObtained = array_sum($notes);
        $noteGlobale   = round(($totalObtained / $totalMax) * 100, 2);

        $pointsForts        = trim($_array['points_forts'] ?? '');
        $pointsAmeliorations = trim($_array['points_ameliorations'] ?? '');

        // Remplissage entité
        $eval->setId_cours($_array['id_cours']);
        $eval->setUserId($uidTarget); // cible de l'évaluation
        $eval->setToken($token);
        $eval->setNotes_json(json_encode($notes, JSON_UNESCAPED_UNICODE));
        $eval->setNote_globale($noteGlobale);
        $eval->setPoints_forts($pointsForts);
        $eval->setPoints_ameliorations($pointsAmeliorations);
        $eval->setIp_address($_array['ip']);
        $eval->setUser_agent($_array['ua']);

        $resEval = $eval->create();

        if ($resEval) {
            return [
                "success" => true,
                "message" => !empty($_array['assistant_id']) 
                    ? "✅ Merci, l'évaluation de l'assistant a été enregistrée !" 
                    : "✅ Merci pour votre évaluation du cours !"
            ];
        }

        return ["success" => false, "message" => "Erreur lors de l'enregistrement."];
    }

    public function courseEvaluationResults($id_cours, $userId = null) {
        $courseEval = new evaluation_submissions();
        $courseEval->setId_cours($id_cours);

        // 1. Récupérer toutes les évaluations du cours
        $evaluations = $courseEval->read($id_cours);

        if (!$evaluations || count($evaluations) === 0) {
            return [
                'success' => true,
                'data' => []
            ];
        }

        // Structure par formateur (UID)
        $resultsByUser = [];

        foreach ($evaluations as $eval) {
            $uid = intval($eval['user_id']);
            if (!isset($resultsByUser[$uid])) {
                $resultsByUser[$uid] = [
                    'criteriaSums'   => [],
                    'criteriaCounts' => [],
                    'sumGlobal'      => 0,
                    'countGlobal'    => 0,
                    'observations'   => []
                ];
            }

            // Notes critère par critère
            $notes = json_decode($eval['notes_json'], true);
            if ($notes && is_array($notes)) {
                foreach ($notes as $critere => $note) {
                    if (!isset($resultsByUser[$uid]['criteriaSums'][$critere])) {
                        $resultsByUser[$uid]['criteriaSums'][$critere] = 0;
                        $resultsByUser[$uid]['criteriaCounts'][$critere] = 0;
                    }
                    $resultsByUser[$uid]['criteriaSums'][$critere]   += floatval($note);
                    $resultsByUser[$uid]['criteriaCounts'][$critere] += 1;
                }
            }

            // Note globale
            $resultsByUser[$uid]['sumGlobal']   += floatval($eval['note_globale']);
            $resultsByUser[$uid]['countGlobal'] += 1;

            // Observations
            if (!empty($eval['points_forts'])) {
                $resultsByUser[$uid]['observations'][] = [
                    'type'  => 'fort',
                    'text'  => $eval['points_forts'],
                    'date'  => $eval['date_soumission']
                ];
            }
            if (!empty($eval['points_ameliorations'])) {
                $resultsByUser[$uid]['observations'][] = [
                    'type'  => 'amelioration',
                    'text'  => $eval['points_ameliorations'],
                    'date'  => $eval['date_soumission']
                ];
            }
        }

        // 2. Mise en forme finale
        $finalResults = [];
        foreach ($resultsByUser as $uid => $r) {
            $criteriaResults = [];
            foreach ($r['criteriaSums'] as $critere => $somme) {
                $criteriaResults[] = [
                    'critere' => $critere,
                    'average' => $r['criteriaCounts'][$critere] > 0 
                        ? round($somme / $r['criteriaCounts'][$critere], 2)
                        : 0
                ];
            }

            $globalAverage = $r['countGlobal'] > 0 
                ? round($r['sumGlobal'] / $r['countGlobal'], 2) 
                : 0;

            $finalResults[$uid] = [
                'global_average'     => $globalAverage,
                'participants_count' => $r['countGlobal'],
                'criteria'           => $criteriaResults,
                'observations'       => $r['observations']
            ];
        }

        // 3. Si on demande seulement pour un userId spécifique (assistant ou formateur)
        if ($userId !== null && isset($finalResults[$userId])) {
            return [
                'success' => true,
                'data' => $finalResults[$userId]
            ];
        }

        // Sinon on renvoie tout (cas admin)
        return [
            'success' => true,
            'data' => $finalResults
        ];
    }


    public function hasAssistantEval($id_cours, $assistantId){
        $eval = new evaluation_submissions();
        $eval->setUserId($assistantId);
        $eval->setId_cours($id_cours);

        return $eval->readEvalByAssist();
    }

    public function computeEvaluationResults($coursId, $userId = null){
        $submissions = new evaluation_submissions();
        $results = new evaluation_results();

        // Charger toutes les soumissions liées au cours
        $subs = $submissions->read($coursId);

        if(!$subs || count($subs) === 0){
            return ['success'=>false, 'message'=>'Aucune évaluation trouvée.'];
        }

        // Calcul global
        $totalNotes = 0;
        $nb = 0;
        foreach($subs as $s){
            $totalNotes += floatval($s['note_globale']);
            $nb++;
        }

        $moyenne = $nb > 0 ? round($totalNotes / $nb, 2) : 0;

        // Vérifier si un résultat existe déjà
        $existing = $results->readForUser($coursId, $userId ?? 0);

        if($existing){
            $results->setId_cours($coursId)
                    ->setUser_id($userId ?? 0)
                    ->setMoyenne_globale($moyenne)
                    ->setTotal_participants($nb);
            $results->update();
        } else {
            $results->setId_cours($coursId)
                    ->setUser_id($userId ?? 0)
                    ->setMoyenne_globale($moyenne)
                    ->setTotal_participants($nb);
            $results->create();
        }

        return ['success'=>true, 'moyenne'=>$moyenne, 'total'=>$nb];
    }

    public function validateCourseEvaluation($_array = []) {
        $cours             = new cours();
        $coursStatus       = new cours_status();
        $evalSubmissions   = new evaluation_submissions();
        $evalResults       = new evaluation_results();
        $utils             = new utils();
        $account           = new accounts();

        $coursId = (int)($_array['cours_id'] ?? 0);
        $userId  = (int)($_array['userId'] ?? 0); // INF qui valide

        // 0) Contrôles de base
        $course = $cours->read($coursId);
        if (!$course) {
            return ['success' => false, 'message' => "❌ Cours introuvable."];
        }

        $currentStatusId = (int)($course['status'] ?? 0);
        $statusObj = $coursStatus->read($currentStatusId);
        if (!$statusObj) {
            return ['success' => false, 'message' => "❌ Statut actuel invalide."];
        }

        $evalReview = $coursStatus->readByCode('EVAL_REVIEW');
        if (!$evalReview) {
            return ['success' => false, 'message' => "❌ Statut EVAL_REVIEW introuvable."];
        }
        if ($currentStatusId !== (int)$evalReview['id']) {
            return [
                'success' => false,
                'message' => "⚠️ Ce cours n'est pas en attente de validation (statut actuel : {$statusObj['libelle']})."
            ];
        }

        // 1) Récup soumissions & calcul par évalué
        $submissions = $evalSubmissions->read($coursId); // tableau des lignes pour ce cours
        if (!$submissions || count($submissions) === 0) {
            return ['success' => false, 'message' => "❌ Aucune évaluation soumise pour ce cours."];
        }

        $notesByUser = []; // uid => [total100, count]
        foreach ($submissions as $s) {
            $uid = (int)$s['user_id'];
            $note100 = (float)$s['note_globale']; // sur 100
            if (!isset($notesByUser[$uid])) {
                $notesByUser[$uid] = ['total100' => 0.0, 'count' => 0];
            }
            $notesByUser[$uid]['total100'] += $note100;
            $notesByUser[$uid]['count']++;
        }

        // 2) Enregistrer dans evaluation_results (moyenne SUR 20)
        foreach ($notesByUser as $uid => $data) {
            $moyenne100 = $data['count'] > 0 ? ($data['total100'] / $data['count']) : 0.0;
            $moyenne20  = round($moyenne100 * 0.20, 2); // conversion /100 -> /20

            $existing = $evalResults->readForUser($coursId, $uid);
            $evalResults->setId_cours($coursId)
                        ->setUser_id($uid)
                        ->setMoyenne_globale($moyenne20) // ★ SUR 20
                        ->setTotal_participants((int)$data['count']);

            if ($existing) {
                $evalResults->update();
            } else {
                $evalResults->create();
            }
        }

        // 3) Clôture (statut suivant)
        $result = $this->changeCourseStatus(
            $coursId,
            $userId,
            'next',
            "Validation finale de l'évaluation par l'INF"
        );
        if (!$result['success']) {
            return $result;
        }

        // 4) Stats formateur / assistant (voir implémentation accounts::incrementStats plus bas)
        $nbHeures = (float)($course['duree_heure'] ?? 0);

        $account->incrementStats((int)$course['UID'], 1, $nbHeures);
        if (!empty($course['uid_assistant'])) {
            $account->incrementStats((int)$course['uid_assistant'], 1, $nbHeures);
        }

        // 5) Notifications
        $targets = [(int)$course['UID']];
        if (!empty($course['uid_assistant'])) {
            $targets[] = (int)$course['uid_assistant'];
        }

        foreach ($targets as $uid) {
            $this->notifyUser(
                $uid,
                "Évaluation validée ✅",
                "Le cours « {$course['title']} » a été validé et clôturé. ➕ Votre compte a été mis à jour avec {$nbHeures} heures.",
                "index.php?page=this-course&id={$course['id']}&tab=evaluation",
                "SUCCESS",
                true
            );
        }

        return [
            'success' => true,
            'message' => "✅ Évaluation validée (moyennes enregistrées sur 20), compteurs mis à jour et cours clôturé.",
            'new_status' => $result['new_status'],
            'nb_heures' => $nbHeures,
            'evaluations' => $notesByUser
        ];
    }

}