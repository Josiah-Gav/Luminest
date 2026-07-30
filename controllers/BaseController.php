<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/database/db.php';

class BaseController extends AppDatabase
{
    protected $conn;

    public function __construct()
    {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    public function getDbConnection()
    {
        return $this->conn;
    }
}
