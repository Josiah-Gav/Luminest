<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/controllers/BaseController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/Admin.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sendJsonResponse($payload, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

$currentRole = isset($_SESSION['role']) ? strtolower(trim((string) $_SESSION['role'])) : '';
$isAdminRequest = $currentRole === 'admin' || $currentRole === 'administrator';

if (!$isAdminRequest) {
    if (!empty($_GET['ajax_search']) || !empty($_POST['ajax']) || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        sendJsonResponse(['success' => false, 'message' => 'Unauthorized request.'], 401);
    }
    header('Location: ../auth/login.php');
    exit;
}

$isAjaxRequest = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || !empty($_POST['ajax'])
    || !empty($_GET['ajax']);

$admin = new Admin();
$res = null;

if(isset($_POST['create_user'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $expertise = $_POST['expertise'] ?? $_POST['especialty'] ?? null;
    $number = $_POST['phone_number'];

    $res = $admin->createUser($full_name, $email, $password, $role, $expertise, $number);
    if ($isAjaxRequest) {
        if ($res) {
            sendJsonResponse(['success' => true, 'message' => 'User created successfully.', 'redirect' => '/luminest/view/Admin/user_management.php']);
        }
        sendJsonResponse(['success' => false, 'message' => 'Unable to create user. Please try again.'], 400);
    }
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
    if ($isAjaxRequest) {
        if ($res) {
            sendJsonResponse(['success' => true, 'message' => 'User updated successfully.', 'redirect' => '/luminest/view/Admin/user_management.php']);
        }
        sendJsonResponse(['success' => false, 'message' => 'Unable to update user. Please try again.'], 400);
    }
    header("Location: ../../view/Admin/user_management.php");
}

if(isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $res = $admin->deleteUser($user_id);
    if ($isAjaxRequest) {
        if ($res) {
            sendJsonResponse(['success' => true, 'message' => 'User deleted successfully.', 'redirect' => '/luminest/view/Admin/user_management.php']);
        }
        sendJsonResponse(['success' => false, 'message' => 'Unable to delete user. Please try again.'], 400);
    }
    header("Location: ../../view/Admin/user_management.php");
}
?>