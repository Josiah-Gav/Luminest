<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/database/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/Maintenance.php';

$db = new Database();
$user = new User($db->getConnection());
$maintenance = new Maintenance($db->getConnection());
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
        header('Location: ../../view/Tenant/maintenance_request.php');
        exit;
    }

    header('Location: ../../view/Tenant/maintenance_history.php');
    exit;
}