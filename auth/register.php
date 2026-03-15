<?php
    if (!defined('APP_STARTED')) {
        define('APP_STARTED', true);
    }

    require_once($_SERVER['DOCUMENT_ROOT'] . '/__class/autoload.class.php');
    include_once($_SERVER['DOCUMENT_ROOT'] . '/config/config.php');

    $obj = new objects();

    $grades = $obj->getAllGrades();
    $olms = $obj->getAllOlms(1); // Si tu as une méthode dédiée

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>TRAINIUM - Inscription</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

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

  <!-- Style du loader -->
  <style>
    body {
      margin: 0;
      padding: 0;
    }
  </style>
</head>
<body>


  <!-- Contenu principal -->
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
                  <h4 class="text-center">Hello! Vous êtes Formateur JCI</h4>
                  <h6 class="font-weight-light text-center">Veuillez-vous Inscrire!</h6>
                  
                  <div id="response-message" class="mt-3"></div>
                  
                  <div id="registration-container">                      
                    <form class="register-form pt-3" id="register-form" action="" method="POST">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label for="nom">Nom</label>
                          <input type="text" name="nom" class="form-control form-control-lg" placeholder="Nom" required>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label for="prenoms">Prénoms</label>
                          <input type="text" name="prenoms" class="form-control form-control-lg" placeholder="Prénoms" required>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label for="email">Adresse email</label>
                          <input type="email" name="email" class="form-control form-control-lg" placeholder="Adresse email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label for="contact">Contact</label>
                          <input type="tel" name="contact" class="form-control form-control-lg" placeholder="Contact téléphonique" required>
                        </div>
                      </div>

                      <div class="form-group mb-4">
                        <label for="olm" class="form-label">OLM d'origine</label>
                        <select name="olm" id="olmSelect" class="form-select form-select-lg" required>
                          <option value="">-- Sélectionner --</option>
                          <?php foreach ($olms as $olm): ?>
                            <option value="<?= $olm['code'] ?>"><?= $olm['nom'] ?></option>
                          <?php endforeach; ?>
                          <option value="autre">Autre (préciser)</option>
                        </select>

                      </div>

                      <div class="row">
                          <div class="col-md-6 mb-4">
                            <label for="date_debut" class="form-label">Date de début comme formateur</label>
                            <input type="date" name="date_debut" class="form-control form-control-lg" required>
                          </div>

                          <div class="col-md-6 mb-4">
                            <label for="grade" class="form-label">Grade actuel</label>
                            <select name="grade" class="form-select form-select-lg" required>
                                <option value="" disabled selected>-- Sélectionner --</option>
                                <?php foreach ($grades as $grade) { ?>
                                <option value="<?= $grade['code'] ?>"><?= $grade['libelle'] ?></option>
                                <?php } ?>
                            </select>
                          </div>
                      </div>

                      <div class="mt-4 d-grid gap-2">
                          <button type="submit" class="btn btn-primary btn-lg">Inscription</button>
                      </div>

                      <div class="text-center mt-4 font-weight-light">
                          Déjà inscrit ? <a href="login.php" class="text-primary">Connectez-vous</a>
                      </div>

                    </form>
                  </div>
                </div>
            
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>

  <!-- jQuery + script loader -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  
  <script type="text/javascript">
    $(document).ready(function() {
      $('#olmSelect').select2({
        placeholder: "-- Sélectionner votre OLM --",
        width: '100%', // Pour que ça prenne toute la largeur
        language: "fr"
      });
    });
  </script>

  <script type="text/javascript">
    $(document).ready(function () {

      $("#register-form").submit(function(e){
          e.preventDefault();
          var formData = new FormData(this);

        $.ajax({
            type: "POST",
            url: "../auth/treatment.php?action=register-form",
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (response) {
                let messageClass = response.success ? 'alert-success' : 'alert-danger';
                let message = response.message || "Une erreur est survenue.";

                $("#registration-container").html(`
                    <div class="alert ${messageClass} text-center p-4">
                        <h4 class="mb-2">${response.success ? '🎉 Inscription réussie !' : '⚠️ Attention'}</h4>
                        <p>${message}</p>
                        ${response.success ? '<a href="./login.php" class="btn btn-primary mt-3">Aller à la page de connexion</a>' : ''}
                    </div>
                `);
            },            
            error: function(xhr) {
              $('#response-message').html(
                  `<div class="alert alert-danger">
                      Erreur AJAX : <pre>${xhr.responseText}</pre>
                  </div>`
              );
            }

        });
      });

    });
  </script>

</body>
</html>
