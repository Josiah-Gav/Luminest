<?php

class Database {
    protected $host = "localhost";
    protected $port = "3309";
    protected $username = "root";
    protected $password = "";
    protected $database = "luminest";
    protected $conn;

    public function __construct() {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT         => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function setConnection($conn) {
        $this->conn = $conn;
    }

    public function __destruct() {
        $this->conn = null;
    }
}

class AppDatabase extends Database {}

// -------------------------------------------------------------
// Instantiate and expose $pdo / $conn for your dashboard views
// -------------------------------------------------------------
$db = new AppDatabase();
$pdo  = $db->getConnection();
$conn = $pdo; // Optional alias if any views use $conn instead of $pdo