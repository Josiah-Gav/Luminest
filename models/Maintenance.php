<?php
class Maintenance{
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    public function createRequest($tenant_id, $category, $description){
        $query = "INSERT INTO maintenance_requests (tenant_id, category, description, status, created_at) VALUES (:tenant_id, :category, :description, 'pending', NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tenant_id', $tenant_id);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':description', $description);

        return $stmt->execute();
    }

    public function getRequestsByTenant($tenant_id){
        $query = "SELECT * FROM maintenance_requests WHERE tenant_id = :tenant_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tenant_id', $tenant_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>