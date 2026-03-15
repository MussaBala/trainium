<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
  <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
    <a class="navbar-brand brand-logo" href="../index.php"><img src="../assets/images/logo_inf_jci.png" alt="logo" /></a>
    <a class="navbar-brand brand-logo-mini" href="../index.php"><img src="../assets/images/logo_inf_jci.png" alt="logo" /></a>
  </div>
  <div class="navbar-menu-wrapper d-flex align-items-stretch">
    <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
      <span class="mdi mdi-menu"></span>
    </button>
    <ul class="navbar-nav navbar-nav-right">
      
      <li class="nav-item nav-profile dropdown">
        <a class="nav-link dropdown-toggle" id="profileDropdown" href="?page=my-profile" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="nav-profile-img">
              <img src="../__files/<?= $_SESSION['account']['id'] ?>/profile/<?= $_SESSION['account']['avatar'] ?>" alt="Avatar" class="rounded" width="80">
            <span class="availability-status online"></span>
          </div>
          <div class="nav-profile-text">
            <!-- $_SESSION['user']['name'] -->
            <p class="mb-1 text-black"><?=$_SESSION['user']['login']?></p>
          </div>
        </a>
        <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
          <a class="dropdown-item" href="../index.php?page=edit-user">
            <i class="mdi mdi-tools me-2 text-success"></i> Mon Profil </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="../auth/logout.php">
            <i class="mdi mdi-logout me-2 text-primary"></i> Deconnexion </a>
        </div>
      </li>

      <li class="nav-item d-none d-lg-block full-screen-link">
        <a class="nav-link">
          <i class="mdi mdi-fullscreen" id="fullscreen-button"></i>
        </a>
      </li>

      <li class="nav-item dropdown">
        <a class="nav-link count-indicator dropdown-toggle" id="messageDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="mdi mdi-email-outline"></i>
          <span class="count-symbol bg-warning"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list" aria-labelledby="messageDropdown">
          <h6 class="p-3 mb-0">Messages</h6>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item preview-item">
            <div class="preview-thumbnail">
              <img src="../__files/<?= $_SESSION['account']['id'] ?>/profile/<?= $_SESSION['account']['avatar'] ?>" alt="image" class="profile-pic">
            </div>
            <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
              <h6 class="preview-subject ellipsis mb-1 font-weight-normal">Mark send you a message</h6>
              <p class="text-gray mb-0"> 1 Minutes ago </p>
            </div>
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item preview-item">
            <div class="preview-thumbnail">
              <img src="../__files/<?= $_SESSION['account']['id'] ?>/profile/<?= $_SESSION['account']['avatar'] ?>" alt="image" class="profile-pic">
            </div>
            <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
              <h6 class="preview-subject ellipsis mb-1 font-weight-normal">Cregh send you a message</h6>
              <p class="text-gray mb-0"> 15 Minutes ago </p>
            </div>
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item preview-item">
            <div class="preview-thumbnail">
              <img src="../__files/<?= $_SESSION['account']['id'] ?>/profile/<?= $_SESSION['account']['avatar'] ?>" alt="image" class="profile-pic">
            </div>
            <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
              <h6 class="preview-subject ellipsis mb-1 font-weight-normal">Profile picture updated</h6>
              <p class="text-gray mb-0"> 18 Minutes ago </p>
            </div>
          </a>
          <div class="dropdown-divider"></div>
          <h6 class="p-3 mb-0 text-center">4 new messages</h6>
        </div>
      </li>

      <!-- Icone cloche dans la navbar -->
      <li class="nav-item dropdown">
          <a class="nav-link position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="mdi mdi-bell-outline fs-4"></i>
            <span id="notif-count"
                  class="notif-badge badge rounded-pill bg-danger d-none">
            </span>
          </a>
          <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0" aria-labelledby="notifDropdown" style="width: 320px; max-width: 100%;">
              
              <!-- En-tête -->
              <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-light">
                  <span class="fw-semibold">Notifications</span>
                  <button id="mark-all-read" class="btn btn-sm btn-link text-decoration-none text-primary">
                      Tout marquer comme lu
                  </button>
              </div>

              <!-- Liste des notifications -->
              <div id="notif-list" class="list-group list-group-flush" style="max-height: 350px; overflow-y: auto;">
                  <div class="text-center text-muted small p-3">Aucune notification</div>
              </div>

              <!-- Footer -->
              <div class="text-center py-2 border-top bg-light">
                  <a href="../?page=all-notifications" class="small text-decoration-none">Voir toutes les notifications</a>
              </div>
          </div>
      </li>


      <li class="nav-item nav-logout d-none d-lg-block">
        <a class="nav-link" href="../auth/logout.php">
          <i class="mdi mdi-power"></i>
        </a>
      </li>
    </ul>
    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
      <span class="mdi mdi-menu"></span>
    </button>
  </div>
</nav>