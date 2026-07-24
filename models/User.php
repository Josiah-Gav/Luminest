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

        // Bind parameters
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password_hash', password_hash($this->password, PASSWORD_DEFAULT));
        $stmt->bindParam(':phone_number', $this->phone_number);

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

    public function read(){
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }
}