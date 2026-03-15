<?php
$obj = new objects();
$coursId = $_GET['id'] ?? null;

if (!$coursId || !is_numeric($coursId)) {
  die("ID du cours invalide.");
}

$cours = $obj->getCourse($coursId);
$docs = $obj->getAllDocsByCours($coursId);

if (!$cours || $cours['UID'] != $_SESSION['user']['id']) {
  die("Vous n'avez pas accès à ce cours.");
}

$olms = $obj->getAllOlms(); // Si tu as une méthode dédiée
$grade = ACCOUNT['grade'];
$role = USER['role'];
$FA = $obj->getAllFormateursByGrade($grade);


?>

<div class="container mt-5">
  <div class="card shadow-sm">
    <div class="card-body">
      <h3 class="mb-4 text-primary"><i class="mdi mdi-pencil"></i> Modifier le cours</h3>

      <div id="edit-cours-container" class="mb-3">
        <?php if ($grade !== 'FA' || $role = 'SYSADMIN'){ ?>
        <form id="edit-course-form" method="POST" enctype="multipart/form-data">            
          <input type="hidden" name="cours_id" value="<?= $cours['id'] ?>">
          <input type="hidden" name="user_id" value="<?= $_SESSION['user']['id'] ?>">
          <input type="hidden" name="code_cours" value="<?= $cours['code_cours'] ?>">
          <!-- Champ caché pour stocker les fichiers à supprimer -->
          <input type="hidden" name="file_delete_ids" id="file_delete_ids" value="">

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="date_cours">Date du cours</label>
              <input type="date" name="date_cours" class="form-control" value="<?= $cours['date_cours'] ?>" required>
            </div>
            <div class="col-md-6">
              <label for="olm">OLM organisateur</label>
              <select name="olm" id="olm" class="form-select" required>
                <option value="">-- Sélectionner --</option>
                <?php 
                  $olmCodes = array_column($olms, 'code'); // extrait tous les codes en array simple
                  $isAutre = !in_array($cours['olm'], $olmCodes); 
                ?>
                <?php foreach ($olms as $olm): ?>
                  <option value="<?= htmlspecialchars($olm['code']) ?>" <?= ($cours['olm'] === $olm['code'] ? 'selected' : '') ?>>
                    <?= htmlspecialchars($olm['nom']) ?>
                  </option>
                <?php endforeach; ?>
                <option value="autre" <?= $isAutre ? 'selected' : '' ?>>Autre (préciser)</option>
              </select>

              <!-- Champ personnalisé pour OLM autre -->
              <input 
                type="text" 
                name="olm_autre" 
                id="olm_autre" 
                class="form-control mt-2" 
                placeholder="Exemple : JCI Ouaga Etoile / Burkina Faso" 
                style="<?= $isAutre ? '' : 'display:none;' ?>" 
                value="<?= $isAutre ? htmlspecialchars($cours['olm']) : '' ?>"
              >
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6 col-12">
              <label class="form-label fw-semibold">Formateur Assistant</label>
              <select name="id_assistant" id="assistSelect" class="form-select form-select-lg">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($FA as $key => $assistant): ?>
                  <option value="<?= $assistant['UID'] ?>"  <?= ($cours['uid_assistant'] == $assistant['UID']) ? 'selected' : '' ?>><?= $assistant['nom'] .' '. $assistant['prenom'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="">Type de formation</label>
              <select name="type_formation" class="form-select">
                <option value="">-- Sélectionner --</option>
                <option value="interne" <?= ($cours['type_cours'] == 'interne') ? 'selected' : '' ?>>Interne</option>
                <option value="public" <?= ($cours['type_cours'] == 'public') ? 'selected' : '' ?>>Public</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
              <div class="col-md-6">
                <label>Intitulé du cours</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($cours['title']) ?>" required>
              </div>

              <div class="col-md-6">
                <label for="theme">Thème</label>
                <input type="text" name="theme" class="form-control" value="<?= htmlspecialchars($cours['theme']) ?>" required>
              </div>
          </div>

          <div class="mb-3">
            <label for="lieu">Lieu</label>
            <input type="text" name="lieu" class="form-control" value="<?= htmlspecialchars($cours['lieu']) ?>" required>
          </div>

          <div class="mb-3">
            <label for="duree_heure">Durée (heures)</label>
            <input type="number" name="duree_heure" class="form-control" step="0.5" value="<?= $cours['duree_heure'] ?>" required>
          </div>

          <!-- Liste des fichiers déjà uploadés -->
          <div class="mb-3">
            <label class="form-label">Documents uploadés</label>
            <ul id="existing-files" class="list-group">
              <?php foreach ($docs as $file): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <a href="<?= BASE_URL . $file['url_fichier'] ?>" target="_blank"><?= htmlspecialchars($file['nom_fichier']) ?></a>
                  <button type="button" class="btn btn-sm btn-danger delete-file" data-file-id="<?= $file['id'] ?>">🗑 Supprimer</button>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <!-- Nouveaux fichiers -->
          <div class="mb-3">
            <label class="form-label">Ajouter de nouveaux fichiers</label>
            <input type="file" name="new_files[]" class="form-control" multiple>
            <small class="text-muted">Formats acceptés : PDF, JPG, PNG. Taille max : 5 Mo par fichier.</small>
          </div>

          <div class="mt-4 d-grid">
            <button type="submit" class="btn btn-primary">
              <i class="mdi mdi-content-save"></i> Enregistrer les modifications
            </button>
          </div>
        </form>
        <?php } else { ?>
          <div class="alert alert-danger text-center">
              <h4 class="text-center text-danger">
                ❌ En tant que Formateur Assistant, vous n'avez pas accès à cette ressource.
            </h4>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<!-- jQuery : gestion affichage du champ "Autre OLM" -->
<script type="text/javascript">
  $(document).ready(function() {
    $('#olm').select2({
      placeholder: "-- Sélectionner votre OLM --",
      width: '100%', // Pour que ça prenne toute la largeur
      language: "fr"
    });
    $('#assistSelect').select2({
      placeholder: "-- Sélectionner un Assistant --",
      width: '100%', // Pour que ça prenne toute la largeur
      language: "fr"
    });
  });
</script>

<script>
  $(document).ready(function() {
    function toggleOlmAutre() {
      if ($('#olm').val() === 'autre') {
        $('#olm_autre').show().attr('required', true);
      } else {
        $('#olm_autre').hide().removeAttr('required').val('');
      }
    }

    toggleOlmAutre(); // Exécution initiale
    $('#olm').on('change', toggleOlmAutre);
  });
</script>

<script>
  $(document).ready(function () {
      const maxSize = 5 * 1024 * 1024; // 5 Mo

      $(document).on('change', 'input[type="file"]', function () {
          let input = $(this);
          let files = input[0].files;
          let errorList = [];
          let validFiles = [];

          // On vide le message précédent
          input.next(".file-error").remove();

          $.each(files, function (i, file) {
              if (file.size > maxSize) {
                  errorList.push(`📄 ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} Mo)`);
              } else {
                  validFiles.push(file);
              }
          });

          // Affichage des erreurs
          if (errorList.length > 0) {
              let errorHtml = `
                  <div class="file-error alert alert-warning p-2 mt-2" style="font-size: 0.9rem;">
                      ⚠️ <strong>Fichier(s) trop volumineux</strong><br>
                      <small>Chaque fichier doit être ≤ 5 Mo.</small>
                      <ul class="mt-1 mb-0">
                          ${errorList.map(f => `<li>${f}</li>`).join('')}
                      </ul>
                  </div>
              `;
              input.after(errorHtml);
              input.val(""); // Réinitialise pour forcer un nouveau choix
          }
      });
  });
</script>
<script>
  $(document).ready(function () {
      var deleteIds = [];

      $(document).on('click', '.delete-file', function (e) {
        e.preventDefault();
        var fileId = $(this).data('file-id');
        var $li = $(this).closest('li');

        if (!fileId) return;

        Swal.fire({
            title: 'Supprimer ce fichier ?',
            text: "Cette action est irréversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#045326',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
              // Ajouter l’ID dans le tableau
              deleteIds.push(fileId);
              $('#file_delete_ids').val(deleteIds.join(','));

              // Supprimer visuellement
              $li.fadeOut(300, function () {
                  $(this).remove();
              });

              Swal.fire({
                  icon: 'success',
                  title: 'Supprimé !',
                  text: 'Le fichier a été marqué pour suppression.',
                  timer: 1500,
                  showConfirmButton: false
              });
            }
        });
      });
  });
</script>
<script>
  $(document).ready(function(){
      // Soumission formulaire édition cours
      $('#edit-course-form').on('submit', function(e) {
          e.preventDefault();

          let formData = new FormData(this);

          $.ajax({
              url: './courses/treatment.php?action=edit-cours',
              type: 'POST',
              data: formData,
              dataType: "json",
              processData: false, // indispensable pour fichiers
              contentType: false, // indispensable pour fichiers
              success: function (response) {
                  let messageClass = response.success ? 'alert-success' : 'alert-danger';
                  let message = response.message || "Une erreur est survenue.";

                  $("#edit-cours-container").prepend(`
                      <div class="alert ${messageClass} text-center p-3">
                          <strong>${message}</strong>
                      </div>
                  `);

                  if (response.success) {
                      setTimeout(() => {
                          window.location.href = `index.php?page=this-course&id=${formData.get('cours_id')}`;
                      }, 3000);
                  }
              },
              error: function(xhr) {
                  $("#edit-cours-container").prepend(
                      `<div class="alert alert-danger">
                          Erreur serveur : ${xhr.status} - ${xhr.statusText}
                      </div>`
                  );
              }
          });
      });
  });
</script>

