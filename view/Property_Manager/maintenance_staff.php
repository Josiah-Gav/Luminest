<?php
session_start();
require_once __DIR__ . '/../../database/db.php';

if (isset($_SESSION['role']) && $_SESSION['role'] !== 'Property_Manager') {
    header('Location: ../../index.php');
    exit;
}

function tableExists(PDO $pdo, string $tableName): bool
{
    $stmt = $pdo->prepare("SHOW TABLES LIKE :table_name");
    $stmt->execute([':table_name' => $tableName]);
    return (bool)$stmt->fetchColumn();
}

function columnExists(PDO $pdo, string $tableName, string $columnName): bool
{
    $stmt = $pdo->prepare("SHOW COLUMNS FROM {$tableName} LIKE :column_name");
    $stmt->execute([':column_name' => $columnName]);
    return (bool)$stmt->fetchColumn();
}

function getAssignmentColumn(PDO $pdo): ?string
{
    if (!tableExists($pdo, 'maintenance_requests')) {
        return null;
    }

    if (columnExists($pdo, 'maintenance_requests', 'assigned_staff_id')) {
        return 'assigned_staff_id';
    }

    if (columnExists($pdo, 'maintenance_requests', 'assigned_staff')) {
        return 'assigned_staff';
    }

    return null;
}

function getMaintenanceRequestIdColumn(PDO $pdo): ?string
{
    if (!tableExists($pdo, 'maintenance_requests')) {
        return null;
    }

    if (columnExists($pdo, 'maintenance_requests', 'request_id')) {
        return 'request_id';
    }

    if (columnExists($pdo, 'maintenance_requests', 'id')) {
        return 'id';
    }

    return null;
}

function getMaintenanceStatusColumn(PDO $pdo): ?string
{
    if (!tableExists($pdo, 'maintenance_requests')) {
        return null;
    }

    if (columnExists($pdo, 'maintenance_requests', 'status')) {
        return 'status';
    }

    return null;
}

