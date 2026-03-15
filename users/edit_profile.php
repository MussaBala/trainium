<?php

$obj = new objects();
$logger = new activities_log();
$userId = $_SESSION['user']['id'];
$user = $_SESSION['user'];
$account = $_SESSION['account'];
$olmList = $obj->getAllOlms(1); // Si tu as une méthode dédiée
$grades = $obj->getAllGrades();
?>

<div class="container my-4">
  <h2 class="mb-4 text-primary">📝 Modifier mes informations</h2>

  <div class="row">
    <div class="col-md-7">
      <div id="updateMessage"></div>
      <form onsubmit="return false" enctype="multipart/form-data" id="updateAccount" action="" method="post" class="card shadow p-4 mb-4">
        <h5 class="text-success mb-3">Informations du compte</h5>

        <input type="hidden" name="id" value="<?= $account['id'] ?>">

        <div class="mb-3">
          <label for="avatar" class="form-label">Avatar</label>
          <?php if (!empty($account['avatar'])): ?>
            <div class="mb-2">
              <img src="../__files/<?= $account['id'] ?>/profile/<?= $account['avatar'] ?>" alt="Avatar" class="rounded" width="80">
            </div>
          <?php endif; ?>
          <input type="file" name="avatar" value="<?= $account['avatar'] ?>" class="form-control">
        </div>

        <div class="mb-3">
          <label for="nom" class="form-label">Nom</label>
          <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($account['nom']) ?>" required>
        </div>

        <div class="mb-3">
          <label for="prenom" class="form-label">Prénoms</label>
          <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($account['prenom']) ?>" required>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Adresse email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($account['email']) ?>" required>
        </div>

        <div class="mb-3">
          <label for="telephone" class="form-label">Téléphone</label>
          <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($account['telephone']) ?>">
        </div>

        <div class="mb-3">
          <label for="olm" class="form-label">OLM</label>
          <select name="olm" id="olm" class="form-select" required>
              <option value="" disabled selected>-- Sélectionner votre OLM --</option>
              <?php
              foreach ($olmList as $olmList) {
                $code = $olmList['code'];
                $label = $olmList['nom'];
                  $selected = (isset($account['olm']) && $account['olm'] === $code) ? 'selected' : '';
                  echo "<option value=\"$key\" $selected>$label</option>";
              }
              ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="grade" class="form-label">Grade Formateur JCI</label>
          <select name="grade" id="grade" class="form-select" required>
            <option value="" disabled>-- Sélectionner votre GRADE --</option>
            <?php foreach ($grades as $grade) { ?>
              <option value="<?= $grade['code'] ?>" <?= ($account['grade'] === $grade['code']) ? 'selected' : '' ?>>
                <?= $grade['libelle'] ?>
              </option>
            <?php } ?>
          </select>
        </div>

        <div class="mb-3">
          <label for="date_deb_formateur" class="form-label">Date début formateur</label>
          <input type="date" name="date_deb_formateur" class="form-control" value="<?= $account['date_deb_formateur'] ?>">
        </div>

        <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
      </form>
    </div>

    <div class="col-md-5">
      <div id="response-message" class="mt-3"></div>

      <form id="update-login-form" class="card shadow p-4" method="post">
        <h5 class="text-warning mb-3"><i class="mdi mdi-lock-reset"></i> Identifiants de connexion</h5>
        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
        <div class="mb-3">
          <label for="login" class="form-label">Nom d'utilisateur</label>
          <input type="text" name="login" class="form-control" value="<?= htmlspecialchars($user['login']) ?>" readonly>
        </div>

        <div class="mb-3">
          <label for="current_password" class="form-label">Mot de passe actuel</label>
          <div class="input-group">
            <input type="password" name="current_password" id="current_password" class="form-control" required>
            <!-- <span class="input-group-text toggle-password" data-target="current_password"><i class="mdi mdi-eye"></i></span> -->
          </div>
        </div>

        <div class="mb-3">
          <label for="new_password" class="form-label">Nouveau mot de passe</label>
          <div class="input-group">
            <input type="password" name="new_password" id="new_password" class="form-control" required minlength="6">
            <span class="input-group-text toggle-password" data-target="new_password"><i class="mdi mdi-eye"></i></span>
          </div>
        </div>

        <div class="mb-3">
          <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
          <div class="input-group">
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            <!-- <span class="input-group-text toggle-password" data-target="confirm_password"><i class="mdi mdi-eye"></i></span> -->
          </div>
          <div class="invalid-feedback d-none" id="match-error">Les mots de passe ne correspondent pas.</div>
        </div>

        <div class="d-grid mt-3">
          <button type="submit" class="btn btn-warning text-white">🔐 Mettre à jour le mot de passe</button>
        </div>

      </form>
    </div>
  </div>
