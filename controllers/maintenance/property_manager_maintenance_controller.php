<?php

class PropertyManagerMaintenanceController
{
    private PDO $pdo;
    private bool $tableReady = false;
    private ?string $errorMsg = null;
    private array $requests = [];
    private array $staffList = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function handleRequest(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['role']) && $_SESSION['role'] !== 'Property_Manager') {
            header('Location: ../../index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_request') {
            $this->handleUpdateRequest();
        }

        if (isset($_GET['ajax']) && $_GET['ajax'] === 'search') {
            $this->handleAjaxSearchRequest();
        }

        $this->loadInitialData();
    }

    public function isTableReady(): bool
    {
        return $this->tableReady;
    }

    public function getErrorMsg(): ?string
    {
        return $this->errorMsg;
    }

    public function getRequests(): array
    {
        return $this->requests;
    }

    public function getStaffList(): array
    {
        return $this->staffList;
    }

    public function formatStatusLabel(string $status): string
    {
        return ucwords(str_replace(['-', '_'], ' ', strtolower($status)));
    }

    public function statusBadgeClass(string $status): string
    {
        $normalized = strtolower($status);
        if ($normalized === 'resolved') {
            return 'success';
        }
        if ($normalized === 'accepted') {
            return 'primary';
        }
        if ($normalized === 'in-progress') {
            return 'info';
        }
        if ($normalized === 'on-hold') {
            return 'warning text-dark';
        }
        if ($normalized === 'cancelled' || $normalized === 'rejected') {
            return 'danger';
        }

        return 'secondary';
    }

    public function formatPriorityLabel(string $priority): string
    {
        return ucwords(str_replace(['-', '_'], ' ', strtolower($priority)));
    }

    public function priorityBadgeClass(string $priority): string
    {
        $normalized = strtolower($priority);
        if ($normalized === 'urgent') {
            return 'danger';
        }
        if ($normalized === 'high') {
            return 'warning text-dark';
        }
        if ($normalized === 'medium') {
            return 'primary';
        }

        return 'secondary';
    }

    private function handleUpdateRequest(): void
    {
        header('Content-Type: application/json');

        try {
            if (!$this->tableExists('maintenance_requests')) {
                throw new RuntimeException('maintenance_requests table not found.');
            }

            $columns = $this->getColumns('maintenance_requests');
            $idColumn = $this->getRequestIdColumn($columns);
            if ($idColumn === null) {
                throw new RuntimeException('No request identifier column found.');
            }

            $requestId = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
            if (!$requestId) {
                throw new RuntimeException('Invalid request ID.');
            }

            $requestLookup = $this->pdo->prepare("SELECT * FROM maintenance_requests WHERE {$idColumn} = :request_id LIMIT 1");
            $requestLookup->execute([':request_id' => $requestId]);
            $requestRow = $requestLookup->fetch(PDO::FETCH_ASSOC);
            if (!$requestRow) {
                throw new RuntimeException('Maintenance request not found.');
            }

            $sets = [];
            $params = [':request_id' => $requestId];

            $assignedStaffColumn = $this->getAssignedStaffColumn($columns);
            if ($assignedStaffColumn !== null) {
                $staffIdRaw = trim((string)($_POST['assigned_staff_id'] ?? $_POST['assigned_staff'] ?? ''));

                if ($staffIdRaw === '') {
                    $sets[] = "{$assignedStaffColumn} = NULL";

                    if ($this->hasColumn($columns, 'status') && strtolower((string)($requestRow['status'] ?? 'pending')) === 'accepted') {
                        $sets[] = 'status = :status';
                        $params[':status'] = 'pending';
                    }
                } else {
                    $staffId = filter_var($staffIdRaw, FILTER_VALIDATE_INT);
                    if ($staffId === false) {
                        throw new RuntimeException('Invalid assigned staff ID.');
                    }

                    $requestCategory = strtolower((string)($requestRow['category'] ?? ''));

                    $staffQuery = $this->pdo->prepare('SELECT role, expertise FROM users WHERE user_id = :staff_id LIMIT 1');
                    $staffQuery->execute([':staff_id' => $staffId]);
                    $staffRow = $staffQuery->fetch(PDO::FETCH_ASSOC);

                    if (!$staffRow || ($staffRow['role'] ?? '') !== 'Maintenance_Staff') {
                        throw new RuntimeException('Selected user is not a maintenance staff member.');
                    }

                    $expertise = strtolower((string)($staffRow['expertise'] ?? ''));
                    if ($requestCategory !== '' && $expertise !== '' && $expertise !== $requestCategory && $expertise !== 'general') {
                        throw new RuntimeException('Selected staff expertise does not match this maintenance role.');
                    }

                    $sets[] = "{$assignedStaffColumn} = :assigned_staff_id";
                    $params[':assigned_staff_id'] = $staffId;

                    if ($this->hasColumn($columns, 'status') && strtolower((string)($requestRow['status'] ?? 'pending')) === 'pending') {
                        $sets[] = 'status = :status';
                        $params[':status'] = 'accepted';
                    }
                }
            }

            if (empty($sets)) {
                throw new RuntimeException('No valid fields to update.');
            }

            if ($this->hasColumn($columns, 'updated_at')) {
                $sets[] = 'updated_at = NOW()';
            }

            $sql = "UPDATE maintenance_requests SET " . implode(', ', $sets) . " WHERE {$idColumn} = :request_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            echo json_encode([
                'success' => true,
                'message' => "Maintenance request #{$requestId} updated.",
                'data' => $this->fetchMaintenanceRequestById((int) $requestId),
            ]);
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        exit;
    }

    private function handleAjaxSearchRequest(): void
    {
        header('Content-Type: application/json');

        try {
            $search = trim((string)($_GET['q'] ?? ''));
            $status = trim((string)($_GET['status'] ?? ''));
            $priority = trim((string)($_GET['priority'] ?? ''));

            echo json_encode([
                'success' => true,
                'data' => $this->fetchMaintenanceRequests($search, $status, $priority),
            ]);
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
            ]);
        }

        exit;
    }

    private function loadInitialData(): void
    {
        $this->tableReady = $this->tableExists('maintenance_requests');
        if (!$this->tableReady) {
            return;
        }

        try {
            $this->requests = $this->fetchMaintenanceRequests();
            $this->staffList = $this->fetchStaffList();
        } catch (Throwable $e) {
            $this->errorMsg = $e->getMessage();
        }
    }

    private function tableExists(string $tableName): bool
    {
        $stmt = $this->pdo->prepare('SHOW TABLES LIKE :table_name');
        $stmt->execute([':table_name' => $tableName]);

        return (bool)$stmt->fetchColumn();
    }

    private function getColumns(string $tableName): array
    {
        $stmt = $this->pdo->query("SHOW COLUMNS FROM {$tableName}");
        $columns = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $columns[] = $row['Field'];
        }

        return $columns;
    }

    private function hasColumn(array $columns, string $name): bool
    {
        return in_array($name, $columns, true);
    }

    private function getRequestIdColumn(array $columns): ?string
    {
        if ($this->hasColumn($columns, 'request_id')) {
            return 'request_id';
        }

        if ($this->hasColumn($columns, 'id')) {
            return 'id';
        }

        return null;
    }

    private function getAssignedStaffColumn(array $columns): ?string
    {
        if ($this->hasColumn($columns, 'assigned_staff_id')) {
            return 'assigned_staff_id';
        }

        if ($this->hasColumn($columns, 'assigned_staff')) {
            return 'assigned_staff';
        }

        return null;
    }

    private function selectExpr(array $columns, string $column, string $alias): string
    {
        if ($this->hasColumn($columns, $column)) {
            return "m.{$column} AS {$alias}";
        }

        return "NULL AS {$alias}";
    }

    private function fetchStaffList(): array
    {
        $stmt = $this->pdo->query("SELECT user_id, full_name, expertise FROM users WHERE role = 'Maintenance_Staff' ORDER BY full_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchMaintenanceRequests(string $search = '', string $status = '', string $priority = ''): array
    {
        if (!$this->tableExists('maintenance_requests')) {
            return [];
        }

        $columns = $this->getColumns('maintenance_requests');
        $idColumn = $this->getRequestIdColumn($columns);
        if ($idColumn === null) {
            return [];
        }

        $assignedStaffColumn = $this->getAssignedStaffColumn($columns);

        $selectParts = [
            "m.{$idColumn} AS request_id",
            $this->selectExpr($columns, 'title', 'title'),
            $this->selectExpr($columns, 'description', 'description'),
            $this->selectExpr($columns, 'category', 'category'),
            $this->selectExpr($columns, 'priority', 'priority'),
            $this->selectExpr($columns, 'status', 'status'),
            $this->selectExpr($columns, 'tenant_id', 'tenant_id'),
            $assignedStaffColumn !== null ? "m.{$assignedStaffColumn} AS assigned_staff_id" : 'NULL AS assigned_staff_id',
            $this->selectExpr($columns, 'block', 'block'),
            $this->selectExpr($columns, 'lot', 'lot'),
            $this->selectExpr($columns, 'created_at', 'created_at'),
            $this->selectExpr($columns, 'updated_at', 'updated_at'),
            't.full_name AS tenant_name',
            's.full_name AS staff_name',
        ];

        $sql = 'SELECT ' . implode(', ', $selectParts) . ' FROM maintenance_requests m ';

        $sql .= $this->hasColumn($columns, 'tenant_id')
            ? ' LEFT JOIN users t ON m.tenant_id = t.user_id '
            : ' LEFT JOIN users t ON 1 = 0 ';

        $sql .= $assignedStaffColumn !== null
            ? " LEFT JOIN users s ON m.{$assignedStaffColumn} = s.user_id "
            : ' LEFT JOIN users s ON 1 = 0 ';

        $sql .= ' WHERE 1 = 1 ';
        $params = [];

        if ($search !== '') {
            $searchFilters = [];

            if ($this->hasColumn($columns, 'title')) {
                $searchFilters[] = 'm.title LIKE :search';
            }
            if ($this->hasColumn($columns, 'description')) {
                $searchFilters[] = 'm.description LIKE :search';
            }
            if ($this->hasColumn($columns, 'category')) {
                $searchFilters[] = 'm.category LIKE :search';
            }
            if ($this->hasColumn($columns, 'status')) {
                $searchFilters[] = 'm.status LIKE :search';
            }
            $searchFilters[] = 't.full_name LIKE :search';
            $searchFilters[] = 's.full_name LIKE :search';

            if (!empty($searchFilters)) {
                $sql .= ' AND (' . implode(' OR ', $searchFilters) . ') ';
                $params[':search'] = '%' . $search . '%';
            }
        }

        if ($status !== '' && $this->hasColumn($columns, 'status')) {
            $sql .= ' AND m.status = :status ';
            $params[':status'] = $status;
        }

        if ($this->hasColumn($columns, 'status')) {
            $sql .= " AND m.status <> 'completed' ";
        }

        if ($priority !== '' && $this->hasColumn($columns, 'priority')) {
            $sql .= ' AND m.priority = :priority ';
            $params[':priority'] = $priority;
        }

        $orderByColumn = $this->hasColumn($columns, 'created_at') ? 'created_at' : $idColumn;
        $sql .= " ORDER BY m.{$orderByColumn} DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchMaintenanceRequestById(int $requestId): ?array
    {
        if (!$this->tableExists('maintenance_requests')) {
            return null;
        }

        $columns = $this->getColumns('maintenance_requests');
        $idColumn = $this->getRequestIdColumn($columns);
        if ($idColumn === null) {
            return null;
        }

        $assignedStaffColumn = $this->getAssignedStaffColumn($columns);

        $selectParts = [
            "m.{$idColumn} AS request_id",
            $this->selectExpr($columns, 'title', 'title'),
            $this->selectExpr($columns, 'description', 'description'),
            $this->selectExpr($columns, 'category', 'category'),
            $this->selectExpr($columns, 'priority', 'priority'),
            $this->selectExpr($columns, 'status', 'status'),
            $this->selectExpr($columns, 'tenant_id', 'tenant_id'),
            $assignedStaffColumn !== null ? "m.{$assignedStaffColumn} AS assigned_staff_id" : 'NULL AS assigned_staff_id',
            $this->selectExpr($columns, 'created_at', 'created_at'),
            $this->selectExpr($columns, 'updated_at', 'updated_at'),
            't.full_name AS tenant_name',
            's.full_name AS staff_name',
        ];

        $sql = 'SELECT ' . implode(', ', $selectParts) . ' FROM maintenance_requests m ';
        $sql .= $this->hasColumn($columns, 'tenant_id')
            ? ' LEFT JOIN users t ON m.tenant_id = t.user_id '
            : ' LEFT JOIN users t ON 1 = 0 ';
        $sql .= $assignedStaffColumn !== null
            ? " LEFT JOIN users s ON m.{$assignedStaffColumn} = s.user_id "
            : ' LEFT JOIN users s ON 1 = 0 ';
        $sql .= " WHERE m.{$idColumn} = :request_id LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':request_id' => $requestId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
