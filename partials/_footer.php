<footer class="footer">
  <div class="d-sm-flex justify-content-center justify-content-sm-between">
    <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2025 <a href="https://www.hakility.com/" target="_blank">HAKILITY</a>. All rights reserved.</span>
    <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with <i class="mdi mdi-heart text-danger"></i></span>
  </div>
</footer>

  <script src="<?= VENDORS ?>/js/vendor.bundle.base.js"></script>
    <!-- JS de base (Bootstrap, Popper, etc.) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
 <!-- endinject -->
    
    <!-- Plugin js for this page -->
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <!-- Bootstrap Datepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js" integrity="sha512-V1dOc+SbG9TUSz+OGDyfSUlmAHe6lsdzD2LfDwWmoi4gEipVqOvpoM/W8AIaFK9K8AXzAkFjApQz7XzAuwL/mg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="<?= JS ?>/off-canvas.js"></script>
    <script src="<?= JS ?>/misc.js"></script>
    <script src="<?= JS ?>/settings.js"></script>
    <script src="<?= JS ?>/todolist.js"></script>
    <script src="<?= JS ?>/notifications.js"></script>
    <script>
      const VAPID_PUBLIC_KEY = "<?= VAPID_PUBLIC_KEY ?>";
    </script>

    <script>
        // On rend la clé publique disponible côté JS
        window.VAPID_PUBLIC_KEY = "<?= VAPID_PUBLIC_KEY ?>";
    </script>
    <script src="<?= JS ?>/push.js"></script>

    <!-- jQuery Cookie -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js" integrity="sha512-PWqVfCIv9WcPU1DdU44fXKM60rNgkek6BdMB0E7C+ae0a1wOjKnBCd0dMlVJWswuT4oD9FbI+hrGh9CEIo2Y9A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>    <!-- endinject -->
    
    <!-- Service Worker -->
    <script>
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js')
          .then(reg => console.log('✅ Service Worker registered'))
          .catch(err => console.error('❌ Service Worker error:', err));
      }
    </script>
        <!-- Custom js for this page -->
    <script src="<?= JS ?>/dashboard.js"></script>

    <script>
      function loadNotifications() {
          $.get("api/get-notifications.php", { action: "fetch" }, function(res) {
              console.log(res);

              let list = $("#notif-list");
              let count = $("#notif-count");

              list.empty();

              if (!res.success || res.notifications.length === 0) {
                  list.html('<div class="text-center text-muted py-2">Aucune notification</div>');
                  count.addClass("d-none");
              } else {
                  res.notifications.forEach(n => {
                      let item = `
                        <a href="${n.url}" 
                          class="list-group-item list-group-item-action d-flex justify-content-between align-items-start ${n.is_read == 0 ? 'fw-bold' : ''}">
                          <div class="me-auto">
                            <div>${n.title}</div>
                            <small class="text-muted">${n.message}</small>
                          </div>
                          <small class="text-muted">${n.created_at}</small>
                        </a>
                      `;
                      list.append(item);
                  });

                  // Badge nombre non-lus
                  if (res.unread_count > 0) {
                      count.text(res.unread_count).removeClass("d-none");
                  } else {
                      count.addClass("d-none");
                  }
              }
          }, "json");
      }

      // 🔄 Rechargement périodique
      setInterval(loadNotifications, 30000); // toutes les 30s
      $(document).ready(loadNotifications);
    </script>

