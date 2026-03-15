<?php
if (!defined('APP_STARTED')) {
    die("Accès non autorisé.");
}

// ---------------------------------------------
// Chargement du fichier .env
// ---------------------------------------------
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);

        // Ignorer les commentaires et les lignes mal formées
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }
    }
}

// ---------------------------------------------
// Définir des constantes d’environnement
// ---------------------------------------------
define("APP_ENV", $_ENV['APP_ENV'] ?? 'production');
define("APP_NAME", $_ENV['APP_NAME'] ?? 'INF Trainers');
define("APP_DEBUG", ($_ENV['APP_DEBUG'] ?? 'false') === 'true');

// ---------------------------------------------
// Calcul dynamique de l'URL de base
// ---------------------------------------------
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['SCRIPT_NAME']); // / ou /monprojet
$basePath = rtrim($scriptPath, '/') . '/';
$defaultBaseUrl = $scheme . '://' . $host . $basePath;

// ---------------------------------------------
// Définition de BASE_URL avec fallback sur .env
// ---------------------------------------------
define('BASE_URL', rtrim($_ENV['BASE_PATH'] ?? $defaultBaseUrl, '/') . '/');

// ---------------------------------------------
// Définition des chemins d'actifs
// ---------------------------------------------
define('ASSETS', BASE_URL . 'assets');
define('CSS', ASSETS . '/css');
define('JS', ASSETS . '/js');
define('IMG', ASSETS . '/images');
define('VENDORS', ASSETS . '/vendors');

// ---------------------------------------------
// Définition des données de la BD
// ---------------------------------------------
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);


// ---------------------------------------------
// Définition des autres accès
// ---------------------------------------------
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME']);
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD']);
define('CRITERES', $criteres = [
        "Maîtrise du contenu" => [
            "Connaissance approfondie du sujet" => "contenu_connaissance",
            "Capacité à répondre aux questions avec précision" => "contenu_questions",
            "Utilisation d'exemples pertinents et actualisés" => "contenu_exemples",
            "Cohérence entre le contenu annoncé et délivré" => "contenu_coherence"
        ],
        "Pédagogie et méthodes" => [
            "Structuration logique et claire" => "pedagogie_structure",
            "Adaptation au niveau des participants" => "pedagogie_adaptation",
            "Utilisation de méthodes variées" => "pedagogie_methodes",
            "Capacité à simplifier les concepts" => "pedagogie_simplification",
            "Encouragement de la participation" => "pedagogie_participation"
        ],
        "Communication" => [
            "Clarté et fluidité de l'expression" => "com_clarte",
            "Vocabulaire adapté au public" => "com_vocabulaire",
            "Gestion du rythme" => "com_rythme",
            "Langage corporel et contact visuel" => "com_corporel",
            "Écoute active et reformulation" => "com_ecoute"
        ],
        "Gestion du groupe" => [
            "Climat positif et motivant" => "groupe_climat",
            "Maintien de l'attention" => "groupe_attention",
            "Gestion des comportements difficiles" => "groupe_comportement",
            "Respect du temps imparti" => "groupe_temps",
            "Implication équilibrée de tous" => "groupe_implication"
        ],
        "Supports et outils" => [
            "Qualité visuelle et pédagogique des supports" => "support_qualite",
            "Pertinence des supports" => "support_pertinence",
            "Maîtrise des outils techniques" => "support_maitrise",
            "Supports facilitant mémorisation et mise en pratique" => "support_memoire"
        ],
        "Impact et résultats" => [
            "Atteinte des objectifs pédagogiques" => "impact_objectifs",
            "Pertinence des compétences transmises" => "impact_competences",
            "Susciter un changement de pratiques" => "impact_changement",
            "Retours positifs des participants" => "impact_retours",
            "Évaluation des acquis" => "impact_acquis"
        ],
        "Qualités personnelles" => [
            "Enthousiasme et dynamisme" => "perso_enthousiasme",
            "Ouverture d'esprit et adaptabilité" => "perso_ouverture",
            "Patience et bienveillance" => "perso_patience"
        ]
    ]
);
define('ADMIN1_EMAIL', 'trebichristian@gmail.com');
define('ADMIN2_EMAIL', 'ndooffoo@gmail.com');
define('SYSADMIN_EMAIL', 'mansahpro@gmail.com');
define('TYPE_COURS', $typeCours = [
        "interne" => "Interne",
        "public" => "Public"
]);
// === VAPID KEYS (Web Push) ===
define("VAPID_PUBLIC_KEY",  "BNHyfFsSnPMSfz4A_AcsMqEX_NcGiJXkaOJjGll2_2f25Zc9UASrzevNSc3vi_PL5SiQQ18L7NGPlfFMXgSXGbc");
define("VAPID_PRIVATE_KEY", "9O3pHBE90TZ_cGUFlqwa86lYakNfpLimn9A0x26nkW0");

// Tu peux aussi ajouter le subject (contact mail obligatoire pour WebPush)
define("VAPID_SUBJECT", "mailto:mansahpro@gmail.com");
// ---------------------------------------------
// Chemin absolu du système de fichiers
// ---------------------------------------------
define('DOC_ROOT', $_SERVER['DOCUMENT_ROOT']);
