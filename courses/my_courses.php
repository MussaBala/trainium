<?php

$obj = new objects();
$userId = $_SESSION['user']['id'];
$coursList = $obj->getCoursByUser($userId); // méthode à créer si besoin
$coursAssistList = $obj->getCourseByAssistant($userId);
$CourseStatus = $obj->getAllCourseStatus();

// Construire un dictionnaire des statuts pour accès rapide
$statusDict = [];
foreach ($CourseStatus as $st) {
    $statusDict[$st['id']] = $st;
}

?>

<div class="container py-5">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
    <h2 class="mb-3 mb-md-0 text-primary">
      <i class="mdi mdi-library-books"></i> Mes cours déclarés
    </h2>

    <!-- Barre de recherche -->
    <div class="input-group w-100 w-md-auto" style="max-width: 400px;">
      <input type="text" class="form-control" id="search-course" placeholder="🔍 Rechercher un cours..." onkeyup="filterCourses()">
    </div>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="../index.php?page=add-course" class="btn btn-secondary btn-sm">
      <i class="mdi mdi-book-plus"></i> Ajouter Cours
    </a>
  </div>


  <!-- Grille des cours -->
  <div class="row" id="courses-container">
    <?php 
        foreach ($coursList as $cours): 
          $olm = $obj->getOlmByCode($cours['olm']);
            $statusId = intval($cours['status']);
            $status = $statusDict[$statusId] ?? null;
            
            // Définition des badges selon le code statut
            $badgeStatut = '<span class="badge bg-secondary">Inconnu</span>';
            if ($status) {
                switch ($status['code']) {
                    case 'REJECTED':
                        $badgeStatut = '<span class="badge bg-danger"><i class="mdi mdi-file-document-remove"></i> Rejeté</span>';
                        break;
                    case 'DRAFT':
                        $badgeStatut = '<span class="badge bg-secondary"><i class="mdi mdi-file-document-edit"></i> Brouillon</span>';
                        break;
                    case 'APPROVED_INF':
                        $badgeStatut = '<span class="badge bg-info"><i class="mdi mdi-timer-sand"></i> Approuvé par l\'INF</span>';
                        break;
                    case 'PENDING':
                        $badgeStatut = '<span class="badge bg-warning text-dark"><i class="mdi mdi-progress-clock"></i> En cours</span>';
                        break;
                    case 'EVAL_CLOSED':
                        $badge = '<span class="badge bg-dark"><i class="mdi mdi-lock"></i> Évaluation clôturée</span>';
                        break;
                    case 'EVAL_REVIEW':
                        $badgeStatut = '<span class="badge bg-dark"><i class="mdi mdi-account-check"></i> En revue INF</span>';
                        break;
                    case 'COURS_VALIDATED':
                        $badgeStatut = '<span class="badge bg-primary"><i class="mdi mdi-check-circle-outline"></i> Validé</span>';
                        break;
                    case 'LOCKED':
                        $badgeStatut = '<span class="badge bg-success"><i class="mdi mdi-lock-alert"></i> Verrouillé</span>';
                        break;
                }
            }
    ?>
      <div class="col-md-6 col-lg-4 mb-4 course-card">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex flex-column justify-content-between">
                <div class="mb-3">
                    <strong><i class="mdi mdi-check-decagram"></i>Statut du cours :</strong>
                    <?= $badgeStatut ?>
                </div>

            <div>
              <h2 class="card-title text-primary"><?= htmlspecialchars($cours['title']) ?></h2>
              <h4 class="card-title text-primary"><?= htmlspecialchars($cours['theme']) ?></h4>

              <ul class="list-unstyled small mb-3">
                <li>
                  <i class="mdi mdi-tag-outline text-muted me-1"></i>
                  <strong>Type :</strong> <?= strtoupper($cours['type_cours'] ?? 'N/A') ?>
                </li>
                <li>
                  <i class="mdi mdi-school-outline text-muted me-1"></i>
                  <strong>OLM :</strong> <?= strtoupper($olm['nom']) ?>
                </li>
                <li>
                  <i class="mdi mdi-calendar-range text-muted me-1"></i>
                  <strong>Date :</strong> <?= date('d/m/Y', strtotime($cours['date_cours'])) ?>
                </li>
              </ul>
            </div>

            <a href="index.php?page=this-course&id=<?= $cours['id'] ?>" class="btn btn-outline-primary w-100 mt-auto">
              <i class="mdi mdi-eye-outline"></i> Voir le cours
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Aucun résultat -->
  <div class="text-center text-muted d-none" id="no-result">
    <i class="mdi mdi-alert-circle-outline fs-4"></i>
    <p>Aucun cours trouvé pour cette recherche.</p>
  </div>
</div>


<script>
  function filterCourses() {
    const input = document.getElementById("search-course").value.toLowerCase();
    const cards = document.querySelectorAll(".course-card");
    let count = 0;

    cards.forEach(card => {
      const text = card.textContent.toLowerCase();
      const match = text.includes(input);
      card.style.display = match ? "block" : "none";
      if (match) count++;
    });

    document.getElementById("no-result").classList.toggle("d-none", count > 0);
  }
</script>
