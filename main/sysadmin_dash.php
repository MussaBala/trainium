<?php

// Sécurité : Vérifier que l'utilisateur est bien SYSADMIN
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'SYSADMIN') {
    header("Location: login.php");
    exit;
}


$obj = new objects();

// Statistiques
$totalUsers = count($obj->getAllAccounts());
$totalValidated = count(array_filter($obj->getAllAccounts(), fn($a) => $a['validate'] == 1));
$totalPending = count(array_filter($obj->getAllAccounts(), fn($a) => $a['validate'] == 0));
$totalRefused = count(array_filter($obj->getAllAccounts(), fn($a) => $a['validate'] == -1));
// $totalLogs = count((new activities_log())->getAll());

// Nouvelles listes
$latestDraftCourses      = $obj->getLatestCoursesByStatus('DRAFT', 6);        // retourne un array
$latestEvalReviewCourses = $obj->getLatestCoursesByStatus('EVAL_REVIEW', 6);  // retourne un array

$stats = $obj->getAllStats();

$user = $_SESSION['user'] ?? [];
$account = $_SESSION['account'] ?? [];
$nomComplet = ucwords(strtolower($account['prenom'] . ' ' . $account['nom']));

$status = $obj->getAllCourseStatus();

// Helpers (badge harmonisé)
function status_badge($status) {
    switch ($status) {
        case '1':
            return '<span class="badge bg-warning text-dark"><i class="mdi mdi-file-document-edit"></i> Brouillon</span>';
        case '2':
            return '<span class="badge bg-info"><i class="mdi mdi-timer-sand"></i> Approuvé INF</span>';
        case '3':
            return '<span class="badge bg-info text-dark"><i class="mdi mdi-progress-clock"></i> En cours</span>';
        case '4':
            return '<span class="badge bg-dark"><i class="mdi mdi-account-check"></i> En revue INF</span>';
        case '5':
            return '<span class="badge bg-primary"><i class="mdi mdi-check-circle-outline"></i> Validé</span>';
        case '6':
            return '<span class="badge bg-success text-dark"><i class="mdi mdi-lock-alert"></i> Verrouillé</span>';
        default:
            return '<span class="badge bg-secondary">N/A</span>';
    }
}

// Pour tronquer proprement
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

?>

