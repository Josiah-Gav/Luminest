<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/database/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/User.php';

$db = new Database();
$user = new User($db->getConnection());
$error = null;
$res = null;

function normalizePhoneNumber($phoneNumber) {
    $digits = preg_replace('/\D+/', '', (string)$phoneNumber);

    if ($digits === '') {
        return '';
    }

    if (strlen($digits) === 12 && strpos($digits, '63') === 0) {
        return '0' . substr($digits, 2);
    }

    if (strlen($digits) === 11 && strpos($digits, '0') === 0) {
        return $digits;
    }

    if (strlen($digits) === 10 && strpos($digits, '0') !== 0) {
        return '0' . $digits;
    }

    return $digits;
}

// LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    unset($_POST['login']); // Remove the 'login' key from $_POST to avoid confusion

    if (empty($email) || empty($password)) {
        echo "Email and password are required.";
        exit;
    }

    $userData = $user->getLoginUserByEmail($email);

    try {
        if ($userData && password_verify($password, $userData['password_hash'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = $userData['user_id'];
            $_SESSION['email'] = $userData['email'];
            $_SESSION['username'] = $userData['full_name'];
            $_SESSION['role'] = $userData['role'];
            $_SESSION['phone_number'] = $userData['phone_number'];
            $_SESSION['expertise'] = $userData['expertise'] ?? '';
            $_SESSION['owned_house'] = $userData['owned_house'] ?? '';


            if (isset($_SESSION['role'])) {
                if ($_SESSION['role'] === 'Maintenance_Staff') {
                    header("Location: ../../view/Maintenance_Staff/dashboard.php");
                } elseif ($_SESSION['role'] === 'Property_Manager') {
                    header("Location: ../../view/Property_Manager/dashboard.php");
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

        $error = "Invalid email or password.";
    } catch (PDOException $e) {
        die('Connection failed: ' . $e->getMessage());
    }

    header("Location: ../../view/auth/login.php?error=" . urlencode($error));
    exit;
}

// REGISTER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $fullName = $_POST['full_name'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phoneNumber = normalizePhoneNumber($_POST['phone_number'] ?? '');

    // Registration includes email; login does not.
    if (!empty($email)) {
        if (empty($fullName) || empty($password) || empty($phoneNumber)) {
            echo "All fields are required....";
            exit;
        }

        // Check if user already exists by email.
        if ($user->emailExists($email)) {
            echo "Email already exists.";
            exit;
        }

        if ($user->phoneNumberExists($phoneNumber)) {
            echo "Phone number already exists.";
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

