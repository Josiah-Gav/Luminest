<?php

    require_once '../layout/header.php';

    $db = new Database();
    $user = new User($db->getConnection());
?>

<form method="POST" action="../../controllers/auth/auth_controller.php">
    <div class="container mt-5">
        <h2>Login</h2>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <input type="hidden" name="login" value="login">
        <button type="submit" class="btn btn-primary">Login</button>
    </div>
</form>

<p  >Don't have an account? <a href="register.php">Register here</a></p>


<?php
    require_once '../layout/footer.php';
?>


