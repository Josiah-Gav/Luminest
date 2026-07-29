<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/auth/auth_controller.php';

$profile = $user->getByID($_SESSION['user_id']);
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 fw-bold mb-2 text-accent-black">Profile</h1>
                    <p class="text-muted mb-4">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Tenant') ?>! Here you can review your account details and manage your tenant information.</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="text-muted small">Full Name</div>
                                <div class="fw-semibold"><?= htmlspecialchars($_SESSION['username'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="text-muted small">Email Address</div>
                                <div class="fw-semibold">tenant@example.com</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="text-muted small">Role</div>
                                <div class="fw-semibold"><?= htmlspecialchars($_SESSION['role'] ?? 'Tenant') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="text-muted small">Account Status</div>
                                <div class="fw-semibold text-accent-blue">Active</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="text-muted small">Phone Number</div>
                                <div class="fw-semibold">+63 912 345 6789</div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3">
                                <h2 class="h6 fw-bold text-accent-black mb-2">Tenant summary</h2>
                                <p class="text-muted small mb-0">You can manage service requests, review past issues, and keep track of progress from this area.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3">
                                <h2 class="h6 fw-bold text-accent-red mb-2">Property details</h2>
                                <p class="text-muted small mb-0">Property address, unit number, and occupancy updates will appear here as your account is linked to a property.</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 border rounded-3">
                                <h2 class="h6 fw-bold text-accent-blue mb-2">Quick access</h2>
                                <div class="d-grid gap-2 d-md-flex">
                                    <a href="maintenance_request.php" class="btn btn-outline-primary btn-sm">New request</a>
                                    <a href="maintenance_history.php" class="btn btn-outline-secondary btn-sm">View history</a>
                                    <a href="dashboard.php" class="btn btn-outline-dark btn-sm">Back to dashboard</a>
                                </div>
                            </div>
                        </div>
                    </div>
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

<?php
if(isset($_GET['success']) && $_GET['success'] == 'profile_updated') {
    echo "<p style='color: green;'>Profile updated successfully.</p>";
} elseif(isset($_GET['error']) && $_GET['error'] == 'update_failed') {
    echo "<p style='color: red;'>Failed to update profile.</p>";
} elseif(isset($_GET['success']) && $_GET['success'] == 'password_changed') {
    echo "<p style='color: green;'>Password changed successfully.</p>";
} elseif(isset($_GET['error']) && $_GET['error'] == 'password_change_failed') {
    echo "<p style='color: red;'>Failed to change password.</p>";
} elseif(isset($_GET['error']) && $_GET['error'] == 'invalid_current_password') {
    echo "<p style='color: red;'>Invalid current password.</p>";
}
?>
<form method="POST" action="../../controllers/auth/profile_controller.php">
    <input type="hidden" name="user_id" value="<?=$profile['user_id']?>">
    <label for="full_name">Full Name:</label>
    <input type="text" id="full_name" name="full_name" value="<?=$profile['full_name']?>" required><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?=$profile['email']?>" required readonly><br>

    <label for="phone_number">Phone Number:</label>
    <input type="text" id="phone_number" name="phone_number" value="<?=$profile['phone_number']?>" required><br>

    
    <button type="submit" name="update_profile">Update Profile</button>
</form>

<form method="POST" action="../../controllers/auth/profile_controller.php">
    <label for="current_password">Current Password:</label>
    <input type="password" id="current_password" name="current_password" required><br>
    <label for="new_password">New Password:</label>
    <input type="password" id="new_password" name="new_password" required><br>
    <button type="submit" name="change_password">Change Password</button>
</form>