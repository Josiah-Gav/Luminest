<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-accent-soft">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 fw-bold mb-2 text-accent-black">Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Tenant') ?>.</h1>
                    <p class="text-muted mb-0">Manage your maintenance requests and keep track of your property updates from one place.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3 text-accent-red">Quick actions</h2>
                    <div class="d-grid gap-2">
                        <a href="maintenance_request.php" class="btn btn-primary">Submit a maintenance request</a>
                        <a href="maintenance_history.php" class="btn btn-outline-secondary">View maintenance history</a>
                        <a href="profile.php" class="btn btn-outline-primary">View profile</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3 text-accent-blue">What you can do</h2>
                    <ul class="mb-0 ps-3 text-muted">
                        <li>Report issues quickly and clearly.</li>
                        <li>Track each request from submission to resolution.</li>
                        <li>Review updates and completion details anytime.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../layout/footer.php';
?>