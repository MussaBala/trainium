<?php
function renderEvalBlock($cours, $data, $type, $isAdmin) {
    if (!$data) {
        echo '<div class="alert alert-info">Aucune évaluation disponible.</div>';
        return;
    }
?>
    <!-- Résultats globaux -->
    <div class="card shadow-sm mb-4">
        <div class="card-body text-center">
            <h5 class="mb-3 text-secondary">
                <i class="mdi mdi-chart-bar"></i> Résultats de l'évaluation <?= $type === "assistant" ? "(Assistant)" : "" ?>
            </h5>

            <?php if ($cours['status'] == 4 && $isAdmin): ?>
            <div class="card shadow-sm mt-5">
            <div class="card-body">
                <h5 class="mb-3 text-info">
                <i class="mdi mdi-check-decagram"></i> Validation globale du cours
                </h5>
                <div id="response-message" class="mb-3"></div>

                <form id="courseval-form">
                    <input type="hidden" name="cours_id" value="<?= $cours['id'] ?>">
                    <input type="hidden" name="userId" value="<?= $_SESSION['user']['id'] ?>">
                    <button type="submit" class="btn btn-success">
                        <i class="mdi mdi-check-circle"></i> Valider l'évaluation (formateur + assistant)
                    </button>
                </form>
            </div>
            </div>
            <?php endif; ?>

            <!-- Score global -->
            <div id="evaluation-summary-<?= $type ?>" class="mb-3">
                <div class="display-6 fw-bold text-primary">                            
                    <?= $data['global_average'] ?? '--' ?>
                </div>
                <div class="small text-muted">Note globale sur 100</div>
            </div>

            <!-- Progress bar -->
            <?php $globalWidth = min($data['global_average'] ?? 0, 100); ?>
            <div class="progress mx-auto mb-3" style="height: 14px; max-width: 280px;">
                <div class="progress-bar bg-gradient"
                    role="progressbar" style="width: <?= $globalWidth ?>%;"
                    aria-valuenow="<?= $globalWidth ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>

            <!-- Nb participants -->
            <div class="small fw-semibold text-muted">
                <?= $data['participants_count'] ?? 0 ?> participant(s)
            </div>
        </div>
    </div>

    <!-- Tableau critères -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h6 class="mb-3 fw-bold text-secondary">
                <i class="mdi mdi-format-list-bulleted"></i> Moyenne par critère
            </h6>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div id="radar-section-container-<?= $type ?>" class="row g-4"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Observations -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h6 class="mb-3 fw-bold text-secondary">
                <i class="mdi mdi-comment-text-outline"></i> Observations
            </h6>
            <div class="table-responsive">
                <table id="observations-table-<?= $type ?>" class="table table-hover table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Observation</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['observations'])): ?>
                            <?php foreach ($data['observations'] as $obs): ?>
                                <tr>
                                    <td><?= date("d/m/Y H:i", strtotime($obs['date'])) ?></td>
                                    <td><?= htmlspecialchars($obs['text']) ?></td>
                                    <td><?= $obs['type'] == 'fort' ? '💡 Point fort' : '⚠️ Amélioration' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center text-muted">Aucune observation</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>
