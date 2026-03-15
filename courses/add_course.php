<?php
$obj = new objects();
$grades = $obj->getAllGrades(); // Pour d’éventuelles options
$olms = $obj->getAllOlms(); // Si tu as une méthode dédiée
$uid = USER['id'];
$grade = ACCOUNT['grade'];
$FA = $obj->getAllFormateursByGrade($grade);
$role = USER['role'];

// // var_dump($grade);
// if ($grade != 'FA') {
//   echo "formateur assistant";
// }

?>

<div class="container my-4" id="course-container">
  <div class="row justify-content-center">
    <div class="col-lg-12 col-md-12 col-12">
      <div class="card shadow-lg border-0 rounded-3">
        <div class="card-body p-4">

          <!-- Logo -->
          <div class="text-center mb-4">
            <img src="../../assets/images/logo_inf_jci.png" 
                 alt="Logo" class="img-fluid" style="max-height: 70px;">
          </div>

          <!-- Titre -->
          <h3 class="text-center fw-bold mb-4">
            <i class="mdi mdi-book-plus-outline text-primary"></i> Déclarer un cours
          </h3>

          <!-- Message de retour -->
          <div id="response-message" class="mb-3"></div>
          <div id="form-container">
            <?php if ($grade == "FA" && $role == "FORM") { ?>
              <div class="alert alert-danger text-center">
                  <h4 class="text-center text-danger">
                    ❌ En tant que Formateur Assistant, vous ne pouvez pas animer de cours en tant que formateur principal! <br>
                    Prière de vous rapprocher de l'Institut de Formation de la JCI CI.
                </h4>
              </div>
            <?php } else { ?>
              <form id="add-cours-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="uid" value="<?= $uid ?>">

                <!-- 📅 Date & OLM -->
                <div class="row g-3 mb-3">
                  <div class="col-md-6 col-12">
                    <label class="form-label fw-semibold">📅 Date du cours</label>
                    <input type="date" name="date_cours" class="form-control form-control-lg" required>
                  </div>
                  <div class="col-md-6 col-12">
                    <label class="form-label fw-semibold">🏛️ OLM organisateur</label>
                    <select name="olm" id="olmSelect" class="form-select form-select-lg" required>
                      <option value="">-- Sélectionner --</option>
                      <?php foreach ($olms as $olm): ?>
                        <option value="<?= $olm['code'] ?>"><?= $olm['nom'] ?></option>
                      <?php endforeach; ?>
                      <option value="autre">Autre (préciser)</option>
                    </select>
                    <input type="text" name="olm_autre" id="olmAutreInput"
                          class="form-control form-control-lg mt-2 d-none"
                          placeholder="Ex: JCI Abidjan / Côte d'Ivoire">
                    <small class="text-muted d-none" id="olmAutreHelp">Format : OLM/Pays</small>
                  </div>
                </div>

                <!-- 📝 Assistant & Type -->
                <div class="row g-3 mb-3">
                  <div class="col-md-6 col-12">
                    <label class="form-label fw-semibold">Formateur Assistant</label>
                    <select name="id_assistant" class="form-select form-select-lg">
                      <option value="">-- Sélectionner --</option>
                      <?php foreach ($FA as $key => $assistant): ?>
                        <option value="<?= $assistant['UID'] ?>"><?= $assistant['nom'] .' '. $assistant['prenom'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-6 col-12">
                    <label class="form-label fw-semibold">🎓 Type de formation</label>
                    <select name="type_formation" class="form-select form-select-lg" required>
                      <option value="">-- Sélectionner --</option>
                      <option value="interne">Interne</option>
                      <option value="public">Public</option>
                    </select>
                  </div>
                </div>

                <!-- 📚 Intitulé & Thème -->
                <div class="row g-3 mb-3">
                  <div class="col-md-6 col-12">
                    <label class="form-label fw-semibold">📝 Intitulé du cours</label>
                    <input type="text" name="title" class="form-control form-control-lg" required>
                  </div>
                  <div class="col-md-6 col-12">
                    <label class="form-label fw-semibold">📚 Thème du cours</label>
                    <input type="text" name="theme" class="form-control form-control-lg" required>
                  </div>

                </div>

                <!-- 📍 Lieu & ⏳ Durée -->
                <div class="row g-3 mb-3">
                  <div class="col-md-6 col-12">
                    <label class="form-label fw-semibold">📍 Lieu de la formation</label>
                    <input type="text" name="lieu" class="form-control form-control-lg" required>
                  </div>
                  <div class="col-md-6 col-12">
                    <div class="mb-3">
                      <label class="form-label fw-semibold">⏳ Durée (heures)</label>
                      <input type="number" name="duree_heure" step="0.5" class="form-control form-control-lg" value="2" required>
                    </div>
                  </div>
                </div>

                <!-- 📂 Upload fichiers -->
                <div class="mb-3">
                  <label class="form-label fw-semibold">📂 Upload de documents</label>
                  <input type="file" id="fileInput" name="documents[]" class="form-control" multiple accept=".pdf,.ppt,.pptx,.jpg,.jpeg,.png">
                  <div class="d-flex justify-content-between mt-2">
                    <small class="text-muted">Formats acceptés : PDF, PPT, JPG, PNG (max 5 Mo)</small>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="resetFiles">
                      <i class="mdi mdi-close"></i> Réinitialiser
                    </button>
                  </div>
                  <div id="file-preview" class="row row-cols-2 row-cols-md-4 g-2 mt-3"></div>
                </div>

                <!-- ✅ Soumission -->
                <div class="d-grid mt-4">
                  <button type="submit" id="submitBtn" class="btn btn-primary btn-lg shadow">
                    <span id="btn-text"><i class="mdi mdi-send"></i> Soumettre le cours</span>
                    <span id="btn-spinner" class="spinner-border spinner-border-sm d-none"></span>
                  </button>
                </div>
              </form>
            <?php } ?>

          </div>

        </div>
      </div>
    </div>
  </div>
</div>


<script>
  $(function() {
    $("#olmSelect").on("change", function() {
      if ($(this).val() === "autre") {
        $("#olmAutreInput, #olmAutreHelp").removeClass("d-none");
        $("#olmAutreInput").attr("required", true);
      } else {
        $("#olmAutreInput, #olmAutreHelp").addClass("d-none");
        $("#olmAutreInput").val("").removeAttr("required");
      }
    });
  });
</script>

<script>
  $(function() {
    const $form = $("#add-cours-form");
    const $submitBtn = $("#submitBtn");
    const $olmSelect = $("#olmSelect");
    const $olmAutreInput = $("#olmAutreInput");
    const $olmAutreHelp = $("#olmAutreHelp");

    const regexOlm = /^JCI\s.+\s*\/\s*.+$/i; // format attendu "JCI Abidjan / Côte d'Ivoire"

    // Vérification globale du formulaire
    function validateForm() {
      let isValid = true;

      // Vérifier chaque champ required classique
      $form.find("input[required], select[required], textarea[required]").each(function() {
        if (!$(this).val().trim()) {
          isValid = false;
        }
      });

      // Vérification spéciale pour OLM = "autre"
      if ($olmSelect.val() === "autre") {
        const val = $olmAutreInput.val().trim();
        if (!regexOlm.test(val)) {
          isValid = false;
          if (val.length > 0) {
            $olmAutreHelp.removeClass("text-muted text-success").addClass("text-danger")
              .text("❌ Format invalide. Exemple : JCI Abidjan / Côte d'Ivoire");
          } else {
            $olmAutreHelp.removeClass("text-danger text-success").addClass("text-muted")
              .text("Format : JCI Nom_OLM / Pays");
          }
        } else {
          $olmAutreHelp.removeClass("text-danger text-muted").addClass("text-success")
            .text("✅ Format correct");
        }
      }

      // Activer/désactiver le bouton
      $submitBtn.prop("disabled", !isValid);
    }

    // Affiche/masque champ OLM Autre
    $olmSelect.on("change", function() {
      if ($(this).val() === "autre") {
        $olmAutreInput.removeClass("d-none").attr("required", true);
        $olmAutreHelp.removeClass("d-none").removeClass("text-danger text-success").addClass("text-muted")
          .text("Format : JCI Nom_OLM / Pays");
      } else {
        $olmAutreInput.addClass("d-none").val("").removeAttr("required");
        $olmAutreHelp.addClass("d-none");
      }
      validateForm();
    });

    // Écoute tous les champs du formulaire
    $form.on("input change blur", "input, select, textarea", validateForm);

    // Désactiver le bouton par défaut
    $submitBtn.prop("disabled", true);

    // Validation initiale
    validateForm();
  });
</script>

<script>
  $(function(){
    // Reset files
    $('#resetFiles').click(() => {
      $('#fileInput').val('');
      $('#file-preview').empty();
    });

    // Preview files
    $('#fileInput').on('change', function (e) {
      const preview = $('#file-preview').empty();
      const files = e.target.files;
      const allowed = ['pdf','ppt','pptx','jpg','jpeg','png'];

      [...files].forEach(file => {
        const ext = file.name.split('.').pop().toLowerCase();
        if(!allowed.includes(ext)) return;

        const reader = new FileReader();
        reader.onload = function(ev){
          let html = '';
          if(['jpg','jpeg','png'].includes(ext)){
            html = `<div class="col text-center">
                      <img src="${ev.target.result}" class="img-fluid rounded shadow-sm" style="max-height:100px;object-fit:cover;">
                      <small class="d-block text-truncate">${file.name}</small>
                    </div>`;
          } else {
            html = `<div class="col-12">
                      <div class="border p-2 rounded bg-light d-flex align-items-center">
                        📎 <span class="ms-2 text-truncate">${file.name}</span>
                      </div>
                    </div>`;
          }
          preview.append(html);
        };
        reader.readAsDataURL(file);
      });
    });

    // Soumission AJAX
    $('#add-cours-form').submit(function(e){
        e.preventDefault();

        const formData = new FormData(this);
        const $btn = $('#submitBtn');
        const $btnText = $('#btn-text');
        const $btnSpinner = $('#btn-spinner');

        // Désactivation du bouton et affichage du spinner
        $btn.prop('disabled', true);
        $btnText.addClass('d-none');
        $btnSpinner.removeClass('d-none');
        $('#response-message').html('');

        $.ajax({
            type: "POST",
            url: "./courses/treatment.php?action=add-course",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(res){
                let cls = res.success ? "alert-success" : "alert-danger";
                $('#form-container').html(`
                    <div class="alert ${cls} text-center">
                        ${res.message}
                    </div>
                `);

                if (res.success) {
                    setTimeout(() => {
                        window.location.href = `index.php?page=my-courses`;
                    }, 3000);
                } else {
                    // Réactiver le bouton si la création échoue
                    $btn.prop('disabled', false);
                }
            },
            error: function(xhr){
                $('#form-container').html(`
                    <div class="alert alert-danger text-center">
                        ❌ Erreur serveur (${xhr.status})
                    </div>
                `);
                // Réactiver le bouton en cas d’erreur
                $btn.prop('disabled', false);
            },
            complete: function(){
                // Remettre l'état initial du bouton si on le réactive
                if (!$btn.prop('disabled')) {
                    $btnText.removeClass('d-none');
                    $btnSpinner.addClass('d-none');
                }
            }
        });
    });
  });
</script>
