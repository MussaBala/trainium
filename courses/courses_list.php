<?php
// course_list.php
if (!isset($_SESSION)) session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/__class/autoload.class.php');

// Vérification du rôle
if (($_SESSION['user']['role'] ?? '') !== 'SYSADMIN') {
    echo "<div class='container py-5'><div class='alert alert-danger'>Accès refusé. Cette page est réservée aux administrateurs système.</div></div>";
    exit;
}

$obj = new objects();
$coursList = $obj->getAllCourses();
$CourseStatus = $obj->getAllCourseStatus();

// Construire un dictionnaire des statuts pour accès rapide
$statusDict = [];
foreach ($CourseStatus as $st) {
    $statusDict[$st['id']] = $st;
}
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <h2 class="text-primary mb-2">
      <i class="mdi mdi-library-books"></i> Liste des cours
    </h2>
  </div>

  <div class="table-responsive">
    <table id="coursTable" class="table table-striped table-bordered nowrap align-middle" style="width:100%">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Code</th>
          <th>Titre</th>
          <th>Thème</th>
          <th>Type</th>
          <th>OLM</th>
          <th>Date</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($coursList as $index => $cours): 
            $olm = $obj->getOlmByCode($cours['olm']);
            $statusId = intval($cours['status']);
            $status = $statusDict[$statusId] ?? null;

            // Définition des badges selon le code statut
            $badge = '<span class="badge bg-secondary">Inconnu</span>';
            if ($status) {
                switch ($status['code']) {
                    case 'REJECTED':
                        $badge = '<span class="badge bg-danger"><i class="mdi mdi-file-document-remove"></i> Rejeté</span>';
                        break;
                    case 'DRAFT':
                        $badge = '<span class="badge bg-secondary"><i class="mdi mdi-file-document-edit"></i> Brouillon</span>';
                        break;
                    case 'APPROVED_INF':
                        $badge = '<span class="badge bg-info"><i class="mdi mdi-timer-sand"></i> Approuvé par l\'INF</span>';
                        break;
                    case 'PENDING':
                        $badge = '<span class="badge bg-warning text-dark"><i class="mdi mdi-progress-clock"></i> En cours</span>';
                        break;
                    // case 'EVAL_CLOSED':
                    //     $badge = '<span class="badge bg-dark"><i class="mdi mdi-lock"></i> Évaluation clôturée</span>';
                    //     break;
                    case 'EVAL_REVIEW':
                        $badge = '<span class="badge bg-dark"><i class="mdi mdi-account-check"></i> En revue INF</span>';
                        break;
                    case 'COURS_VALIDATED':
                        $badge = '<span class="badge bg-primary"><i class="mdi mdi-check-circle-outline"></i> Validé</span>';
                        break;
                    case 'LOCKED':
                        $badge = '<span class="badge bg-success"><i class="mdi mdi-lock-alert"></i> Verrouillé</span>';
                        break;
                }
            }

            // Mise en avant visuelle : ligne surlignée si en attente de validation
            $rowClass = ($status && $status['code'] === 'DRAFT') ? 'table-warning fw-bold' : '';
          ?>
          <tr class="<?= $rowClass ?>">
            <td><?= $index+1 ?></td>
            <td><?= htmlspecialchars($cours['code_cours']) ?></td>
            <td><?= htmlspecialchars($cours['title']) ?></td>
            <td><?= htmlspecialchars($cours['theme']) ?></td>
            <td><?= strtoupper($cours['type_cours']) ?></td>
            <td><?= strtoupper($olm['nom'] ?? '') ?></td>
            <td><?= date('d/m/Y', strtotime($cours['date_cours'])) ?></td>
            <td><?= $badge ?></td>
            <td>
              <a href="index.php?page=this-course&id=<?= $cours['id'] ?>" class="btn btn-sm btn-outline-primary">
                <i class="mdi mdi-eye-outline"></i> Voir
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th></th>
          <th>Code</th>
          <th>Titre</th>
          <th>Thème</th>
          <th>Type</th>
          <th>OLM</th>
          <th>Date</th>
          <th>Statut</th>
          <th></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>


<script>
$(document).ready(function() {
  $('#coursTable tfoot th').each(function (i) {
    if (i !== 0 && i !== 8) {
      var title = $(this).text();
      $(this).html('<input type="text" class="form-control form-control-sm" placeholder="Filtrer ' + title + '" />');
    }
  });

  var table = $('#coursTable').DataTable({
    responsive: true,
    pageLength: 10,
    order: [[6, 'desc']], // tri par date par défaut
    initComplete: function () {
      this.api().columns().every(function () {
        var that = this;
        $('input', this.footer()).on('keyup change clear', function () {
          if (that.search() !== this.value) {
            that.search(this.value).draw();
          }
        });
      });
    }
  });
});
</script>
