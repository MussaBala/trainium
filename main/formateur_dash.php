<?php

// Sécurité : Vérifier que l'utilisateur est bien FORM
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'FORM') {
    header("Location: login.php");
    exit;
}

$obj = new objects;
$user_id = $_SESSION['user']['id'];

$nom = $_SESSION['account']['nom'] ?? '';
$prenom = ucwords(strtolower($_SESSION['account']['prenom'])) ?? '';
$nomComplet = $prenom . ' ' . $nom;
$formateur = $_SESSION['account'] ?? [];
$grade = $_SESSION['account']['grade'] ?? '';
$dateDebut = $_SESSION['account']['date_deb_formateur'] ?? null;
$myCourses = $obj->getCoursByUser($user_id);

if ($dateDebut) {
    $dateObj = new DateTime($dateDebut);
    setlocale(LC_TIME, 'fr_FR.UTF-8');
    $dateDebutFormat = strftime('%d %B %Y', $dateObj->getTimestamp());
} else {
    $dateDebutFormat = 'Non défini';
}
?>

<div class="container py-4">
    <!-- Bienvenue -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-light p-4 rounded shadow-sm d-flex align-items-center">
                <div class="me-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px; height:60px; font-size:1.5rem;">
                        <i class="mdi mdi-account-circle"></i>
                    </div>
                </div>
                <div>
                    <h3 class="mb-1 text-primary">Bienvenue, <?= $nomComplet ?> 👋</h3>
                    <small class="text-muted">Grade : <strong><?= $grade ?></strong> — Formateur depuis <strong><?= $dateDebutFormat ?></strong></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <a href="index.php?page=my-courses" class="card shadow-sm h-100 border-0 text-decoration-none text-dark hover-card">
                <div class="card-body text-center">
                    <div class="text-primary mb-2"><i class="mdi mdi-book-open-page-variant" style="font-size:2rem;"></i></div>
                    <h6 class="mb-1">Mes Formations</h6>
                    <p class="fs-4 fw-bold"><?= count($myCourses) ?? 0 ?></p>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4">
            <div class="card shadow-sm h-100 border-0 text-center">
                <div class="card-body">
                    <div class="text-success mb-2"><i class="mdi mdi-timer" style="font-size:2rem;"></i></div>
                    <h6 class="mb-1">Heures totales</h6>
                    <p class="fs-4 fw-bold"><?= $formateur['heures'] ?? 0 ?> h</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <a href="index.php?page=my-documents" class="card shadow-sm h-100 border-0 text-decoration-none text-dark hover-card">
                <div class="card-body text-center">
                    <div class="text-info mb-2"><i class="mdi mdi-file-document" style="font-size:2rem;"></i></div>
                    <h6 class="mb-1">Documents</h6>
                    <p class="fs-4 fw-bold"><?= $formateur['documents_count'] ?? 0 ?></p>
                </div>
            </a>
        </div>
    </div>

    <!-- Raccourcis -->
    <div class="d-flex flex-wrap gap-2 mb-5">
        <a href="index.php?page=my-courses" class="btn btn-primary flex-fill"><i class="mdi mdi-calendar"></i> Mes sessions</a>
        <a href="index.php?page=my-documents" class="btn btn-success flex-fill"><i class="mdi mdi-file"></i> Mes documents</a>
        <a href="index.php?page=edit-user" class="btn btn-dark flex-fill"><i class="mdi mdi-cog"></i> Mon profil</a>
    </div>

    <!-- Historique -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="mdi mdi-history"></i> Dernières sessions</h5>
        </div>
        <div class="table-responsive">
            <table id="course_session" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Titre</th>
                        <th>Date</th>
                        <th>Durée</th>
                        <th>OLM Organisatrice</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($myCourses)): ?>
                        <?php foreach ($myCourses as $i => $session): 
                                $olm = $obj->getOlmByCode($session['olm']);
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($session['title']) ?></td>
                                <td><?= htmlspecialchars($session['date_cours']) ?></td>
                                <td><?= htmlspecialchars($session['duree_heure']) ?>h</td>
                                <td><?= htmlspecialchars($olm['nom']) ?></td>
                                <td>
                                    <?php if ($session['status'] === 'validée'): ?>
                                        <span class="badge bg-success">Validée</span>
                                    <?php elseif ($session['status'] === 'refusée'): ?>
                                        <span class="badge bg-danger">Refusée</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.hover-card:hover {
    transform: translateY(-4px);
    transition: 0.3s ease;
    box-shadow: 0px 4px 15px rgba(0,0,0,0.1);
}
</style>

<script>
    $(document).ready(function () {
        $('#course_session').DataTable({
            responsive: true,
            pageLength: 10
        });
    });    

</script>