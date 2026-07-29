<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/auth/auth_controller.php';

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