function fetchStaff(PDO $pdo, string $search = ''): array
{
    $assignedStaffColumn = getAssignmentColumn($pdo);
    $requestIdColumn = getMaintenanceRequestIdColumn($pdo);
    $statusColumn = getMaintenanceStatusColumn($pdo);
    $canCountAssignments = $assignedStaffColumn !== null && $requestIdColumn !== null;

    $select = "
        SELECT
            u.user_id,
            u.full_name,
            u.email,
            u.phone_number,
            u.created_at
    ";

    if ($canCountAssignments && $statusColumn !== null) {
        $select .= ", COALESCE(SUM(CASE WHEN m.{$statusColumn} IN ('pending', 'accepted', 'in-progress', 'on-hold') THEN 1 ELSE 0 END), 0) AS active_requests ";
        $select .= ", COALESCE(SUM(CASE WHEN m.{$statusColumn} IN ('resolved', 'completed') THEN 1 ELSE 0 END), 0) AS completed_requests ";
    } elseif ($canCountAssignments) {
        $select .= ", COALESCE(COUNT(m.{$requestIdColumn}), 0) AS active_requests ";
        $select .= ", 0 AS completed_requests ";
    } else {
        $select .= ", 0 AS active_requests ";
        $select .= ", 0 AS completed_requests ";
    }

    $sql = $select . "
        FROM users u
    ";

    if ($canCountAssignments) {
        $sql .= " LEFT JOIN maintenance_requests m ON u.user_id = m.{$assignedStaffColumn}";

        $sql .= " ";
    }

    $sql .= " WHERE u.role = 'Maintenance_Staff' ";

    $params = [];
    if ($search !== '') {
        $sql .= "
            AND (
                u.full_name LIKE :search
                OR u.email LIKE :search
                OR u.phone_number LIKE :search
                OR CAST(u.user_id AS CHAR) LIKE :search
            )
        ";
        $params[':search'] = '%' . $search . '%';
    }

    $sql .= " GROUP BY u.user_id ORDER BY u.created_at DESC, u.user_id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'remove_staff_role') {
    header('Content-Type: application/json');

    try {
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

        if (!$userId) {
            throw new RuntimeException('Invalid staff ID.');
        }

        $stmt = $pdo->prepare("UPDATE users SET role = 'Prospect' WHERE user_id = :user_id AND role = 'Maintenance_Staff'");
        $stmt->execute([':user_id' => $userId]);

        echo json_encode([
            'success' => true,
            'message' => "User #{$userId} removed from maintenance staff.",
        ]);
    } catch (Throwable $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
        ]);
    }

    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'search') {
    header('Content-Type: application/json');

    try {
        $search = trim((string)($_GET['q'] ?? ''));
        echo json_encode([
            'success' => true,
            'data' => fetchStaff($pdo, $search),
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

$errorMsg = null;
$staffMembers = [];

try {
    $staffMembers = fetchStaff($pdo);
} catch (Throwable $e) {
    $errorMsg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Staff Management - Luminest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div id="alertContainer"></div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0"><i class="fa-solid fa-user-gear text-dark me-2"></i>Maintenance Staff Management</h2>
            <p class="text-muted mb-0">AJAX search for maintenance staff with active and completed request counts</p>
        </div>
        <div class="d-flex gap-2">
            <a href="maintenance_history.php" class="btn btn-outline-dark btn-sm">
                <i class="fa-solid fa-clock-rotate-left me-1"></i> Maintenance History
            </a>
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input id="searchInput" type="text" class="form-control" placeholder="Search by name, email, phone, or ID...">
                    </div>
                </div>
                <div class="col-md-5 text-md-end">
                    <span class="badge bg-dark fs-6" id="resultCount">Total Staff: <?= count($staffMembers) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Active Assignments</th>
                            <th>Completed Jobs</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="staffTableBody">
                        <?php if (empty($staffMembers)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No maintenance staff found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($staffMembers as $row): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars((string)$row['user_id']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone_number'] ?? 'N/A') ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars((string)$row['active_requests']) ?></span></td>
                                    <td><span class="badge bg-success"><?= htmlspecialchars((string)$row['completed_requests']) ?></span></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove" data-user-id="<?= htmlspecialchars((string)$row['user_id']) ?>">
                                            Remove Staff Role
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('staffTableBody');
    const resultCount = document.getElementById('resultCount');
    const alertContainer = document.getElementById('alertContainer');

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function showAlert(message, type) {
        alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${escapeHtml(message)}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
    }

    function renderRows(rows) {
        resultCount.textContent = `Total Staff: ${rows.length}`;

        if (!Array.isArray(rows) || rows.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No matching staff found.</td></tr>';
            return;
        }

        tableBody.innerHTML = rows.map((row) => `
            <tr>
                <td><strong>#${escapeHtml(row.user_id)}</strong></td>
                <td>${escapeHtml(row.full_name)}</td>
                <td>${escapeHtml(row.email)}</td>
                <td>${escapeHtml(row.phone_number || 'N/A')}</td>
                <td><span class="badge bg-primary">${escapeHtml(row.active_requests)}</span></td>
                <td><span class="badge bg-success">${escapeHtml(row.completed_requests)}</span></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove" data-user-id="${escapeHtml(row.user_id)}">Remove Staff Role</button>
                </td>
            </tr>
        `).join('');
    }

    async function loadData() {
        try {
            const params = new URLSearchParams({
                ajax: 'search',
                q: searchInput.value.trim()
            });

            const res = await fetch(`maintenance_staff.php?${params.toString()}`);
            const payload = await res.json();

            if (payload.success) {
                renderRows(payload.data || []);
            }
        } catch (err) {
            console.error('Staff search error:', err);
        }
    }

    let debounce;
    searchInput.addEventListener('input', function () {
        clearTimeout(debounce);
        debounce = setTimeout(loadData, 300);
    });

    document.addEventListener('click', async function (event) {
        const btn = event.target.closest('.btn-remove');
        if (!btn) {
            return;
        }

        const userId = btn.getAttribute('data-user-id');
        if (!confirm('Remove this user from maintenance staff?')) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'remove_staff_role');
        formData.append('user_id', userId);

        try {
            const res = await fetch('maintenance_staff.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const payload = await res.json();

            if (payload.success) {
                showAlert(payload.message, 'success');
                loadData();
            } else {
                showAlert(payload.message || 'Failed to remove staff role.', 'danger');
            }
        } catch (err) {
            console.error('Role update error:', err);
            showAlert('Failed to update staff role.', 'danger');
        }
    });
});
</script>
</body>
</html>
