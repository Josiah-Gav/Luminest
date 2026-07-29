<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/database/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/Maintenance.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Tenant') {
    header('Location: ../auth/login.php');
    exit;
}

function sendJsonResponse($payload, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

$isAjaxRequest = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || !empty($_POST['ajax'])
    || !empty($_GET['ajax']);

$db = new Database();
$maintenance = new Maintenance($db->getConnection());
$tenant_id = (int) ($_SESSION['user_id'] ?? 0);
$request_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$request = null;
$error = '';
$success = '';

if (!$request_id) {
    $error = 'Invalid request ID.';
} else {
    if (isset($_POST['mark_resolved'])) {
        $updated = $maintenance->markRequestAsResolvedByTenant($request_id, $tenant_id);
        if ($updated) {
            $_SESSION['flash_message'] = 'Maintenance request marked as resolved.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Unable to mark this request as resolved.';
            $_SESSION['flash_type'] = 'danger';
        }
        header('Location: ../../view/Tenant/maintenance_details.php?id=' . $request_id);
        exit;
    }

    $request = $maintenance->getRequestByIdForTenant($request_id, $tenant_id);
    if (!$request) {
        $error = 'Request not found or does not belong to you.';
    }
}
