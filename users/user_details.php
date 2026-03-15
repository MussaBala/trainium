<?php
$obj = new objects;

// Vérifie si on est sur "mon profil" ou "détail d'un autre utilisateur"
$page = $_GET['page'] ?? '';

if ($page === 'my-profile') {
    // Cas 1 : Profil de l'utilisateur connecté
    if (!isset($_SESSION['account']['id'])) {
        // Pas de session => on redirige vers login
        header("Location: /?page=login&error=not_logged_in");
        exit;
    }
    $idAcc = intval($_SESSION['account']['id']);
} else {
    // Cas 2 : Profil d'un autre utilisateur
    if (!isset($_GET['idAcc']) || !is_numeric($_GET['idAcc'])) {
        // Mauvaise URL => redirection avec message d'erreur
        header("Location: /?page=list-user&error=invalid_id");
        exit;
    }
    $idAcc = intval($_GET['idAcc']);
}

// Récupération du compte
$account = $obj->getAccountById($idAcc);

if (!$account) {
    // Utilisateur introuvable => message stylé
    echo '
    <div class="container py-5">
        <div class="alert alert-danger text-center shadow-sm">
            <i class="mdi mdi-account-off"></i> 
            <strong>Profil introuvable</strong><br>
            L\'utilisateur demandé n\'existe pas ou a été supprimé.
        </div>
        <div class="text-center mt-3">
            <a href="/?page=list-user" class="btn btn-outline-primary">
                <i class="mdi mdi-arrow-left"></i> Retour à la liste des utilisateurs
            </a>
        </div>
    </div>';
    exit;
}

// Définition UID (optionnel)
$uid = $account['UID'] ?? null;

$role = $_SESSION['user']['role'];
// Formatage des données
$fullName = htmlspecialchars($account['nom'] . ' ' . $account['prenom']);
$email = htmlspecialchars($account['email']);
$telephone = htmlspecialchars($account['telephone']);
$olmDet = $obj->getOlmByCode($account['olm']);
$olm = htmlspecialchars($olmDet['nom']);
$gradeData = $obj->getGradeByCode($account['grade']);
$grade = htmlspecialchars($gradeData['libelle']);

$dateObj = new DateTime($account['date_deb_formateur']);
setlocale(LC_TIME, 'fr_FR.UTF-8');
$dateDebut = strftime('%d %B %Y', $dateObj->getTimestamp());

$statut = (int) $account['validate'];

switch ($statut) {
    case 1:
        $badge = '<span class="badge bg-success">Validé</span>';
        break;
    case -1:
        $badge = '<span class="badge bg-danger">Refusé</span>';
        break;
    default:
        $badge = '<span class="badge bg-warning text-dark">En attente</span>';
}

