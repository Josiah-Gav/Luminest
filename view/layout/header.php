<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luminest</title>
</head>
<!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<body>
    
<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/database/db.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/User.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/controllers/auth/auth_controller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>