<div class="container py-3">

  <!-- Bandeau résumé -->
  <div class="row g-3 row-cols-2 row-cols-md-4 mb-3">
    <div class="col">
      <div class="card shadow-sm border-start border-4 border-primary h-100">
        <div class="card-body p-3 d-flex align-items-center">
          <i class="mdi mdi-account-clock-outline fs-2 text-primary me-2"></i>
          <div>
            <small class="text-muted d-block">En attente</small>
            <h5 class="mb-0"><?= $totalPending ?? 0 ?></h5>
            <a href="index.php?page=list-user" class="small text-decoration-none">Gérer</a>
          </div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card shadow-sm border-start border-4 border-success h-100">
        <div class="card-body p-3 d-flex align-items-center">
          <i class="mdi mdi-account-check-outline fs-2 text-success me-2"></i>
          <div>
            <small class="text-muted d-block">Validés</small>
            <h5 class="mb-0"><?= $totalValidated ?? 0 ?></h5>
            <a href="index.php?page=list-user&filter=valid" class="small text-decoration-none">Voir</a>
          </div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card shadow-sm border-start border-4 border-warning h-100">
        <div class="card-body p-3 d-flex align-items-center">
          <i class="mdi mdi-book-clock-outline fs-2 text-warning me-2"></i>
          <div>
            <small class="text-muted d-block">À valider</small>
            <h5 class="mb-0"><?= $stats['cours_pending'] ?? 0 ?></h5>
            <a href="index.php?page=courses-list&filter=draft" class="small text-decoration-none">Détails</a>
          </div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card shadow-sm border-start border-4 border-info h-100">
        <div class="card-body p-3 d-flex align-items-center">
          <i class="mdi mdi-calendar-multiselect fs-2 text-info me-2"></i>
          <div>
            <small class="text-muted d-block">Programmées</small>
            <h5 class="mb-0"><?= $stats['sessions_programmed'] ?? 0 ?></h5>
            <a href="index.php?page=courses-list&filter=pending" class="small text-decoration-none">Gérer</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Actions rapides -->
  <!-- <div class="card shadow-sm mb-3">
    <div class="card-header bg-light fw-bold py-2">⚡ Actions rapides</div>
    <div class="card-body d-flex flex-wrap gap-2">
      <a href="index.php?page=courses-list&filter=pending" class="btn btn-sm btn-outline-warning flex-fill text-start">
        <i class="mdi mdi-book-clock"></i> Cours à valider
      </a>
    </div>
  </div> -->

  <!-- Ligne 1 : Derniers DRAFT -->
  <div class="card shadow-sm mb-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <span class="fw-bold">📝 Derniers cours à valider (Brouillons)</span>
      <a href="index.php?page=courses-list&filter=draft" class="btn btn-sm btn-outline-secondary">Voir tout</a>
    </div>
    <div class="card-body">
      <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xxl-5">
        <?php if (!empty($latestDraftCourses)): ?>
          <?php foreach ($latestDraftCourses as $c): ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body p-3 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span><?= status_badge($c['status']) ?></span>
                        <small class="text-muted" title="<?= e($c['date_creation']) ?>">
                        <i class="mdi mdi-clock-outline"></i> <?= date('d/m', strtotime($c['date_creation'])) ?>
                        </small>
                    </div>

                    <!-- Titre du cours -->
                    <h6 class="mb-1 text-truncate" title="<?= e($c['title']) ?>"><?= e($c['title']) ?></h6>

                    <!-- Thème de la formation -->
                    <?php if (!empty($c['theme'])): ?>
                        <div class="small text-muted mb-2">
                        <i class="mdi mdi-lightbulb-on-outline"></i><b> <?= e($c['theme']) ?> </b>
                        </div>
                    <?php endif; ?>

                    <!-- OLM -->
                    <div class="small text-muted mb-2">
                        <i class="mdi mdi-domain"></i> <?= e($c['olm'] ?? '—') ?>
                    </div>

                    <!-- Date du cours -->
                    <div class="small mb-3">
                        <i class="mdi mdi-calendar"></i>
                        <?= !empty($c['date_cours']) ? date('d/m/Y', strtotime($c['date_cours'])) : 'Date à définir' ?>
                    </div>

                    <!-- Actions -->
                    <div class="mt-auto d-flex gap-2">
                        <a href="index.php?page=this-course&id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-primary w-100">
                        <i class="mdi mdi-eye-outline"></i> Ouvrir
                        </a>
                        <a href="index.php?page=this-course&id=<?= (int)$c['id'] ?>#validation" class="btn btn-sm btn-primary w-100">
                        <i class="mdi mdi-check-circle-outline"></i> Valider
                        </a>
                    </div>
                    </div>
                </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col"><div class="text-muted text-center py-3">Aucun brouillon récent.</div></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Ligne 2 : Derniers EVAL_REVIEW -->
  <div class="card shadow-sm mb-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <span class="fw-bold">🔎 Validation des Evaluations</span>
      <a href="index.php?page=courses-list&filter=eval_review" class="btn btn-sm btn-outline-secondary">Voir tout</a>
    </div>
    <div class="card-body">
      <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xxl-5">
        <?php if (!empty($latestEvalReviewCourses)): ?>
          <?php foreach ($latestEvalReviewCourses as $c): 
                $acc = $obj->getAccountByUID($c['UID']);
                $auteur = $acc['nom'] .' '. $acc['prenom'];
            ?>
            <div class="col">
              <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-3 d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <span><?= status_badge($c['status']) ?></span>
                    <small class="text-muted" title="<?= e($c['date_creation']) ?>">
                      <i class="mdi mdi-clock-outline"></i> <?= date('d/m', strtotime($c['date_creation'])) ?>
                    </small>
                  </div>
                  <h6 class="mb-1 text-truncate" title="<?= e($c['title']) ?>"><?= e($c['title']) ?></h6>
                  <div class="small text-muted mb-2">
                    <i class="mdi mdi-account-tie"></i> <?= e($auteur ?? 'Auteur N/A') ?>
                  </div>
                  <div class="small mb-3">
                    <i class="mdi mdi-calendar-check"></i>
                    <?= !empty($c['date_cours']) ? date('d/m/Y', strtotime($c['date_cours'])) : 'Date à définir' ?>
                  </div>
                  <div class="mt-auto d-flex gap-2">
                    <a href="index.php?page=this-course&id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-primary w-100">
                      <i class="mdi mdi-eye-outline"></i> Ouvrir
                    </a>
                    <a href="index.php?page=this-course&id=<?= (int)$c['id'] ?>#evaluation" class="btn btn-sm btn-dark w-100">
                      <i class="mdi mdi-clipboard-check-outline"></i> Valider l'evaluation
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col"><div class="text-muted text-center py-3">Aucune revue récente.</div></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Dernières activités (inchangé, juste compacté) -->
  <div class="card shadow-sm">
    <div class="card-header bg-light fw-bold py-2">🕵️ Dernières activités</div>
    <div class="table-responsive">
      <table class="table table-hover align-middle table-sm">
        <thead class="table-light">
          <tr>
            <th>Date</th>
            <th>Utilisateur</th>
            <th>Type</th>
            <th>Action</th>
            <th>Résultat</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($logs)): ?>
            <?php foreach ($logs as $log): ?>
              <tr>
                <td><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                <td><?= e($log['user_email'] ?? 'SYSTEM') ?></td>
                <td><span class="badge bg-<?= ($log['type'] === 'INFO') ? 'primary' : 'danger' ?>"><?= e($log['type']) ?></span></td>
                <td><?= e($log['label']) ?></td>
                <td class="text-truncate" style="max-width: 220px;" title="<?= e($log['message']) ?>">
                  <?= e($log['message']) ?>
                </td>
              </tr>
            <?php endforeach ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center text-muted">Aucune activité récente.</td></tr>
          <?php endif ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
