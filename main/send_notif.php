<?php
        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['SYSADMIN','INF'])) {
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            die;
        }

?>

<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="mb-3 fw-bold text-secondary">
      <i class="mdi mdi-bullhorn"></i> Envoyer une notification
    </h5>

    <form id="sendNotifForm">
      <div class="mb-3">
        <label class="form-label">Titre</label>
        <input type="text" name="title" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Message</label>
        <textarea name="message" class="form-control" rows="3" required></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">URL de redirection</label>
        <input type="text" name="url" class="form-control" value="#">
      </div>

      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" value="1" id="sendEmail" name="send_email">
        <label class="form-check-label" for="sendEmail">Envoyer aussi par email</label>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="mdi mdi-send"></i> Envoyer
      </button>
    </form>

    <div id="notifResult" class="mt-3"></div>
  </div>
</div>

<script>
    $("#sendNotifForm").on("submit", function(e) {
    e.preventDefault();

    $.ajax({
            url: "./main/treatment.php?action=send-notif",
        method: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(res) {
            if (res.success) {
                $("#notifResult").html('<div class="alert alert-success">'+res.message+'</div>');
                $("#sendNotifForm")[0].reset();
            } else {
                $("#notifResult").html('<div class="alert alert-danger">'+res.message+'</div>');
            }
        },
        error: function() {
            $("#notifResult").html('<div class="alert alert-danger">Erreur AJAX</div>');
        }
    });
});

</script>