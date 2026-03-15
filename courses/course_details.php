<?php
    $obj = new objects();
    $role = $_SESSION['user']['role'];

    $idCours = $_GET['id'] ?? null;
    $cours = $obj->getCourse($idCours);

    if (!$cours) {
        echo "<div class='alert alert-danger'>Cours introuvable.</div>";
        exit;
    }

    $nomAssistant = '';
    $account = $obj->getAccountByUID($cours['UID']);
    if (isset($cours['uid_assistant'])) {
        $assistant =  $obj->getAccountByUID($cours['uid_assistant']);
        $already = $obj->hasAssistantEval($idCours, $cours['uid_assistant']);

        if (isset($assistant)) {
            $nomAssistant = ucwords($assistant['prenom'] . ' ' . $assistant['nom']);
        }else {
            $nomAssistant = "-";
        }
    }
    
    $nomFormateur = ucwords($account['prenom'] . ' ' . $account['nom']);
    $isSYSADMIN = ($_SESSION['user']['role'] === 'SYSADMIN');
    $isOwner = ($cours['UID'] == $_SESSION['user']['id']);
    $documents = $obj->getAllDocsByCours($idCours);
    $qrcode = $cours['qr_code'];
    // Détermine le statut du cours
    $statutCours = intval($cours['status']);
    switch ($statutCours) {
        case 1:
            $badgeStatut = '<span class="badge bg-primary"><i class="mdi mdi-check-circle-outline"></i> Validé</span>';
            break;
        case -1:
            $badgeStatut = '<span class="badge bg-danger"><i class="mdi mdi-close-circle-outline"></i> Refusé</span>';
            break;
        default:
            $badgeStatut = '<span class="badge bg-warning text-dark"><i class="mdi mdi-clock-outline"></i> En attente de validation</span>';
    }

    $olm = $obj->getOlmByCode($cours['olm']);


    $logs = $obj->getLogsByCoursId($cours['id']);
    $resultats = $obj->courseEvaluationResults($idCours);
    $currentUserId = $_SESSION['user']['id'];

    // Si admin → tout voir
    if ($_SESSION['user']['role'] === 'INF') {
        $dataByUser = $resultats['data'];
    }
    // Si formateur principal → voir ses notes + option assistant
    elseif ($cours['UID'] == $currentUserId) {
        $dataByUser = [
            'me' => $resultats['data'][$cours['UID']] ?? [],
            'assistant' => $resultats['data'][$cours['uid_assistant']] ?? []
        ];
    }
    // Si assistant → seulement ses notes
    elseif ($cours['uid_assistant'] == $currentUserId) {
        $dataByUser = [
            'me' => $resultats['data'][$cours['uid_assistant']] ?? []
        ];
    }
    
?>

