<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/database/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/User.php';

$db = new Database();
$user = new User($db->getConnection());
$res = null;

// LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    unset($_POST['login']); // Remove the 'login' key from $_POST to avoid confusion
    $userData = $user->readByEmail($_POST['email']);
    if (empty($email) || empty($password)) {
        echo "Email and password are required.";
        exit;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['user_id'] = $userData['user_id'];
    $_SESSION['email'] = $userData['email'];
    $_SESSION['username'] = $userData['full_name'];
    $_SESSION['role'] = $userData['role']; // Default role to 'user' if not set
    $_SESSION['phone_number'] = $userData['phone_number'];

    // Login by email to match the existing input field.
    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $db->getConnection()->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $userData = $stmt->fetch();

    try {
        if ($userData && password_verify($password, $userData['password_hash'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $userData['user_id'];
            $_SESSION['email'] = $userData['email'];
            if (isset($_SESSION['role'])) {
                if ($_SESSION['role'] === 'Maintenance_Staff') {
                    header("Location: ../../view/Maintenance_Staff/dashboard.php");
                } elseif ($_SESSION['role'] === 'Property_Manager') {
                    header("Location: ../../view/dashboard.php");
                } elseif ($_SESSION['role'] === 'Admin') {
                    header("Location: ../../view/Admin/dashboard.php");
                } elseif ($_SESSION['role'] === 'Tenant') {
                    header("Location: ../../view/Tenant/dashboard.php");
                } else {
                    header("Location: ../../view/Prospect/dashboard.php");
                }
                exit;
            }
            exit;
        }

        echo "Invalid email or password.";
    } catch (PDOException $e) {
        die('Connection failed: ' . $e->getMessage());
    }

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../view/auth/login.php");
        exit;
    } else{
        header("Location: view/Tenant/dashboard.php");
    }
}

// REGISTER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $fullName = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $phoneNumber = $_POST['phone_number'] ?? '';

    // Registration includes email; login does not.
    if (!empty($email)) {
        if (empty($fullName) || empty($password)) {
            echo "All fields are required....";
            exit;
        }

        // Check if user already exists by email.
        $query = "SELECT user_id FROM users WHERE email = :email";
        $stmt = $db->getConnection()->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "Email already exists.";
            exit;
        }

        // Create new user record based on current schema.
        $user->full_name = $fullName;
        $user->email = $email;
        $user->password = $password;
        $user->phone_number = $phoneNumber;

        if ($user->create()) {
            header("Location: ../../view/auth/login.php");
            exit;
        }

        echo "Error registering user.";
        exit;
    }

    if (empty($email) || empty($password)) {
        echo "Email and password are required.";
        exit;
    }
}