?>
<div class="container py-4">
  <?php if (isset($_GET['error'])): ?>
      <?php
          $errorMsg = '';
          switch ($_GET['error']) {
              case 'not_logged_in':
                  $errorMsg = "Vous devez être connecté pour accéder à cette page.";
                  break;
              case 'invalid_id':
                  $errorMsg = "L'identifiant de l'utilisateur est invalide.";
                  break;
              case 'access_denied':
                  $errorMsg = "Vous n'avez pas les droits pour accéder à ce profil.";
                  break;
              default:
                  $errorMsg = "Une erreur est survenue.";
          }
      ?>
      <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
          <i class="mdi mdi-alert-circle-outline"></i>
          <?= htmlspecialchars($errorMsg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
      </div>
  <?php endif; ?>
  <!-- Header & Actions -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <h2 class="text-primary mb-0"><i class="mdi mdi-account-circle-outline"></i> Profil du formateur</h2>

    <div class="d-flex flex-wrap gap-2">
      <?php if ($role == 'SYSADMIN'): ?>
      <a href="../index.php?page=list-user" class="btn btn-secondary btn-sm">
        <i class="mdi mdi-arrow-left-bold"></i> Retour à la liste
      </a>
      <?php endif; ?>
      <?php if ($_SESSION['account']['id'] == $idAcc): ?>
        <a href="../index.php?page=edit-user" class="btn btn-outline-primary btn-sm">
          <i class="mdi mdi-pencil-outline"></i> Modifier mon profil
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tabs navigation -->
  <ul class="nav nav-tabs flex-wrap mb-3" id="userTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab" aria-controls="account" aria-selected="true">
        <i class="mdi mdi-account-box-outline"></i> Détails du compte
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="evolution-tab" data-bs-toggle="tab" data-bs-target="#evolution" type="button" role="tab" aria-controls="evolution" aria-selected="false">
        <i class="mdi mdi-chart-line"></i> Évolution
      </button>
    </li>
  </ul>

  <!-- Tabs content -->
  <div class="tab-content" id="userTabsContent">
    <!-- Détails compte -->
    <div class="tab-pane fade show active" id="account" role="tabpanel" aria-labelledby="account-tab">
      <div id="response-message" class="mb-3"></div>
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-sm-6">
              <strong>Nom & Prénoms :</strong> <br>
              <span class="text-dark"><?= $fullName ?></span>
            </div>
            <div class="col-sm-6">
              <strong>Email :</strong> <br>
              <span><?= $email ?></span>
            </div>
            <div class="col-sm-6">
              <strong>Téléphone :</strong> <br>
              <span><?= $telephone ?></span>
            </div>
            <div class="col-sm-6">
              <strong>OLM d'origine :</strong> <br>
              <span><?= $olm ?></span>
            </div>
            <div class="col-sm-6">
              <strong>Grade actuel :</strong> <br>
              <span><?= $grade ?></span>
            </div>
            <div class="col-sm-6">
              <strong>Date début formateur :</strong> <br>
              <span><?= $dateDebut ?></span>
            </div>
            <div class="col-sm-6">
              <strong>Statut du compte :</strong> <br>
              <span><?= $badge ?></span>
            </div>
          </div>

          <?php if ($statut === 0): ?>
            <hr>
            <div class="d-flex flex-wrap gap-2 mt-3">
              <button class="btn btn-success" onclick="submitValidation(<?= $idAcc ?>, 'valider')">
                <i class="mdi mdi-check"></i> Valider
              </button>
              <button class="btn btn-danger" onclick="submitValidation(<?= $idAcc ?>, 'refuser')">
                <i class="mdi mdi-close"></i> Refuser
              </button>
            </div>
            <div id="response-admin-message" class="mt-3"></div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Évolution -->
    <div class="tab-pane fade" id="evolution" role="tabpanel" aria-labelledby="evolution-tab">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h5 class="text-secondary"><i class="mdi mdi-information-outline"></i> Informations dévolution</h5>
          <p class="text-muted">Cette section peut contenir :</p>
          <ul class="list-unstyled ps-3">
            <li><i class="mdi mdi-format-list-bulleted me-1"></i> Formations données</li>
            <li><i class="mdi mdi-timer-outline me-1"></i> Heures cumulées</li>
            <li><i class="mdi mdi-check-decagram me-1"></i> Statut de progression</li>
            <li><i class="mdi mdi-file-document-outline me-1"></i> Fichiers pédagogiques</li>
          </ul>
          <div class="alert alert-info mt-3">
            <i class="mdi mdi-wrench"></i> Module en cours de développement...
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php 
    if ($role == 'SYSADMIN' && isset($account['UID']) || is_numeric($account['UID'])): 
        $logs = $obj->getUserLogs($uid);
  ?>
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
          <h5 class="text-secondary"><i class="mdi mdi-history"></i> Journal d'activité</h5>
          <?php if (!empty($logs)): ?>
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="user-logs">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Action</th>
                    <th>Résultat</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($logs as $i => $log): ?>
                    <tr>
                      <td><?= $i + 1 ?></td>
                      <td><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                      <td><span class="badge bg-light text-dark"><?= htmlspecialchars($log['action_type']) ?></span></td>
                      <td><?= htmlspecialchars($log['action_label']) ?></td>
                      <td><?= htmlspecialchars($log['message']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="alert alert-warning">Aucune activité enregistrée pour cet utilisateur.</div>
          <?php endif; ?>
        </div>
    </div>
  <?php endif; ?>
</div>

  <script>
    $(document).ready(function () {
          $('#user-logs').DataTable({
              responsive: true,
              pageLength: 10,
              language: {
                // url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr.json"
              }
          });
      });    
  </script>

<script>            
    function submitValidation(id, action) {
        if (!confirm(`Confirmer l'action : ${action.toUpperCase()} ?`)) return;

        $.ajax({
            type: "POST",
            url: "./users/treatment.php?action=account-validation",
            dataType: "json",
            data: { id: id, action: action },
            success: function(response) {
                // Vérifie que le serveur a bien retourné une structure attendue
                if (typeof response.success !== "undefined") {
                    const alertClass = response.success ? 'alert-success' : 'alert-danger';
                    const message = response.message || (response.success ? "Opération réussie." : "Échec de l'opération.");
                    
                    $('#response-message').html(`<div class="alert ${alertClass}">${message}</div>`);

                    if (response.success) {
                        setTimeout(() => location.reload(), 2000);
                    }
                } else {
                    $('#response-message').html(`<div class="alert alert-warning">Réponse inattendue du serveur.</div>`);
                }
            },
            error: function(xhr) {
                $('#response-admin-message').html(
                    `<div class="alert alert-danger">
                        Erreur AJAX : ${xhr.status}<br><pre>${xhr.responseText}</pre>
                    </div>`
                );
            }
        });
    }
</script>
