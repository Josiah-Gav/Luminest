<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Maintenance_Staff') {
    header('Location: ../auth/login.php');
    exit;
}
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-accent-soft">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 fw-bold mb-2 text-accent-black">Profile</h1>
                    <p class="text-muted mb-0">Keep your account details and team access information organized in one place.</p>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="small text-muted">Full Name</div>
                                <div class="fw-semibold"><?= htmlspecialchars($_SESSION['username'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="small text-muted">Email Address</div>
                                <div class="fw-semibold">staff@example.com</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="small text-muted">Role</div>
                                <div class="fw-semibold"><?= htmlspecialchars($_SESSION['role'] ?? 'Maintenance Staff') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="small text-muted">Account Status</div>
                                <div class="fw-semibold text-accent-blue">Active</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="small text-muted">Phone Number</div>
                                <div class="fw-semibold">+63 917 123 4567</div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="p-3 border rounded-3">
                                <h2 class="h6 fw-bold text-accent-blue mb-2">Quick access</h2>
                                <div class="d-grid gap-2 d-md-flex">
                                    <a href="maintenance_requests.php" class="btn btn-outline-primary btn-sm">Assigned requests</a>
                                    <a href="maintenance_history.php" class="btn btn-outline-secondary btn-sm">History</a>
                                    <a href="dashboard.php" class="btn btn-outline-dark btn-sm">Back to dashboard</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../layout/footer.php';
?>