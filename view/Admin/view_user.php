<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/admin/admin_controller.php';

$user = $admin->getUserById($_GET['id']);
?>

<h1 class="h3 fw-bold mb-3">User <?= $user['full_name'] ?></h1>

<div class="card">
    <div class="card-body">
        <p><strong>User ID:</strong> <?= $user['user_id'] ?></p>
        <p><strong>Full Name:</strong> <?= $user['full_name'] ?></p>
        <p><strong>Email:</strong> <?= $user['email'] ?></p>
        <p><strong>Role:</strong> <?= $user['role'] ?></p>
        <p><strong>Status:</strong> <?= $user['status'] ?></p>
        <p><strong>Phone Number:</strong> <?= $user['phone_number'] ?></p>
        <p><strong>Created At:</strong> <?= $user['created_at'] ?></p>
        <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-primary">Edit User</a>
        <a href="user_management.php" class="btn btn-secondary">Back to User Management</a>
    </div>

</div>