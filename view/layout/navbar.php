<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
      <img src="/luminest/assets/Luminesticon.png" alt="Logo" width="30" height="24" class="d-inline-block align-text-top">
      Luminest
    </a>
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    ?>

    <?php
    if ($_SESSION['role'] == 'Tenant'):
    ?>
    <ul class="navbar-nav flex-row flex-wrap align-items-center gap-3 ms-auto mb-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="../Tenant/dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="../Tenant/maintenance_request.php">Request Maintenance</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="../Tenant/maintenance_history.php">Maintenance History</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="../Tenant/profile.php">Profile</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="../auth/logout.php">Logout</a>
        </li>
    </ul>
    <?php
    endif;
    ?>
  </div>
</nav>