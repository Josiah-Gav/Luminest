<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-accent-soft">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 fw-bold mb-2 text-accent-black">Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>.</h1>
                    <p class="text-muted mb-0">Manage users, review account activity, and keep the platform organized from one place.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3 text-accent-red">Quick actions</h2>
                    <div class="d-grid gap-2">
                        <a href="user_management.php" class="btn btn-primary">Manage users</a>
                        <a href="add_user.php" class="btn btn-outline-primary">Add a new user</a>
                        <a href="view_user.php?id=<?= (int) ($_SESSION['user_id'] ?? 0) ?>" class="btn btn-outline-secondary">View your profile</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3 text-accent-blue">What you can do</h2>
                    <ul class="mb-0 ps-3 text-muted">
                        <li>Create tenant, staff, manager, and admin accounts.</li>
                        <li>Review and update user details in one place.</li>
                        <li>Keep platform access organized and consistent.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../layout/footer.php';
?>