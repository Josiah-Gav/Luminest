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
    $res = $maintenance->createRequest(
        $_SESSION['user_id'],
        $_POST['title'],
        $_POST['category'],
        $_POST['description'],
        $_POST['priority']
    );
    header('Location: ../../view/Tenant/maintenance_history.php');
    exit;
}