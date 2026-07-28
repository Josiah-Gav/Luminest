<?php

class User{
    private $conn;
    private $table = "users";

    public $user_id;
    public $full_name;
    public $email;
    public $password;
    public $phone_number;

    public function __construct($db){
        $this->conn = $db;
    }

    public function create(){
        $query = "INSERT INTO " . $this->table . " (full_name, email, password_hash, phone_number) VALUES (:full_name, :email, :password_hash, :phone_number)";
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $normalizedPhoneNumber = $this->normalizePhoneNumber($this->phone_number);

        // Bind parameters
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password_hash', password_hash($this->password, PASSWORD_DEFAULT));
        $stmt->bindParam(':phone_number', $normalizedPhoneNumber);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function readByEmail($email){
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getLoginUserByEmail($email){
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function emailExists($email){
        $query = "SELECT user_id FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function phoneNumberExists($phoneNumber){
        $normalizedPhoneNumber = $this->normalizePhoneNumber($phoneNumber);

        $query = "SELECT user_id FROM " . $this->table . " WHERE phone_number = :phone_number LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':phone_number', $normalizedPhoneNumber);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    private function normalizePhoneNumber($phoneNumber){
        return preg_replace('/\D+/', '', (string)$phoneNumber);
    }

    public function getByID($id){
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $full_name, $email, $phone_number){
        $query = "UPDATE " . $this->table . " SET full_name = :full_name, email = :email, phone_number = :phone_number WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $full_name = htmlspecialchars(strip_tags($full_name));
        $email = htmlspecialchars(strip_tags($email));
        $normalizedPhoneNumber = $this->normalizePhoneNumber($phone_number);

        // Bind parameters
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone_number', $normalizedPhoneNumber);
        $stmt->bindParam(':user_id', $id);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function updatePassword($id, $new_password){
        $query = "UPDATE " . $this->table . " SET password_hash = :password_hash WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $new_password = htmlspecialchars(strip_tags($new_password));
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); 
        // Bind parameters
        $stmt->bindParam(':password_hash', $hashed_password);
        $stmt->bindParam(':user_id', $id);

        if($stmt->execute()){
            return true;
        }
        return false;
    }
}