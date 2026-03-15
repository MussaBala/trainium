<?php
    define('APP_STARTED', true);
    include_once($_SERVER['DOCUMENT_ROOT'] . '/config/config.php');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>TRAINIUM - Connexion</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Material Design Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">

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

  <!-- Loader -->
  <div id="loader" class="loader-container">
    <img src="../assets/images/logo_inf_jci.png" id="logo-loader" alt="INF Trainers" class="logo-loader" />
  </div>

  <!-- Contenu principal -->
<div class="container-scroller">
  <div class="container-fluid page-body-wrapper full-page-wrapper">
    <div class="content-wrapper d-flex align-items-center justify-content-center auth">
      <div class="row w-100 justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
          <div class="auth-form-light text-center p-5 shadow-sm rounded bg-white">
            <div class="brand-logo mb-3">
              <img src="../../assets/images/logo_inf_jci.png" alt="INF TRAINERS" class="img-fluid" style="max-height: 80px;">
            </div>
            <h4 class="fw-bold">Hello! Let's get started</h4>
            <h6 class="font-weight-light mb-4">Veuillez-vous connecter</h6>

            <div id="response-message" class="mb-3"></div>

            <form class="login-form" id="login-form" method="post">
              <!-- Email -->
              <div class="form-group mb-3 text-start">
                <label for="exampleInputEmail1" class="form-label">Adresse email</label>
                <input type="email" name="email" class="form-control form-control-lg" id="exampleInputEmail1" placeholder="email" required>
              </div>

              <!-- Mot de passe avec affichage -->
              <div class="form-group mb-4 text-start">
                <label for="exampleInputPassword1" class="form-label">Mot de passe</label>
                <div class="input-group">
                  <input type="password" name="password" class="form-control form-control-lg" id="exampleInputPassword1" placeholder="Mot de passe" required>
                  <span class="input-group-text toggle-password" data-target="exampleInputPassword1" style="cursor:pointer"><i class="bi bi-eye"></i></span>
                </div>
              </div>

              <!-- Bouton connexion -->
              <div class="d-grid mb-3">
                <input type="submit" name="valider" value="Connexion" id="btnConnexion" class="btn btn-primary btn-lg">
              </div>

              <div class="d-flex justify-content-between mb-3 small">
                <a href="lost-password.php" class="auth-link text-primary">Mot de passe oublié ?</a>
              </div>

              <div class="text-center small">
                Pas de compte ? <a href="register.php" class="text-primary">Inscrivez-vous</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  <!-- jQuery + script loader -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    $(document).ready(function () {
      const $logo = $('#logo-loader');
      const $loader = $('#loader');
      const $content = $('#main-content');

      setTimeout(function () {
        $logo.addClass('loaded');
      }, 300);

      setTimeout(function () {
        $loader.hide();
        $content.removeClass('d-none');
      }, 3000);
    });
  </script>

  <script>
    $(function () {
      $('.toggle-password').on('click', function () {
        const inputId = $(this).data('target');
        const input = $('#' + inputId);
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
          input.attr('type', 'text');
          icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
          input.attr('type', 'password');
          icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
      });
    });
  </script>



  <script type="text/javascript">
    $(document).ready(function () {

      $("#login-form").submit(function(e){
          e.preventDefault();
          var formData = new FormData(this);

        $.ajax({
            type: "POST",
            url: "../auth/treatment.php?action=user-login",
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(response) {
              // Vérifie que le serveur a bien retourné une structure attendue
              if (typeof response.success !== "undefined") {
                  const alertClass = response.success ? 'alert-success' : 'alert-danger';
                  const message = response.message || (response.success ? "✅ Connexion réussie !" : "❌ Identifiant ou Mot de passe incorrect !");
                  
                  $('#response-message').html(`<div class="alert ${alertClass}">${message}</div>`);

                  if (response.success) {
                      setTimeout(() => window.location.href = "../index.php", 2000);
                  }
              } else {
                  $('#response-message').html(`<div class="alert alert-warning">❌ Réponse inattendue du serveur.</div>`);
              }
            },
            error: function(xhr) {
              $('#response-message').html(
                  `<div class="alert alert-danger">
                      Erreur AJAX : ${xhr.status}<br><pre>${xhr.responseText}</pre>
                  </div>`
              );
            }

        });
      });

    });
</script>

</body>
</html>
