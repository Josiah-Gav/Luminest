<?php
class Maintenance{
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    public function createRequest($tenant_id, $title, $category, $description, $priority = 'medium'){
        $house_id = $this->getOwnedHouseIdByTenant($tenant_id);
        $allowed_categories = $this->getRequestCategoryOptions();

        if (!in_array($category, $allowed_categories, true)) {
            return false;
        }

        $query = "INSERT INTO maintenance_requests (tenant_id, house_id, title, category, description, priority, status, created_at) VALUES (:tenant_id, :house_id, :title, :category, :description, :priority, 'pending', NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tenant_id', $tenant_id);
        $stmt->bindParam(':house_id', $house_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':priority', $priority);

        return $stmt->execute();
    }

    public function getRequestCategoryOptions(){
        $query = "SHOW COLUMNS FROM maintenance_requests LIKE 'category'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $column = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$column || !isset($column['Type'])) {
            return ['general'];
        }

        if (!preg_match("/^enum\((.*)\)$/", (string) $column['Type'], $matches)) {
            return ['general'];
        }

        $raw_values = str_getcsv($matches[1], ',', "'");
        $values = array_values(array_filter(array_map('trim', $raw_values), static function ($value) {
            return $value !== '';
        }));

        return !empty($values) ? $values : ['general'];
    }

    private function getOwnedHouseIdByTenant($tenant_id){
        $query = "SELECT owned_house FROM users WHERE user_id = :tenant_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tenant_id', $tenant_id);
        $stmt->execute();

        $owned_house = $stmt->fetchColumn();

        return $owned_house !== false ? $owned_house : null;
    }

    public function getRequestsByTenant($tenant_id){
        $query = "SELECT mr.*, s.full_name AS assigned_staff_name,
                  CASE
                      WHEN h.house_id IS NULL THEN 'No owned house'
                      ELSE CONCAT('Block ', h.block, ', Lot ', h.lot, ' (', REPLACE(h.house_type, '_', ' '), ')')
                  END AS property_address
                  FROM maintenance_requests mr
                  LEFT JOIN house h ON mr.house_id = h.house_id
                  LEFT JOIN users s ON mr.assigned_staff = s.user_id
                  WHERE mr.tenant_id = :tenant_id
                  ORDER BY mr.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tenant_id', $tenant_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRequestsByStaff($staff_id){
        $query = "SELECT mr.*, u.full_name AS tenant_name,
                  CASE
                      WHEN h.house_id IS NULL THEN 'No owned house'
                      ELSE CONCAT('Block ', h.block, ', Lot ', h.lot, ' (', REPLACE(h.house_type, '_', ' '), ')')
                  END AS property_address
                  FROM maintenance_requests mr
                  LEFT JOIN users u ON mr.tenant_id = u.user_id
                  LEFT JOIN house h ON mr.house_id = h.house_id
                  WHERE mr.assigned_staff = :staff_id
                  ORDER BY mr.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':staff_id', $staff_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRequestByIdForStaff($request_id, $staff_id){
        $query = "SELECT mr.*, u.full_name AS tenant_name,
                  CASE
                      WHEN h.house_id IS NULL THEN 'No owned house'
                      ELSE CONCAT('Block ', h.block, ', Lot ', h.lot, ' (', REPLACE(h.house_type, '_', ' '), ')')
                  END AS property_address
                  FROM maintenance_requests mr
                  LEFT JOIN users u ON mr.tenant_id = u.user_id
                  LEFT JOIN house h ON mr.house_id = h.house_id
                  WHERE mr.id = :request_id
                    AND mr.assigned_staff = :staff_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
        $stmt->execute();

        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        return $request ?: null;
    }

    public function getRequestByIdForTenant($request_id, $tenant_id){
                $query = "SELECT mr.*, s.full_name AS assigned_staff_name,
                  CASE
                      WHEN h.house_id IS NULL THEN 'No owned house'
                      ELSE CONCAT('Block ', h.block, ', Lot ', h.lot, ' (', REPLACE(h.house_type, '_', ' '), ')')
                  END AS property_address
                  FROM maintenance_requests mr
                  LEFT JOIN house h ON mr.house_id = h.house_id
                                    LEFT JOIN users s ON mr.assigned_staff = s.user_id
                  WHERE mr.id = :request_id
                    AND mr.tenant_id = :tenant_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->bindParam(':tenant_id', $tenant_id, PDO::PARAM_INT);
        $stmt->execute();

        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        return $request ?: null;
    }

    public function updateRequestStatusByStaff($request_id, $staff_id, $status, $resolution_notes = null){
        $allowed_statuses = ['in-progress', 'resolved'];

        if (!in_array($status, $allowed_statuses, true)) {
            return false;
        }

        $query = "UPDATE maintenance_requests
                  SET status = :status,
                      resolution_notes = :resolution_notes,
                      resolved_at = CASE
                          WHEN :status = 'resolved' THEN NOW()
                          ELSE resolved_at
                      END,
                      updated_at = NOW()
                  WHERE id = :request_id
                    AND assigned_staff = :staff_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':resolution_notes', $resolution_notes);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function markRequestAsCompletedByTenant($request_id, $tenant_id){
        $query = "UPDATE maintenance_requests
                  SET status = 'completed',
                      completed_at = NOW(),
                      updated_at = NOW()
                  WHERE id = :request_id
                    AND tenant_id = :tenant_id
                    AND status = 'resolved'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);
        $stmt->bindParam(':tenant_id', $tenant_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() === 1;
    }

    public function getHistoryByStaff($staff_id){
        $query = "SELECT mr.*, u.full_name AS tenant_name,
                  CASE
                      WHEN h.house_id IS NULL THEN 'No owned house'
                      ELSE CONCAT('Block ', h.block, ', Lot ', h.lot, ' (', REPLACE(h.house_type, '_', ' '), ')')
                  END AS property_address
                  FROM maintenance_requests mr
                  LEFT JOIN users u ON mr.tenant_id = u.user_id
                  LEFT JOIN house h ON mr.house_id = h.house_id
                                    WHERE mr.assigned_staff = :staff_id
                                        AND mr.status IN ('resolved', 'completed')
                                    ORDER BY COALESCE(mr.resolved_at, mr.completed_at, mr.updated_at) DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>