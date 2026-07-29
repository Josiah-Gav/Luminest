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

$db = new AppDatabase();
$maintenance = new Maintenance();
$tenant_id = (int) ($_SESSION['user_id'] ?? 0);
$requests = $maintenance->getRequestsByTenant($tenant_id);