<div class="container mt-5">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <h2 class="mb-4"><i class="mdi mdi-book-open-page-variant"></i> Détails du cours</h2>

    <div class="d-flex flex-wrap gap-2">
      <?php if ($role == 'SYSADMIN'): ?>
      <a href="../index.php?page=courses-list" class="btn btn-secondary btn-sm">
        <i class="mdi mdi-arrow-left-bold"></i> Retour à la liste
      </a>
      <?php endif; ?>
    </div>
  </div>
    <!-- Navigation onglets -->
    <?php
        $activeTab = "details"; 
        if ($cours['status'] == 4 && $_SESSION['user']['role'] === 'SYSADMIN') {
            $activeTab = "evaluation";
        }

    ?>

    <ul class="nav nav-tabs" id="courseTabs">
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'details' ? 'active' : '' ?>" data-bs-toggle="tab" href="#details">
                <i class="mdi mdi-information-outline"></i> Détails
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'files' ? 'active' : '' ?>" data-bs-toggle="tab" href="#files">
                <i class="mdi mdi-file-outline"></i> Fichiers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'evaluation' ? 'active' : '' ?>" data-bs-toggle="tab" href="#evaluation">
                <i class="mdi mdi-chart-bar"></i> Évaluation
            </a>
        </li>
    </ul>

    <div class="tab-content p-3 bg-light rounded-bottom shadow-sm">

        <!-- Onglet 1 : Détails -->
        <div class="tab-pane fade <?= $activeTab === 'details' ? 'show active' : '' ?>" id="details">
            <div class="row g-4">
                <!-- Infos cours -->
                <div class="col-md-7">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3"><?= htmlspecialchars($cours['title']) ?></h5>
                            <p><strong>Thème :</strong> <?= htmlspecialchars($cours['theme']) ?></p>
                            <p><strong>Formateur :</strong> <?= $nomFormateur ?></p>
                            <p>
                                <strong>Formateur Assistant :</strong> <?= $nomAssistant ?>
                            
                                <?php if ($isOwner && !empty($cours['uid_assistant']) && $cours['status'] == 3 && !$already): ?>
                                    <a class="badge bg-danger rounded-pill" href="../evaluation.php?uid=<?=$cours['uid_assistant']?>&idCours=<?=$idCours?>" target="_blank">
                                        <i class="mdi mdi-account-check"></i> Évaluer l'assistant
                                    </a>
                                <?php endif; ?>

                            </p>
                            <p><strong>Date :</strong> <?= date('d/m/Y', strtotime($cours['date_cours'])) ?></p>
                            <p><strong>OLM :</strong> <?= strtoupper($olm['nom']) ?></p>
                            <p><strong>Lieu :</strong> <?= htmlspecialchars($cours['lieu']) ?></p>
                            <p><strong>Durée :</strong> <?= $cours['duree_heure'] ?> heure(s)</p>
                        <?php if ($isOwner && $cours['status'] < 2): ?>
                          <a href="../?page=edit-course&id=<?= $cours['id'] ?>" class="btn btn-outline-primary mt-3">
                            <i class="mdi mdi-pencil"></i> Modifier ce cours
                          </a>
                        <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- QR Code -->
                <div class="col-md-5 text-center">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="mb-3">QR Code d'évaluation</h6>
                            <?php if (!empty($qrcode) && file_exists($_SERVER['DOCUMENT_ROOT'] . $qrcode)): ?>
                                <img src="<?= $qrcode ?>" class="img-fluid mb-3" style="max-height:200px;">
                                <a href="<?= $qrcode ?>" download class="btn btn-success w-100">
                                    <i class="mdi mdi-download"></i> Télécharger
                                </a>
                            <?php else: ?>
                                <button id="generate-qr-btn" class="btn btn-outline-success w-100">
                                    <i class="mdi mdi-qrcode"></i> Générer le QR Code
                                </button>
                                <div id="qr-preview" class="mt-3" style="display:none;"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onglet 2 : Fichiers -->
        <div class="tab-pane fade <?= $activeTab === 'files' ? 'show active' : '' ?>" id="files">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">📂 Documents & supports</h5>
                <?php if ($isOwner): ?>
                    <button class="btn btn-sm btn-outline-primary" 
                            data-bs-toggle="modal" 
                            data-bs-target="#uploadDocModal">
                        <i class="mdi mdi-upload"></i> Ajouter un fichier
                    </button>
                <?php endif; ?>
            </div>

            <div id="lightgallery" class="row g-3">
                <?php foreach ($documents as $doc): 
                    if ($doc['type_document'] !== 'qr_code'):
                        $ext = strtolower(pathinfo($doc['nom_fichier'], PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg','jpeg','png']);
                        $icon = $isImage 
                            ? $doc['url_fichier'] 
                            : (IMG.'/'.($ext === 'pdf' ? 'pdf.png' : 'file.png'));
                ?>
                    <div class="col-6 col-sm-4 col-md-3 text-center gallery-item" 
                        <?php if ($isImage): ?> data-src="<?= htmlspecialchars($doc['url_fichier']) ?>" <?php endif; ?>>
                        <a href="<?= htmlspecialchars($doc['url_fichier']) ?>" 
                        target="_blank" 
                        <?= $isImage ? '' : 'download' ?>>
                            <img src="<?= htmlspecialchars($icon) ?>" 
                                class="img-fluid rounded shadow-sm mb-2 w-100" 
                                style="height:150px; object-fit:cover;">
                            <div class="small text-truncate"><?= htmlspecialchars($doc['nom_fichier']) ?></div>
                        </a>
                    </div>
                <?php endif; endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                <nav>
                    <ul id="galleryPagination" class="pagination pagination-sm"></ul>
                </nav>
            </div>        
        </div>

            <!-- Modal Upload -->
            <div class="modal fade" id="uploadDocModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="post" id="upload-files" enctype="multipart/form-data" class="modal-content">
                <input type="hidden" name="idCours" value="<?=$idCours?>">
                <input type="hidden" name="userId" value="<?=$cours['UID']?>">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un fichier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="file" name="documents[]" class="form-control" multiple required>
                    <small class="text-muted">Formats autorisés : PDF, PPT, PNG, JPG (max 5Mo)</small>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                    <span id="btn-text"><i class="mdi mdi-upload"></i> Envoyer</span>
                    <span id="btn-spinner" class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                </div>
                </form>
            </div>
            </div>


        <!-- Onglet 3 : evaluation -->
        <div class="tab-pane fade <?= $activeTab === 'evaluation' ? 'show active' : '' ?>" id="evaluation">
            <?php
                $isAdmin     = in_array($_SESSION['user']['role'], ['SYSADMIN','INF']);
                $isFormateur = $cours['UID'] == $_SESSION['user']['id'];
                $isAssistant = $cours['uid_assistant'] == $_SESSION['user']['id'];

                $evals = $obj->courseEvaluationResults($cours['id']);

                $dataFormateur = $evals['data'][$cours['UID']] ?? null;
                $dataAssistant = $evals['data'][$cours['uid_assistant']] ?? null;

                // Si assistant → forcer à n'afficher que ses résultats
                if ($isAssistant) {
                    $dataFormateur = null;
                }
            ?>

            <!-- Tabs pour Admin/Formateur -->
            <?php if ($isAdmin || $isFormateur): ?>
                <ul class="nav nav-tabs mb-3" id="evalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-formateur" data-bs-toggle="tab" data-bs-target="#eval-formateur" type="button" role="tab">
                            Formateur principal
                        </button>
                    </li>
                    <?php if ($dataAssistant): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-assistant" data-bs-toggle="tab" data-bs-target="#eval-assistant" type="button" role="tab">
                            Assistant
                        </button>
                    </li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>

            <div class="tab-content">
                <!-- Bloc Formateur principal -->
                <?php if (($isAdmin || $isFormateur) && $dataFormateur): ?>
                <div class="tab-pane fade show active" id="eval-formateur" role="tabpanel">
                    <?php include $_SERVER['DOCUMENT_ROOT'] . '/partials/eval_block.php'; 
                        renderEvalBlock($cours, $dataFormateur, "formateur", $isAdmin); ?>
                </div>
                <?php endif; ?>

                <!-- Bloc Assistant -->
                <?php if ((($isAdmin || $isFormateur) && $dataAssistant) || $isAssistant): ?>
                <div class="tab-pane fade <?= $isAssistant ? 'show active' : '' ?>" id="eval-assistant" role="tabpanel">
                    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/partials/eval_block.php'; 
                        renderEvalBlock($cours, $dataAssistant, "assistant", $isAdmin); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

        <!-- Validation pour SYSADMIN -->
  <?php if ($isSYSADMIN && ($cours['status'] == null || $cours['status'] == 1 )): ?>
    <div class="card shadow-sm mt-5">
      <div class="card-body">
        <h5 class="mb-3 text-info"><i class="mdi mdi-check-decagram"></i> Validation du Cours</h5>
        <div id="response-message" class="mb-3"></div>

        <form id="validation-form">
          <input type="hidden" name="cours_id" value="<?= $cours['id'] ?>">
          <div class="mb-3">
            <label><strong>Action :</strong></label>
            <div>
              <label class="me-3"><input type="radio" name="valide" value="1"> ✅ Valider</label>
              <label><input type="radio" name="valide" value="0"> ❌ Refuser</label>
            </div>
          </div>

          <div class="mb-3 d-none" id="motif-container">
            <label>Motif du refus <small class="text-danger">(obligatoire)</small></label>
            <textarea name="motif" class="form-control" rows="3" placeholder="Indiquez la raison du refus"></textarea>
          </div>

          <button type="submit" class="btn btn-success">
            <i class="mdi mdi-check"></i> Soumettre la validation
          </button>
        </form>
      </div>
    </div>
  <?php endif; ?>

    <div class="card shadow-sm mt-4">
      <div class="card-body">
          <h5 class="mb-3 text-primary d-flex justify-content-between align-items-center">
          <span><i class="mdi mdi-history"></i> Historique du cours</span>
          <?php if (!empty($logs)): ?>
              <span class="badge bg-info"><?= count($logs) ?> activité(s)</span>
          <?php endif; ?>
          </h5>

          <?php if (!empty($logs)): ?>
          <div class="scrollable-logs" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; border-radius: .25rem;">
            <ul class="list-group list-group-flush">
                <?php foreach ($logs as $log): ?>
                <li class="list-group-item d-flex justify-content-between align-items-start">
                <div class="ms-2 me-auto">
                    <div class="fw-bold">
                    <?php
                        $action = ucfirst($log['action']);
                        switch ($action) {
                        case 'Validation':
                            $badgeClass = 'info';
                            break;
                        case 'Refus':
                            $badgeClass = 'danger';
                            break;
                        case 'Création':
                            $badgeClass = 'secondary';
                            break;
                        case 'Modification':
                            $badgeClass = 'info';
                            break;
                        default:
                            $badgeClass = 'secondary';
                            break;
                        }
                    ?>
                    <span class="badge bg-<?= $badgeClass ?> rounded-pill"><?= $action ?></span>
                    <small class="text-muted">par <?= htmlspecialchars($log['prenom'] . ' ' . $log['nom']) ?></small>
                    </div>
                    <?= nl2br(htmlspecialchars($log['details'])) ?>
                </div>
                <span class="badge bg-secondary rounded-pill">
                    <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                </span>
                </li>
                <?php endforeach; ?>
            </ul>
          </div>
          <?php else: ?>
          <div class="alert alert-info">Aucun historique disponible pour ce cours.</div>
          <?php endif; ?>
      </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/lightgallery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/plugins/zoom/lg-zoom.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/plugins/thumbnail/lg-thumbnail.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const gallery = document.getElementById('lightgallery');
        if (gallery) {
            lightGallery(gallery, {
                selector: 'div[data-src]',
                plugins: [lgZoom, lgThumbnail],
                thumbnail: true,
                zoom: true,
                download: true,
                mobileFirst: true,
                mode: 'lg-slide',
                hideBarsDelay: 2000,
                closable: true,
                escKey: true,
                swipeThreshold: 50,
                enableDrag: true,
                enableSwipe: true,
                fullScreen: true,
                actualSize: false,
                thumbWidth: 80,
                thumbHeight: '60px',
                showThumbByDefault: false
            });
        }
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Initialisation de LightGallery
        const gallery = document.getElementById('lightgallery');
        if (gallery) {
            lightGallery(gallery, {
                selector: '.gallery-item[data-src]',
                plugins: [lgZoom, lgThumbnail],
                thumbnail: true,
                download: true,
                zoom: true,
                mobileFirst: true,
                mode: 'lg-slide',
                hideBarsDelay: 2000,
                closable: true,
                escKey: true,
                swipeThreshold: 50,
                enableDrag: true,
                enableSwipe: true,
                fullScreen: true,
                actualSize: false,
                thumbWidth: 80,
                thumbHeight: '60px',
                showThumbByDefault: false

            });
        }

        // Pagination (affiche 6 items par page)
        const itemsPerPage = 6;
        const items = document.querySelectorAll("#lightgallery .gallery-item");
        const pagination = document.getElementById("galleryPagination");

        function showPage(page) {
            let start = (page - 1) * itemsPerPage;
            let end = start + itemsPerPage;

            items.forEach((item, index) => {
                item.style.display = (index >= start && index < end) ? "block" : "none";
            });
        }

        function setupPagination() {
            let pageCount = Math.ceil(items.length / itemsPerPage);
            pagination.innerHTML = "";

            for (let i = 1; i <= pageCount; i++) {
                let li = document.createElement("li");
                li.className = "page-item";
                li.innerHTML = `<a class="page-link" href="#">${i}</a>`;

                li.addEventListener("click", function (e) {
                    e.preventDefault();
                    document.querySelectorAll("#galleryPagination li").forEach(l => l.classList.remove("active"));
                    li.classList.add("active");
                    showPage(i);
                });

                pagination.appendChild(li);
            }

            if (pagination.firstChild) pagination.firstChild.classList.add("active");
            showPage(1);
        }

        if (items.length > 0) setupPagination();
    });
