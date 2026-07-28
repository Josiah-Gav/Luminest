<?php
class Admin {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllUsers() {
        $query = "SELECT * FROM users";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($user_id) {
        $query = "SELECT * FROM users WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUser($user_id, $full_name, $email, $role, $expertise, $phone_number) {
        $query = "UPDATE users SET full_name = :full_name, email = :email, role = :role, expertise = :expertise, phone_number = :phone_number WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        $currentUser = $this->getUserById($user_id);
        $existingPhone = $currentUser['phone_number'] ?? '';

        // Sanitize input
        $full_name = htmlspecialchars(strip_tags($full_name));
        $email = htmlspecialchars(strip_tags($email));
        $expertise = htmlspecialchars(strip_tags($expertise ?? ''));
        $normalizedPhoneNumber = $this->normalizePhoneNumber($phone_number);
        $existingNormalizedPhone = $this->normalizePhoneNumber($existingPhone);

        if ($normalizedPhoneNumber === '' || $normalizedPhoneNumber === $existingNormalizedPhone) {
            $finalPhoneNumber = $existingPhone;
        } else {
            $finalPhoneNumber = $normalizedPhoneNumber;
        }

        // Bind parameters
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':expertise', $expertise);
        $stmt->bindParam(':phone_number', $finalPhoneNumber);

        return $stmt->execute();
    }

    public function createUser($full_name, $email, $password, $role, $expertise, $phone_number) {
        $query = "INSERT INTO users (full_name, email, password_hash, role, expertise, phone_number) VALUES (:full_name, :email, :password_hash, :role, :expertise, :phone_number)";
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $full_name = htmlspecialchars(strip_tags($full_name));
        $email = htmlspecialchars(strip_tags($email));
        $password = htmlspecialchars(strip_tags($password));
        $normalizedPhoneNumber = $this->normalizePhoneNumber($phone_number);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $expertise = htmlspecialchars(strip_tags($expertise ?? ''));

        // Bind parameters
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $hashedPassword);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':expertise', $expertise);
        $stmt->bindParam(':phone_number', $normalizedPhoneNumber);

        return $stmt->execute();
    }

    public function normalizePhoneNumber($phone_number) {
        // Remove all non-digit characters
        $normalized = preg_replace('/\D/', '', $phone_number);

        return $normalized;
    }

    public function deleteUser($user_id) {
        $query = "DELETE FROM users WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }
}

?>