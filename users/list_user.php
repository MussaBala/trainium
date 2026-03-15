<?php
$obj = new objects();
$accounts = $obj->getAllAccounts();

    // var_dump($obj->testSMTPMail());

?>

<h3 class="mb-4">Liste des Formateurs</h3>

<div class="table-responsive">
  <table class="table table-hover table-bordered align-middle nowrap" id="user_list" style="width:100%">
      <thead class="table-light">
          <tr class="text-center">
              <th>#</th>
              <th>Nom & Prénoms</th>
              <th>Téléphone</th>
              <th>OLM d'Origine</th>
              <th>Grade</th>
              <th>Date Début Formateur</th>
              <th>Statut</th>
              <th>Actions</th>
          </tr>
      </thead>
      <tbody>
        <?php foreach ($accounts as $index => $account): ?>
            <?php
                $res = $obj->findTokenByUID($account['UID']);
                
                $id_acc       = (int) $account['id'];
                $nomComplet   = htmlspecialchars($account['nom'] . ' ' . $account['prenom']);
                $tel          = htmlspecialchars($account['telephone']);
                $olm          = $obj->getOlmByCode($account['olm']);
                $olmLabel     = htmlspecialchars($olm['nom']);
                $gradeData    = $obj->getGradeByCode(htmlspecialchars($account['grade']));
                $gradeLibelle = htmlspecialchars($gradeData['libelle']);
                $dateDebut    = htmlspecialchars($account['date_deb_formateur']);
                $statut       = (int) $account['validate'];

                // Analyse du token
                $tokenUsed = is_array($res) && isset($res['used']) ? (int) $res['used'] : null;

                // Badge combiné : statut + token
                switch (true) {
                    case $statut === 1:
                        $badge = '<span class="badge bg-primary">Validé</span>';
                        break;
                    case $statut === -1:
                        $badge = '<span class="badge bg-danger">Refusé</span>';
                        break;
                    case is_null($res):
                        $badge = '<span class="badge bg-warning">En attente - Non traité</span>';
                        break;
                    case $tokenUsed === 0:
                        $badge = '<span class="badge bg-secondary">En attente - Email envoyé</span>';
                        break;
                    default:
                        $badge = '<span class="badge bg-light text-dark">En attente - Lien expiré</span>';
                        break;
                }
            ?>
            <tr class="text-center">
                <td><?= $index + 1 ?></td>
                <td class="text-start"><?= $nomComplet ?></td>
                <td><?= $tel ?></td>
                <td><?= $olmLabel ?></td>
                <td><?= $gradeLibelle ?></td>
                <td><?= $dateDebut ?></td>
                <td><?= $badge ?></td>
                <td>
                    <a href="../?page=user-detail&idAcc=<?= $id_acc ?>" class="btn btn-sm btn-outline-primary">
                        Voir
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
      </tbody>
  </table>
</div>


<script>
  $(document).ready(function () {
        $('#user_list').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
              // url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr.json"
            }
        });
    });    
</script>