</script>

<!-- course evaluation form -->
<script>
    $(document).ready(function() {
        $('#courseval-form').submit(function(e){
            e.preventDefault();

            const $form = $(this);
            const $btn = $form.find('button[type="submit"]');
            const originalHtml = $btn.html();

            $btn.prop('disabled', true).html(`
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Validation en cours...
            `);

            $.ajax({
                url: './courses/treatment.php?action=validate-courseval',
                method: 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: function(res) {
                    console.log(res);
                    const cls = res.success ? 'alert-success' : 'alert-danger';
                    $('#response-message').html(`<div class="alert ${cls}">${res.message}</div>`);

                    if (res.success) {
                        $form.hide();
                        setTimeout(() => location.reload(), 2500);
                    } else {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function(xhr) {
                    $('#response-message').html(`<div class="alert alert-danger">Erreur serveur : ${xhr.status}</div>`);
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    });
</script>

<script>
    // Gestion du formulaire upload
    $(document).ready(function () {
        $('#upload-files').on('submit', function(e) {
            e.preventDefault();
            let form = this;
            let formData = new FormData(form);
            let btn = $(form).find("button[type=submit]");
            let btnText = btn.find("#btn-text");
            let btnSpinner = btn.find("#btn-spinner");

            // Désactiver le bouton
            btn.prop("disabled", true);
            btnText.addClass("d-none");
            btnSpinner.removeClass("d-none");

            $.ajax({
                url: './courses/treatment.php?action=add-files',
                type: 'POST',
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                success: function (response) {
                    let messageClass = response.success ? 'alert-success' : 'alert-danger';
                    let message = response.message || "Une erreur est survenue.";

                    $("#edit-cours-container").prepend(`
                        <div class="alert ${messageClass} text-center p-3">
                            <strong>${message}</strong>
                        </div>
                    `);

                    if (response.success) {
                        // ✅ Redirection après succès
                        setTimeout(() => {
                            window.location.href = `index.php?page=this-course&id=${formData.get('idCours')}`;
                        }, 2500);
                    }
                },
                error: function(xhr) {
                    $("#edit-cours-container").prepend(
                        `<div class="alert alert-danger text-center p-3">
                            ❌ Erreur serveur : ${xhr.status} - ${xhr.statusText}
                        </div>`
                    );
                },
                complete: function() {
                    btn.prop("disabled", false);
                    btnText.removeClass("d-none");
                    btnSpinner.addClass("d-none");
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#observations-table').DataTable({
            pageLength: 5,
            // language: {
            //     url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            // }
        });
    });
</script>

<script>
    $(document).ready(function () {
        $("input[name='valide']").change(function () {
        if ($(this).val() == "0") {
            $('#motif-container').removeClass('d-none');
        } else {
            $('#motif-container').addClass('d-none');
        }
        });

        $('#validation-form').submit(function (e) {
            e.preventDefault();

            const $btn = $(this).find('button[type="submit"]');
            const originalHtml = $btn.html();

            // Désactiver bouton + spinner
            $btn.prop('disabled', true).html(`
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Traitement...
            `);

            const formData = $(this).serialize();

            $.ajax({
                url: './courses/treatment.php?action=validate-cours',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function (res) {
                    const cls = res.success ? 'alert-success' : 'alert-danger';
                    $('#response-message').html(`<div class="alert ${cls}">${res.message}</div>`);

                    if (res.success) {
                        $('#validation-form').hide();
                        setTimeout(() => {
                            location.reload();
                        }, 3000);
                    } else {
                        // Réactiver bouton si échec logique
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function (xhr) {
                    $('#response-message').html(`<div class="alert alert-danger">Erreur serveur : ${xhr.status}</div>`);
                    // Réactiver bouton si erreur serveur
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function () {

        // 📌 Génération QR Code en AJAX
        $(document).on("click", "#generate-qr-btn", function(e) {
            e.preventDefault();
            let btn = $(this);
            btn.prop("disabled", true).html('<i class="mdi mdi-loading mdi-spin"></i> Génération...');
            $.ajax({
                url: './courses/treatment.php?action=generate-qr',
                type: 'POST',
                data: { cours_id: <?= json_encode($cours['id']) ?> },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        $('#qr-preview').html(`
                            <img src="${res.qr_path}" class="img-fluid mb-3" style="max-height:200px;">
                            <a href="${res.qr_path}" download class="btn btn-success w-100">
                                <i class="mdi mdi-download"></i> Télécharger
                            </a>
                        `).fadeIn();
                        btn.hide();
                    } else {
                        alert(res.message);
                        btn.prop("disabled", false).html('<i class="mdi mdi-qrcode"></i> Générer le QR Code');
                    }
                }
            });
        });

        // 📌 Lightbox initialisation
        const lightbox = GLightbox({
            selector: '.glightbox',
            touchNavigation: true,
            loop: true,
            zoomable: true
        });

        // 📌 Lazy Load Photos
        let photoPage = 1;
        const perPage = 8;

        function loadPhotos() {
            $.ajax({
                url: './courses/treatment.php?action=get-photos',
                type: 'GET',
                data: { cours_id: <?= json_encode($cours['id']) ?>, page: photoPage, limit: perPage },
                dataType: 'json',
                success: function(res) {
                if (res.success && res.photos.length > 0) {
                    res.photos.forEach(photo => {
                        $("#photos-grid").append(`
                            <div class="col-6 col-sm-4 col-md-3 text-center mb-3">
                                <a href="${photo.url_fichier}" 
                                class="photo-lightbox d-block" 
                                data-filename="${photo.nom_fichier}">
                                    <img src="${photo.url_fichier}" 
                                        class="img-fluid rounded shadow-sm" 
                                        style="object-fit:cover; height:150px; width:100%;">
                                </a>
                            </div>
                        `);
                    });
                    page++;
                } else {
                    $("#load-more-photos").hide();
                }
                }
            });
        }

        // Lazy load au clic
        $("#load-more-photos").on("click", function () {
            loadPhotos();
        });

        // Ouverture lightbox (modal bootstrap)
        $(document).on("click", ".photo-lightbox", function (e) {
            e.preventDefault();
            const imgSrc = $(this).attr("href");
            const fileName = $(this).data("filename");
            $("#lightboxImage").attr("src", imgSrc);
            $("#lightboxDownload").attr("href", imgSrc).attr("download", fileName);
            $("#lightboxModal").modal("show");
        });

        // Premier chargement
        loadPhotos();
    });
</script>

<script>
    let obsOffset = 0;
    let obsLimit = 5;
    let courseId = $("#evaluation").data("course-id");

    function loadCourseEvaluation(reset = true) {
        if (reset) obsOffset = 0;

        $.ajax({
            url: "./courses/treatment.php?action=get-evaluation-results",
            type: "GET",
            data: { id: courseId, limit: obsLimit, offset: obsOffset },
            dataType: "json",
            success: function(data) {
                if (!data.success) return;

                // Score global
                animateNumber("#global-average", data.global_average, "%");
                animateProgress("#global-progress", data.global_average);

                // Participants
                $("#total-participants").text(data.participants_count + " participant(s)");

                // Critères
                let $table = $("#criteria-table");
                $table.empty();
                if ($.isEmptyObject(data.criteria)) {
                    $table.append(`<tr><td colspan="2" class="text-center text-muted"><em>Aucune donnée disponible</em></td></tr>`);
                } else {
                    $.each(data.criteria, function(crit, note) {
                        let badgeClass = note >= 4 ? "success" : (note >= 3 ? "warning" : "danger");
                        $table.append(`
                            <tr>
                                <td>${crit}</td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-${badgeClass}">${note}/5</span>
                                </td>
                            </tr>
                        `);
                    });
                }

                // Observations
                let $obsList = $("#observations-list");
                if (reset) $obsList.empty();
                if (data.observations.length > 0) {
                    $.each(data.observations, function(_, obs) {
                        let icon = obs.type === "fort" 
                            ? `<i class="mdi mdi-check-circle text-success me-2"></i>` 
                            : `<i class="mdi mdi-alert-circle text-warning me-2"></i>`;
                        $obsList.append(`
                            <li class="list-group-item d-flex align-items-start small">
                                ${icon}
                                <div>
                                    <div>${obs.text}</div>
                                    <small class="text-muted">${new Date(obs.date).toLocaleDateString()}</small>
                                </div>
                            </li>
                        `);
                    });
                } else if (reset) {
                    $obsList.append(`<li class="list-group-item text-center text-muted">Aucune observation</li>`);
                }

                // Bouton "Charger plus"
                if (data.has_more) {
                    $("#load-more-observations").show();
                } else {
                    $("#load-more-observations").hide();
                }
            }
        });
    }

    // Charger plus d'observations
    $("#load-more-observations button").on("click", function() {
        obsOffset += obsLimit;
        loadCourseEvaluation(false);
    });

    // Animation nombre
    function animateNumber(selector, target, suffix="") {
        let $el = $(selector);
        let start = 0;
        let step = target / 50;
        let interval = setInterval(() => {
            start += step;
            if (start >= target) {
                start = target;
                clearInterval(interval);
            }
            $el.text(Math.round(start) + suffix);
        }, 20);
    }

    // Animation progress bar
    function animateProgress(selector, target) {
        let $el = $(selector);
        let start = 0;
        let step = target / 50;
        let interval = setInterval(() => {
            start += step;
            if (start >= target) {
                start = target;
                clearInterval(interval);
            }
            $el.css("width", start + "%").attr("aria-valuenow", start);
        }, 20);
    }

    // Initial load
    $(document).ready(function() {
        loadCourseEvaluation(true);
    });
</script>

<script>
    $(function () {
        var dataFormateur = <?php echo json_encode($dataFormateur['criteria'] ?? []); ?>;
        var dataAssistant = <?php echo json_encode($dataAssistant['criteria'] ?? []); ?>;
        var CRITERES      = <?php echo json_encode(CRITERES); ?>;

        function renderRadar(criteriaData, containerId) {
            if (!criteriaData || criteriaData.length === 0) {
                $("#" + containerId).html(
                    '<div class="alert alert-light text-center w-100">Aucune donnée disponible</div>'
                );
                return;
            }

            var critereMap = {}, critereSection = {};
            $.each(CRITERES, function (section, labels) {
                $.each(labels, function (label, code) {
                    critereMap[code] = label;
                    critereSection[code] = section;
                });
            });

            var sectionGroups = {};
            $.each(criteriaData, function (i, c) {
                var code = c.critere;
                var section = critereSection[code] || 'Autres';
                if (!sectionGroups[section]) sectionGroups[section] = [];
                sectionGroups[section].push({
                    label: critereMap[code] || code,
                    value: parseFloat(c.average)
                });
            });

            var colors = [
                {bg: 'rgba(4,83,38,0.2)', border: '#045326'},
                {bg: 'rgba(250,77,6,0.2)', border: '#fa4d06'},
                {bg: 'rgba(4,83,38,0.4)', border: '#045326'},
                {bg: 'rgba(250,77,6,0.4)', border: '#fa4d06'}
            ];

            $("#" + containerId).empty();
            var colorIndex = 0;

            $.each(sectionGroups, function (sectionName, criteres) {
                var canvasId = containerId + '-' + sectionName.replace(/\s+/g, '-');
                var $col = $('<div class="col-lg-6 col-12" style="height:350px;"></div>');
                $col.append('<canvas id="' + canvasId + '"></canvas>');
                $("#" + containerId).append($col);

                new Chart($('#' + canvasId), {
                    type: 'radar',
                    data: {
                        labels: criteres.map(c => c.label),
                        datasets: [{
                            label: 'Moyenne (/5)',
                            data: criteres.map(c => c.value),
                            backgroundColor: colors[colorIndex % colors.length].bg,
                            borderColor: colors[colorIndex % colors.length].border,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { r: { beginAtZero: true, max: 5 } }
                    }
                });

                colorIndex++;
            });
        }

        // Render pour les deux blocs si dispo
        renderRadar(dataFormateur, "radar-section-container-formateur");
        renderRadar(dataAssistant, "radar-section-container-assistant");
    });
</script>

