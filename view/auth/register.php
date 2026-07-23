<?php
    require_once '../../database/db.php';
    require_once '../../models/User.php';
    require_once '../../controllers/auth/auth_controller.php';
    require_once '../layout/header.php';

    $db = new Database();
    $user = new User($db->getConnection());
?>

<form method="POST" action="../../controllers/auth/auth_controller.php">
    <div class="container mt-5">
        <h2>Register</h2>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <input type="hidden" name="register" value="register">
        <button type="submit" class="btn btn-primary">Register</button>
    </div>
</form>