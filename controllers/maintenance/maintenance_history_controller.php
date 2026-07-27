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
$history_requests = $maintenance->getHistoryByStaff($staff_id);
