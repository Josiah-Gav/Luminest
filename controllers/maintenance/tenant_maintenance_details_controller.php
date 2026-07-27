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
            $success = 'Maintenance request marked as resolved.';
        } else {
            $error = 'Unable to mark this request as resolved.';
        }
    }

    $request = $maintenance->getRequestByIdForTenant($request_id, $tenant_id);
    if (!$request) {
        $error = 'Request not found or does not belong to you.';
    }
}
