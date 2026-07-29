<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-semibold text-accent-black" href="#">
      <img src="/luminest/assets/Luminesticon.png" alt="Logo" width="30" height="24" class="d-inline-block align-text-top me-2">
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

    <?php
    if ($_SESSION['role'] == 'Maintenance_Staff'):
    ?>
    <ul class="navbar-nav flex-row flex-wrap align-items-center gap-3 ms-auto mb-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="../Maintenance_Staff/dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="../Maintenance_Staff/maintenance_requests.php">Maintenance Requests</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="../Maintenance_Staff/maintenance_history.php">Maintenance History</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="../Maintenance_Staff/profile.php">Profile</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="../auth/logout.php">Logout</a>
        </li>
    </ul>
    <?php
    endif;
    ?>

    <?php
    if ($_SESSION['role'] == 'Admin'):
    ?>
    <ul class="navbar-nav flex-row flex-wrap align-items-center gap-3 ms-auto mb-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="../Admin/dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="../Admin/user_management.php">User Management</a>
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