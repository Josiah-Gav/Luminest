<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/controllers/BaseController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/User.php';

$user = new User();
$error = null;
$res = null;

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION['user_id'])) {
	header('Location: ../../view/auth/login.php');
	exit;
}

function getProfileRedirectPath(): string {
    $role = strtolower(trim((string) ($_SESSION['role'] ?? '')));

    if ($role === 'tenant') {
        return '../../view/Tenant/profile.php';
    }

    if ($role === 'maintenance_staff') {
        return '../../view/Maintenance_Staff/profile.php';
    }

    if ($role === 'property_manager') {
        return '../../view/Property_Manager/profile.php';
    }

    return '../../view/Admin/profile.php';
}

if(isset($_POST['update_profile'])) {
    $user_id = $_SESSION['user_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];

    $res = $user->updateProfile($user_id, $full_name, $email, $phone_number);

    if ($res) {
        // Update session variables
        $_SESSION['username'] = $full_name;
        $_SESSION['email'] = $email;
        $_SESSION['phone_number'] = $phone_number;

        $redirectPath = getProfileRedirectPath();
        header("Location: {$redirectPath}?success=profile_updated");
        exit;
    } else {
        $redirectPath = getProfileRedirectPath();
        header("Location: {$redirectPath}?error=update_failed");
        exit;
    }
}

if(isset($_POST['change_password'])) {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Verify current password
    $userData = $user->getById($user_id);
    if (!$userData || !password_verify($current_password, $userData['password_hash'])) {
        $redirectPath = getProfileRedirectPath();
        header("Location: {$redirectPath}?error=invalid_current_password");
        exit;
    }

    // Update password
    $res = $user->updatePassword($user_id, $new_password);

    if ($res) {
        $redirectPath = getProfileRedirectPath();
        header("Location: {$redirectPath}?success=password_changed");
        exit;
    } else {
        $redirectPath = getProfileRedirectPath();
        header("Location: {$redirectPath}?error=password_change_failed");
        exit;
    }
}

?>