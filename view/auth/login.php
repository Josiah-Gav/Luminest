<?php
require_once '../layout/header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'Prospect';

    if ($role === 'Maintenance_Staff') {
        header('Location: ../Maintenance_Staff/dashboard.php');
    } elseif ($role === 'Property_Manager') {
        header('Location: ../Property_Manager/dashboard.php');
    } elseif ($role === 'Admin') {
        header('Location: ../Admin/dashboard.php');
    } elseif ($role === 'Tenant') {
        header('Location: ../Tenant/dashboard.php');
    } else {
        header('Location: ../Prospect/dashboard.php');
    }
    exit;
}

require_once '../../assets/luminest.php';
?>

<main class="lm-auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="card lm-auth-card border-0">
                    <div class="card-body p-4 p-lg-5">
                        <div class="text-center mb-4">
                            <img src="/luminest/assets/Luminest.png" alt="Luminest" class="lm-auth-logo mb-3">
                            <div><span class="badge rounded-pill lm-auth-badge px-3 py-2">Member Access</span></div>
                            <h1 class="h3 fw-bold mt-3 mb-2 lm-title">Welcome back to <span>Luminest</span></h1>
                            <p class="text-secondary mb-0">Login to continue your property journey.</p>
                        </div>

                        <form method="POST" action="../../controllers/auth/auth_controller.php" class="d-grid gap-3">
                            <div>
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control lm-form-control" id="email" name="email" required>
                            </div>
                            <div>
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <input type="password" class="form-control lm-form-control" id="password" name="password" required>
                            </div>
                            <input type="hidden" name="login" value="login">
                            <button type="submit" class="btn lm-btn-primary btn-lg">Login</button>
                        </form>

                        <p class="text-center text-secondary mt-4 mb-0">
                            Don't have an account?
                            <a href="register.php" class="lm-muted-link">Create one</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../layout/footer.php'; ?>


