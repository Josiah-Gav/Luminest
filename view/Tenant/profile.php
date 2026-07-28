<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/auth/auth_controller.php';

$profile = $user->getByID($_SESSION['user_id']);
?>

<h1>Profile</h1>
<p>Welcome, <?=$_SESSION['username']?>! Here you can view and update your profile information.</p>
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