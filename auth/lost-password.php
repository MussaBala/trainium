<?php
    if (!defined('APP_STARTED')) {
        define('APP_STARTED', true);
    }

    require_once($_SERVER['DOCUMENT_ROOT'] . '/__class/autoload.class.php');
    include_once($_SERVER['DOCUMENT_ROOT'] . '/config/config.php');

    $obj = new objects();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>TRAINIUM - Mot de passe oublié</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="<?= CSS ?>/style.css">
  <link rel="shortcut icon" href="<?= IMG ?>/favicon.ico" />
</head>
<body>
<div class="container-scroller">
  <div class="container-fluid page-body-wrapper full-page-wrapper">
    <div class="content-wrapper d-flex align-items-center auth">
      <div class="row flex-grow">
        <div class="col-lg-6 mx-auto">
          <div class="auth-form-light text-center p-5">
            <div class="brand-logo mb-3">
              <a href="index.php">
                <img src="../../assets/images/logo_inf_jci.png" alt="INF TRAINERS" class="img-fluid" style="max-height: 80px;">
              </a>
            </div>

            <h4 class="mb-2 text-primary"><i class="mdi mdi-lock-reset"></i> Mot de passe oublié</h4>
            <h6 class="font-weight-light">Veuillez entrer votre email pour recevoir un lien de réinitialisation.</h6>

            <div id="response-message" class="mt-3"></div>

            <form class="pt-3" id="forgot-password-form" method="POST">
              <div class="form-group mb-4 text-start">
                <label for="email" class="form-label">Adresse email</label>
                <input type="email" class="form-control form-control-lg" name="email" id="email" placeholder="Votre adresse email" required>
              </div>

              <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-lg">🔐 Envoyer le lien de réinitialisation</button>
              </div>

              <div class="text-center mt-4 font-weight-light">
                <a href="login.php" class="text-primary">← Retour à la connexion</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
  $('#forgot-password-form').submit(function (e) {
    e.preventDefault();
    const formData = $(this).serialize();

    $.ajax({
      type: "POST",
      url: "./treatment.php?action=forgot-password",
      data: formData,
      dataType: "json",
      success: function (response) {
        const alertClass = response.success ? 'alert-success' : 'alert-danger';
        $('#response-message').html(`
          <div class="alert ${alertClass} mt-3">${response.message}</div>
        `);
        if (response.success) {
          $('#forgot-password-form').hide();
        }
      },
      error: function () {
        $('#response-message').html(`
          <div class="alert alert-danger mt-3">Une erreur est survenue. Veuillez réessayer.</div>
        `);
      }
    });
  });
</script>
</body>
</html>
