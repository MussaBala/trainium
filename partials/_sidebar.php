<?php
?>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">

    <li class="nav-item nav-profile">
      <a href="?page=my-profile" class="nav-link">
        <div class="nav-profile-image">
              <img src="../__files/<?= $_SESSION['account']['id'] ?>/profile/<?= $_SESSION['account']['avatar'] ?>" alt="Avatar" class="rounded" width="80">
          <span class="login-status online"></span>
          <!--change to offline or busy as needed-->
        </div>
        <div class="nav-profile-text d-flex flex-column">
          <span class="font-weight-bold mb-2"><?=$_SESSION['user']['login']?></span>
          <span class="text-secondary text-small"> <!-- groupe du user --></span>
        </div>
        <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="../index.php">
        <span class="menu-title">Tableau de bord</span>
        <i class="mdi mdi-home menu-icon"></i>
      </a>
    </li>
    <?php
      $role = $_SESSION['user']['role'];
      switch ($role) {
        case 'SYSADMIN':
          include_once '_menuSysadmin.php';
          break;
        
        case 'ADMIN':
          include_once '_menuAdmin.php';
          break;
        
        case 'FORM':
          include_once '_menuFormateur.php';
          break;
        
        // default:
        //   '';
        //   break;
      }
    ?>
    <li class="nav-item">
      <a class="nav-link" href="../auth/logout.php">
        <span class="menu-title text-alert">Déconnexion</span>
        <i class="mdi mdi-power menu-icon"></i>
      </a>
    </li>
  </ul>
</nav>