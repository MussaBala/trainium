<?php

// var_dump($_SESSION);
// exit;
// // Sécurité : Vérifier que l'utilisateur est bien ADMIN
// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ADMIN') {
//     // header("Location: login.php");
//     exit;
// }

$admin = $_SESSION['user'] ?? [];
$nomComplet = ucwords(strtolower($admin['prenom'] . ' ' . $admin['nom']));
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h3 class="text-primary">👨‍💼 Tableau de bord SYSADMIN</h3>
            <p class="text-muted">Bienvenue <strong><?= $nomComplet ?></strong>. Voici les informations clés de la plateforme.</p>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow border-left-primary">
                <div class="card-body">
                    <h5 class="card-title text-primary">👥 Comptes en attente</h5>
                    <p class="card-text fs-4"><?= $stats['pending_accounts'] ?? 0 ?></p>
                    <a href="index.php?page=list-user" class="btn btn-sm btn-outline-primary">Voir les comptes</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow border-left-success">
                <div class="card-body">
                    <h5 class="card-title text-success">✔️ Comptes validés</h5>
                    <p class="card-text fs-4"><?= $stats['validated_accounts'] ?? 0 ?></p>
                    <a href="index.php?page=list-user&filter=valid" class="btn btn-sm btn-outline-success">Afficher</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow border-left-info">
                <div class="card-body">
                    <h5 class="card-title text-info">🧑‍🏫 Formateurs actifs</h5>
                    <p class="card-text fs-4"><?= $stats['active_trainers'] ?? 0 ?></p>
                    <a href="index.php?page=manage-trainers" class="btn btn-sm btn-outline-info">Détails</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow border-left-warning">
                <div class="card-body">
                    <h5 class="card-title text-warning">📝 Sessions programmées</h5>
                    <p class="card-text fs-4"><?= $stats['sessions_programmed'] ?? 0 ?></p>
                    <a href="index.php?page=sessions" class="btn btn-sm btn-outline-warning">Gérer</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Raccourcis SYSADMIN -->
    <div class="row mb-5">
        <div class="col-md-3 mb-3">
            <a href="index.php?page=add-user" class="btn btn-outline-primary w-100">➕ Ajouter un utilisateur</a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="index.php?page=logs" class="btn btn-outline-dark w-100">📋 Logs d’activités</a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="index.php?page=system-settings" class="btn btn-outline-secondary w-100">⚙️ Paramètres système</a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="index.php?page=notifications" class="btn btn-outline-info w-100">🔔 Notifications</a>
        </div>
    </div>

    <!-- Journal d'activité récent -->
    <div class="row">
        <div class="col">
            <h5 class="text-secondary">🕵️ Dernières activités</h5>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Utilisateur</th>
                        <th>Type</th>
                        <th>Action</th>
                        <th>Résultat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $i => $log): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                                <td><?= htmlspecialchars($log['user_email'] ?? 'SYSTEM') ?></td>
                                <td><span class="badge bg-light text-dark"><?= $log['type'] ?></span></td>
                                <td><?= $log['label'] ?></td>
                                <td><?= $log['message'] ?></td>
                            </tr>
                        <?php endforeach ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Aucune activité récente.</td>
                        </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
