<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/controllers/BaseController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/Maintenance.php';

function sendJsonResponse($payload, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

$isAjaxRequest = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || !empty($_POST['ajax'])
    || !empty($_GET['ajax']);

$user = new User();
$maintenance = new Maintenance();
$res = null;

if(isset($_POST['submit_request'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Tenant' || !isset($_SESSION['user_id'])) {
        header('Location: ../../view/auth/login.php');
        exit;
    }

    $title = trim((string) ($_POST['title'] ?? ''));
    $category = trim((string) ($_POST['category'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $priority = trim((string) ($_POST['priority'] ?? 'medium'));

    if ($title === '' || $category === '' || $description === '') {
        if ($isAjaxRequest) {
            sendJsonResponse(['success' => false, 'message' => 'Please fill in every required field.'], 400);
        }
        header('Location: ../../view/Tenant/maintenance_request.php');
        exit;
    }

    $res = $maintenance->createRequest(
        $_SESSION['user_id'],
        $title,
        $category,
        $description,
        $priority
    );

    if (!$res) {
        if ($isAjaxRequest) {
            sendJsonResponse(['success' => false, 'message' => 'Unable to submit your maintenance request.'], 400);
        }
        header('Location: ../../view/Tenant/maintenance_request.php');
        exit;
    }

    if ($isAjaxRequest) {
        sendJsonResponse(['success' => true, 'message' => 'Maintenance request submitted successfully.', 'redirect' => '/luminest/view/Tenant/maintenance_history.php']);
    }

    header('Location: ../../view/Tenant/maintenance_history.php');
    exit;
}