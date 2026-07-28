<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/database/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/Admin.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../auth/login.php');
    exit;
}

$db = new Database();
$admin = new Admin($db->getConnection());
$res = null;

if(isset($_POST['create_user'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $expertise = $_POST['expertise'] ?? $_POST['especialty'] ?? null;
    $number = $_POST['phone_number'];

    $res = $admin->createUser($full_name, $email, $password, $role, $expertise, $number);
    header("Location: ../../view/Admin/user_management.php");
}

if(isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $expertise = $_POST['expertise'] ?? $_POST['especialty'] ?? null;
    $number = $_POST['phone_number'];

    $res = $admin->updateUser($user_id, $full_name, $email, $role, $expertise, $number);
    header("Location: ../../view/Admin/user_management.php");
}

if(isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $res = $admin->deleteUser($user_id);
    header("Location: ../../view/Admin/user_management.php");
}
?>