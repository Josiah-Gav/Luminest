<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-accent-soft">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 fw-bold mb-2 text-accent-black">Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Maintenance Staff') ?>.</h1>
                    <p class="text-muted mb-0">Review assigned work, update request status, and keep maintenance operations moving from one place.</p>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-grid gap-2 d-md-flex flex-wrap">
                        <a href="maintenance_requests.php" class="btn btn-primary">View assigned requests</a>
                        <a href="maintenance_history.php" class="btn btn-outline-secondary">Open maintenance history</a>
                        <a href="profile.php" class="btn btn-outline-primary">View profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../layout/footer.php';
?>