</div>


<script>
  $("#updateAccount").on('submit', function (e) {
      e.preventDefault();

      const $form = $(this);
      const $submitBtn = $form.find("button[type=submit]");
      const formData = new FormData(this);

      // Désactiver le bouton pour éviter les doubles clics
      $submitBtn.prop("disabled", true).text("Traitement...");

      // Nettoyer les anciens messages
      $("#updateMessage").html("");

      $.ajax({
          type: "POST",
          url: "./users/treatment.php?action=update-account",
          data: formData,
          dataType: 'json',
          processData: false,
          contentType: false,

          success: function (response) {
              const alertClass = response.success ? 'alert-success' : 'alert-danger';
              $("#updateMessage").html(`
                  <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                      ${response.message}
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
              `);

              // Optionnel : rechargement après succès
              if (response.success) {
                  setTimeout(() => window.location.reload(), 2000);
              }
          },

          error: function (xhr, status, error) {
              console.error("Erreur AJAX :", status, error, xhr.responseText);
              $("#updateMessage").html(`
                  <div class="alert alert-danger alert-dismissible fade show" role="alert">
                      Une erreur s'est produite lors de la mise à jour.
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
              `);
          },

          complete: function () {
              $submitBtn.prop("disabled", false).text("Mettre à jour");
          }
      });
  });
</script>

<script>
  $(function () {
    // Affichage/Masquage mot de passe
    $(".toggle-password").on("click", function () {
      const target = $(this).data("target");
      const $input = $("#" + target);
      const $icon = $(this).find("i");

      if ($input.attr("type") === "password") {
        $input.attr("type", "text");
        $icon.removeClass("mdi-eye").addClass("mdi-eye-off");
      } else {
        $input.attr("type", "password");
        $icon.removeClass("mdi-eye-off").addClass("mdi-eye");
      }
    });

    // Soumission AJAX
    $("#update-login-form").submit(function (e) {
      e.preventDefault();

      const newPassword = $("#new_password").val();
      const confirmPassword = $("#confirm_password").val();

      if (newPassword !== confirmPassword) {
        $("#confirm_password").addClass("is-invalid");
        $("#match-error").removeClass("d-none");
        return;
      } else {
        $("#confirm_password").removeClass("is-invalid");
        $("#match-error").addClass("d-none");
      }

      $.ajax({
        type: "POST",
        url: "./users/treatment.php?action=update-login",
        data: $(this).serialize(),
        dataType: "json",
        success: function (response) {
          const alertClass = response.success ? 'alert-success' : 'alert-danger';
          $("#response-message").html(`
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
              ${response.message}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
          `);

          if (response.success) {
            setTimeout(() => {
              // Redirection vers la déconnexion pour forcer la reconnexion
              window.location.href = "../auth/logout.php";
            }, 2500);
          }
        },
        error: function (xhr) {
          $("#response-message").html(`
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              Erreur AJAX : ${xhr.status}<br><pre>${xhr.responseText}</pre>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
          `);
        }
      });
    });
  });
</script>
