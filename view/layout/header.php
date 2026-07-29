<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luminest</title>
    <style>
        :root {
            --lm-red: #c1121f;
            --lm-blue: #1d4ed8;
            --lm-black: #111111;
            --lm-soft: #f8fbff;
            --lm-border: #e8eef7;
        }
        body {
            background: linear-gradient(180deg, #ffffff 0%, var(--lm-soft) 100%);
            color: var(--lm-black);
            font-family: Arial, sans-serif;
        }
        .card, .table, .form-control, .form-select, .btn, .navbar {
            border-radius: 0.75rem;
        }
        .card, .table, .form-control, .form-select {
            border-color: var(--lm-border);
        }
        .btn-primary {
            background-color: var(--lm-blue);
            border-color: var(--lm-blue);
        }
        .btn-outline-primary {
            color: var(--lm-blue);
            border-color: var(--lm-blue);
        }
        .btn-outline-danger {
            color: var(--lm-red);
            border-color: var(--lm-red);
        }
        .text-accent-red { color: var(--lm-red) !important; }
        .text-accent-blue { color: var(--lm-blue) !important; }
        .text-accent-black { color: var(--lm-black) !important; }
        .bg-accent-soft { background-color: var(--lm-soft) !important; }
    </style>
</head>
<!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-4.0.0.js" integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert@2.1.2/dist/sweetalert.min.js"></script>
<body>
    
<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/database/db.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/User.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/controllers/auth/auth_controller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

