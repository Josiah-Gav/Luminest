<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/controllers/BaseController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/Maintenance.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Maintenance_Staff') {
    header('Location: ../auth/login.php');
    exit;
}

function sendJsonResponse($payload, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_request']) && (!empty($_POST['ajax']) || !empty($_SERVER['HTTP_X_REQUESTED_WITH']))) {
    $request_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$request_id) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid request ID.'], 400);
    }
}

$isAjaxRequest = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || !empty($_POST['ajax'])
    || !empty($_GET['ajax']);

$maintenance = new Maintenance();
$staff_id = (int) ($_SESSION['user_id'] ?? 0);
$request_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$request = null;
$error = '';
$success = '';

if (!$request_id) {
    $error = 'Invalid request ID.';
} else {
    if (isset($_POST['update_request'])) {
        $status = trim((string) ($_POST['status'] ?? ''));
        $resolution_notes = trim((string) ($_POST['resolution_notes'] ?? ''));
        $resolution_notes = $resolution_notes === '' ? null : $resolution_notes;

        if ($status === 'resolved') {
            $error = 'Maintenance staff can only mark requests as completed.';
            if ($isAjaxRequest) {
                sendJsonResponse(['success' => false, 'message' => $error], 400);
            }
        } else {
            try {
                $updated = $maintenance->updateRequestStatusByStaff($request_id, $staff_id, $status, $resolution_notes);
                if ($updated) {
                    $success = 'Maintenance request updated successfully.';
                    if ($isAjaxRequest) {
                        $request = $maintenance->getRequestByIdForStaff($request_id, $staff_id);
                        sendJsonResponse(['success' => true, 'message' => $success, 'request' => $request]);
                    }
                } else {
                    $error = 'Unable to update request. Please verify the request assignment and try again.';
                    if ($isAjaxRequest) {
                        sendJsonResponse(['success' => false, 'message' => $error], 400);
                    }
                }
            } catch (Throwable $e) {
                $error = 'Unable to update request due to a server error.';
                if ($isAjaxRequest) {
                    sendJsonResponse(['success' => false, 'message' => $error], 500);
                }
            }
        }
    }

    $request = $maintenance->getRequestByIdForStaff($request_id, $staff_id);
    if (!$request) {
        $error = 'Request not found or not assigned to you.';
    }
}
