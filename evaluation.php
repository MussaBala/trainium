<?php
    if (!defined('APP_STARTED')) {
        define('APP_STARTED', true);
    }

    require_once($_SERVER['DOCUMENT_ROOT'] . '/__class/autoload.class.php');
    include_once($_SERVER['DOCUMENT_ROOT'] . '/config/config.php');

    $obj = new objects();

    $ip = $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'];

    $token   = $_GET['token'] ?? '';
    $dateCours = null;
    $uid     = $_GET['uid'] ?? null; // id assistant

    // Cas 1 : on a un token → évaluation participant
    if ($token) {
        $hash = sha1($token.$ip.$ua);
        $tokenData = $obj->getToken($token);

        if (!$tokenData) {
            die("<div class='alert alert-danger m-3 p-3 rounded'>Lien d'évaluation invalide ou expiré.</div>");
        }

        $id_cours = $tokenData['entity_id'];
        $cours = $obj->getCourse($id_cours);
        $dateCours = $cours['date_cours'];
        // Empêcher double soumission
        $verifyHash = $obj->verifyHash($hash);
        if (!is_null($verifyHash)) {
            die("<div class='alert alert-info m-3 p-3 rounded'>Vous avez déjà évalué ce cours. Merci 🙏</div>");
        }

        $mode = "participant";

    // Cas 2 : on a un uid → évaluation formateur principal sur l’assistant
    } elseif ($uid) {
        $id_cours = $_GET['idCours'];
        $assistantId = intval($uid);

        // Ici, pas de token → on suppose que l’admin ou le formateur principal est loggé
        $cours = $obj->getCourse($id_cours);
        if (!$cours) {
            die("<div class='alert alert-danger m-3 p-3 rounded'>Aucun cours associé à cet assistant.</div>");
        }
        $dateCours = $cours['date_cours'];


        $formateurPrincipal = $cours['UID'];

        // Vérifier si une évaluation existe déjà (le formateur principal ne peut en soumettre qu'une)
        $already = $obj->hasAssistantEval($id_cours, $assistantId);
        if ($already) {
            die("<div class='alert alert-info m-3 p-3 rounded'>Vous avez déjà évalué cet assistant pour ce cours.</div>");
        }

        $mode = "assistant";

        // Sinon → ni token ni uid → lien invalide
    } else {
        die("<div class='alert alert-danger m-3 p-3 rounded'>Lien d'évaluation invalide.</div>");
    }

    $criteres = [
        "Organisation",
        "Pertinence du contenu",
        "Clarté du formateur",
        "Participation",
        "Utilité"
    ];

    $dateCours = strtotime($dateCours);
    $todayTimestamp = strtotime('today');
    if ($dateCours > $todayTimestamp) {
        die("<div class='alert alert-danger m-3 p-3 rounded'>Revenez à la date du Cours.</div>");
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluation du cours</title>
        <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
      
    <!-- Material Design Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Themify Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/themify-icons@1.0.1/css/themify-icons.css">

    <link rel="stylesheet" href="<?= $_SERVER['DOCUMENT_ROOT'] ?>/assets/vendors/css/vendor.bundle.base.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha512-SfZ5pNdZ8s8+w3BFe7Axd5Um0rWgqv5UpJpY8x6DBsFaHF1FJQApNsvVgJsVrKSkdbnLdD1TEXrS5x0z8diqUg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- endinject -->
    
    <!-- Plugin css for this page -->    
    <!-- Bootstrap Datepicker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css" integrity="sha512-mQYu2QvwN0XpG2U28AX7h+gX7YF5Q8yfdLByVulBieclT2ZbNhtyRUlHx02Av8Fw4aMyjIIG+pKK5m/JX1cCyw==" crossorigin="anonymous" referrerpolicy="no-referrer" />    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="<?= CSS ?>/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="<?= IMG ?>/favicon.ico" />

    <style>
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .star {
            font-size: 2rem;
            color: #ccc;
            cursor: pointer;
            transition: color 0.2s;
        }
        .star.selected,
        .star:hover,
        .star:hover ~ .star {
            color: #ffca28;
        }
    </style>
</head>
<body>

    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth">
            <div class="row flex-grow">
                <div class="col-lg-8 mx-auto">
                
                    <div class="auth-form-light text-left p-5">
                        <div class="brand-logo">
                            <a href="../index.php">
                                <img src="../../assets/images/logo_inf_jci.png">
                            </a>
                        </div>
                        <h4 class="text-center">📝 Évaluation de la Formation</h4>
                        <h6 class="font-weight-light text-center mb-3">Hello, merci d'évaluer cette formation</h6>
                        
                        <div id="response-message"></div>

                        <form id="evaluation-form" class="form-section">
                            <input type="hidden" name="mode" value="<?= $mode ?>">
                            <input type="hidden" name="id_cours" value="<?= htmlspecialchars($id_cours) ?>">
                            <input type="hidden" name="ip" value="<?= htmlspecialchars($ip) ?>">
                            <input type="hidden" name="ua" value="<?= htmlspecialchars($ua) ?>">
                            
                            <?php if ($mode === "participant"): ?>
                                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                                <input type="hidden" name="hash" value="<?= htmlspecialchars($hash) ?>">
                            <?php elseif ($mode === "assistant"): ?>
                                <input type="hidden" name="assistant_id" value="<?= htmlspecialchars($uid) ?>">
                            <?php endif; ?>
                            <?php
                            $sections = CRITERES;

                            foreach ($sections as $sectionTitle => $criteria) {
                                echo '<div class="card mb-3 shadow-sm">';
                                echo '<div class="card-header bg-light fw-bold">'.$sectionTitle.'</div>';
                                echo '<div class="card-body">';
                                foreach ($criteria as $label => $name) {
                                    echo '<div class="mb-3">';
                                    echo '<label>'.$label.'</label>';
                                    echo '<div class="star-rating" data-name="'.$name.'"></div>';
                                    echo '</div>';
                                }
                                echo '</div></div>';
                            }
                            ?>

                            <!-- Commentaires -->
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header bg-light fw-bold">💬 Commentaires</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label>Points forts</label>
                                        <textarea name="points_forts" class="form-control" maxlength="500"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label>Points à améliorer</label>
                                        <textarea name="points_ameliorations" class="form-control" maxlength="500"></textarea>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="mdi mdi-send"></i> Envoyer mon évaluation
                            </button>
                        </form>
                    </div>
                </div>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
      <!-- page-body-wrapper ends -->
    </div>


<div class="container my-4">
    <h4 class="text-center"></h4>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    // Génération étoiles
    $(".star-rating").each(function(){
        let name = $(this).data("name");
        let stars = "";
        for(let i=5; i>=1; i--){
            stars += `<span class="star" data-value="${i}">&#9733;</span>`;
        }
        $(this).html(stars).append(`<input type="hidden" name="notes[${name}]">`);
    });

    // Sélection étoile
    $(document).on("click", ".star", function(){
        let value = $(this).data("value");
        let parent = $(this).closest(".star-rating");
        parent.find(".star").removeClass("selected");
        $(this).addClass("selected").nextAll(".star").addClass("selected");
        parent.find("input").val(value);
    });
</script>
<script>
    $(function() {
        $("#evaluation-form").on("submit", function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = form.find("button[type=submit]");
            btn.prop("disabled", true).text("Envoi en cours...");

            $.ajax({
                url: "../courses/treatment.php?action=eval-course",
                type: "POST",
                data: form.serialize(),
                dataType: "json",
                success: function(res) {
                    let alertType = res.success ? "alert-success" : "alert-danger";
                    $("#response-message").html(`<div class="alert ${alertType} p-2">${res.message}</div>`);
                    if (res.success) {
                        form.hide();
                    } else {
                        btn.prop("disabled", false).text("Envoyer mon évaluation");
                    }
                },
                error: function() {
                    $("#response-message").html(`<div class="alert alert-danger p-2">Erreur serveur. Veuillez réessayer.</div>`);
                    btn.prop("disabled", false).text("Envoyer mon évaluation");
                }
            });
        });
    });
</script>
</body>
</html>
