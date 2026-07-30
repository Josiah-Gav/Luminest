<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/auth/auth_controller.php';

$profile = $user->getByID($_SESSION['user_id'] ?? 0);
$profileEmail = $profile['email'] ?? ($_SESSION['email'] ?? '');
$profileName = $profile['full_name'] ?? ($_SESSION['username'] ?? '');
$profilePhone = $profile['phone_number'] ?? ($_SESSION['phone_number'] ?? '');
$profileRole = $profile['role'] ?? ($_SESSION['role'] ?? 'Tenant');
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 fw-bold mb-2 text-accent-black">Profile</h1>
                    <p class="text-muted mb-4">Welcome, <?= htmlspecialchars($profileName ?: 'Tenant') ?>! Here you can review and update your account details.</p>

                    <?php if (isset($_GET['success']) && $_GET['success'] === 'profile_updated'): ?>
                        <div class="alert alert-success">Profile updated successfully.</div>
                    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'update_failed'): ?>
                        <div class="alert alert-danger">Failed to update profile.</div>
                    <?php endif; ?>

                    <form method="POST" action="../../controllers/auth/profile_controller.php" class="row g-3">
                        <input type="hidden" name="update_profile" value="1">

                        <div class="col-md-6">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" value="<?= htmlspecialchars($profileName) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($profileEmail) ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <input type="text" id="role" class="form-control" value="<?= htmlspecialchars($profileRole) ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" id="phone_number" name="phone_number" class="form-control" value="<?= htmlspecialchars($profilePhone) ?>" required>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Save changes</button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">Back to dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 bg-accent-soft">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold text-accent-red mb-2">Need help?</h2>
                    <p class="text-muted small mb-3">Use the maintenance request page whenever you need assistance with your property.</p>
                    <a href="maintenance_request.php" class="btn btn-primary w-100">Create a request</a>
                </div>
            </div>
        </div>
    </div>
</div>