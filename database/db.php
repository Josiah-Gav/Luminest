<?php

class Database{
    private $host = "localhost:3309";
    private $username = "root";
    private $password = "";
    private $database = "luminest";
    private $conn;

    public function __construct(){
        try{
            $db = "mysql:host=$this->host;dbname=$this->database;charset=utf8mb4";
            $option = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => false,
            ];

            $this->conn = new PDO(
                $db,
                $this->username,
                $this->password,
                $option);
        }
        catch(PDOExeption $e){
            die('Connection failed: ' . $e);
        }
    }
    public function setConnection($conn){
        $this->conn = $conn; 
    }
    public function getConnection(){
        return $this->conn; 
    }
    public function __destruct(){
        $this->conn = null;
    }
    
}
    
