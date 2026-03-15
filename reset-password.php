<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/__class/autoload.class.php');
if (!defined('APP_STARTED')) define('APP_STARTED', true);
include_once($_SERVER['DOCUMENT_ROOT'] . '/config/config.php');

$obj = new objects();
$token = $_GET['token'] ?? '';
$record = null;
$token_valid = true;

if (!empty($token)) {
    $record = $obj->findByToken($token);
    if ($record && $record['used'] == 0 && strtotime($record['expires_at']) > time()) {
        $token_valid = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>TRAINIUM - Finalisation du Compte</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= CSS ?>/style.css">
  <link rel="shortcut icon" href="<?= IMG ?>/favicon.ico" />
</head>
<body>
  <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center bg-light px-3">
    <div class="card shadow border-0 w-100" style="max-width: 500px;">
      <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
          <img src="../../assets/images/logo_inf_jci.png" alt="Logo" class="img-fluid" style="max-height: 80px;">
          <h4 class="mt-3 fw-bold text-primary">🔐 Finalisation du Compte</h4>
          <p class="text-muted mb-1">Veuillez définir un mot de passe sécurisé</p>
        </div>

        <!-- Zone d'affichage dynamique -->
        <div id="response-message" class="mb-3"></div>

        <?php if ($token_valid): ?>
        <form id="update-password" method="POST" novalidate>
          <input type="hidden" name="user_id" value="<?= $record['user_id'] ?>">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

          <!-- Password -->
          <div class="mb-3">
            <label for="password" class="form-label">Nouveau mot de passe</label>
            <div class="input-group">
              <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe sécurisé" minlength="8" required>
              <span class="input-group-text toggle-password" data-target="password" style="cursor:pointer"><i class="bi bi-eye"></i></span>
            </div>
          </div>

          <!-- Confirmation -->
          <div class="mb-3">
            <label for="confirm" class="form-label">Confirmer le mot de passe</label>
            <div class="input-group">
              <input type="password" class="form-control" id="confirm" name="confirm" placeholder="Répétez le mot de passe" required>
              <span class="input-group-text toggle-password" data-target="confirm" style="cursor:pointer"><i class="bi bi-eye"></i></span>
            </div>
            <div class="invalid-feedback d-none" id="match-error">Les mots de passe ne correspondent pas.</div>
          </div>

          <!-- Bouton -->
          <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-primary btn-lg">✅ Enregistrer</button>
          </div>
        </form>
        <?php else: ?>
        <div class="alert alert-danger text-center">
          <i class="bi bi-exclamation-triangle-fill me-1"></i> Ce lien est <strong>expiré ou invalide</strong>.<br>
          Veuillez refaire une demande de réinitialisation.
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    $(function () {
      // Toggle mot de passe
      $('.toggle-password').click(function () {
        const target = $(this).data('target');
        const input = $('#' + target);
        const icon = $(this).find('i');
        if (input.attr('type') === 'password') {
          input.attr('type', 'text');
          icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
          input.attr('type', 'password');
          icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
      });

      // Soumission
      $('#update-password').submit(function (e) {
        e.preventDefault();
        const pass = $('#password').val();
        const confirm = $('#confirm').val();

        if (pass !== confirm) {
          $('#confirm').addClass('is-invalid');
          $('#match-error').removeClass('d-none');
          return;
        }

        $('#confirm').removeClass('is-invalid');
        $('#match-error').addClass('d-none');

        const $form = $(this);
        const $btn = $form.find('button[type=submit]');
        $btn.prop('disabled', true).text("🔄 Enregistrement...");

        $.ajax({
          type: "POST",
          url: "./auth/treatment.php?action=reset-password",
          data: $form.serialize(),
          dataType: 'json',
          success: function(response) {
            const alertClass = response.success ? 'alert-success' : 'alert-danger';
            $('#response-message').html(`
              <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${response.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            `);
            if (response.success) {
              setTimeout(() => location.href = "./auth/login.php", 2500);
            } else {
              $btn.prop('disabled', false).text("Enregistrer");
            }
          },
          error: function(xhr) {
            $('#response-message').html(`
              <div class="alert alert-danger">Erreur AJAX : ${xhr.status} - ${xhr.responseText}</div>
            `);
            $btn.prop('disabled', false).text("Enregistrer");
          }
        });
      });
    });
  </script>
</body>
</html>
