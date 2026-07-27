<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/database/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/Maintenance.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Maintenance_Staff') {
    header('Location: ../auth/login.php');
    exit;
}

$db = new Database();
$maintenance = new Maintenance($db->getConnection());
$staff_id = (int) ($_SESSION['user_id'] ?? 0);
$request_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$request = null;
$error = '';
$success = '';

if (!$request_id) {
    $error = 'Invalid request ID.';
} else {
    if (isset($_POST['update_request'])) {
        $status = $_POST['status'] ?? '';
        $resolution_notes = trim($_POST['resolution_notes'] ?? '');
        $resolution_notes = $resolution_notes === '' ? null : $resolution_notes;

        $updated = $maintenance->updateRequestStatusByStaff($request_id, $staff_id, $status, $resolution_notes);
        if ($updated) {
            $success = 'Maintenance request updated successfully.';
        } else {
            $error = 'Unable to update request. Please verify the status and assignment.';
        }
    }

    $request = $maintenance->getRequestByIdForStaff($request_id, $staff_id);
    if (!$request) {
        $error = 'Request not found or not assigned to you.';
    }
}
