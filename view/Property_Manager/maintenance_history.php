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

function getColumns(PDO $pdo, string $tableName): array
{
    $stmt = $pdo->query("SHOW COLUMNS FROM {$tableName}");
    $cols = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $cols[] = $row['Field'];
    }
    return $cols;
}

function hasColumn(array $columns, string $name): bool
{
    return in_array($name, $columns, true);
}

function selectExpr(array $columns, string $column, string $alias): string
{
    if (hasColumn($columns, $column)) {
        return "m.{$column} AS {$alias}";
    }
    return "NULL AS {$alias}";
}

function getAssignedStaffColumn(array $columns): ?string
{
    if (hasColumn($columns, 'assigned_staff_id')) {
        return 'assigned_staff_id';
    }

    if (hasColumn($columns, 'assigned_staff')) {
        return 'assigned_staff';
    }

    return null;
}

function fetchMaintenanceHistory(PDO $pdo, string $search = '', string $status = ''): array
{
    if (!tableExists($pdo, 'maintenance_requests')) {
        return [];
    }

    $columns = getColumns($pdo, 'maintenance_requests');
    $idColumn = hasColumn($columns, 'request_id') ? 'request_id' : (hasColumn($columns, 'id') ? 'id' : null);

    if ($idColumn === null) {
        return [];
    }

    $assignedStaffColumn = getAssignedStaffColumn($columns);

    $selectParts = [
        "m.{$idColumn} AS request_id",
        selectExpr($columns, 'title', 'title'),
        selectExpr($columns, 'category', 'category'),
        selectExpr($columns, 'priority', 'priority'),
        selectExpr($columns, 'status', 'status'),
        selectExpr($columns, 'created_at', 'created_at'),
        selectExpr($columns, 'resolved_at', 'resolved_at'),
        selectExpr($columns, 'completed_at', 'completed_at'),
        "t.full_name AS tenant_name",
        "s.full_name AS staff_name"
    ];

    $sql = "SELECT " . implode(', ', $selectParts) . " FROM maintenance_requests m ";

    $sql .= hasColumn($columns, 'tenant_id')
        ? " LEFT JOIN users t ON m.tenant_id = t.user_id "
        : " LEFT JOIN users t ON 1 = 0 ";

    $sql .= $assignedStaffColumn !== null
        ? " LEFT JOIN users s ON m.{$assignedStaffColumn} = s.user_id "
        : " LEFT JOIN users s ON 1 = 0 ";

    $sql .= " WHERE 1 = 1 ";
    $params = [];

    if (hasColumn($columns, 'status')) {
        $sql .= " AND m.status IN ('resolved', 'completed') ";

        if ($status !== '') {
            $sql .= ' AND m.status = :status ';
            $params[':status'] = $status;
        }
    }

    if ($search !== '') {
        $searchFilters = [];

        if (hasColumn($columns, 'title')) {
            $searchFilters[] = 'm.title LIKE :search';
        }
        if (hasColumn($columns, 'category')) {
            $searchFilters[] = 'm.category LIKE :search';
        }
        $searchFilters[] = 't.full_name LIKE :search';
        $searchFilters[] = 's.full_name LIKE :search';

        if (!empty($searchFilters)) {
            $sql .= ' AND (' . implode(' OR ', $searchFilters) . ') ';
            $params[':search'] = '%' . $search . '%';
        }
    }

    $orderColumn = hasColumn($columns, 'completed_at') ? 'completed_at' : (hasColumn($columns, 'resolved_at') ? 'resolved_at' : (hasColumn($columns, 'updated_at') ? 'updated_at' : $idColumn));
    $sql .= " ORDER BY m.{$orderColumn} DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'search') {
    header('Content-Type: application/json');

    try {
        $search = trim((string)($_GET['q'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));

        echo json_encode([
            'success' => true,
            'data' => fetchMaintenanceHistory($pdo, $search, $status),
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
$historyRequests = [];

try {
    $historyRequests = fetchMaintenanceHistory($pdo);
} catch (Throwable $e) {
    $errorMsg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance History - Luminest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div id="alertContainer"></div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left text-dark me-2"></i>Maintenance History</h2>
            <p class="text-muted mb-0">Resolved and completed maintenance requests</p>
        </div>
        <div class="d-flex gap-2">
            <a href="maintenance.php" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-list-check me-1"></i> Active Maintenance
            </a>
            <a href="maintenance_staff.php" class="btn btn-outline-dark btn-sm">
                <i class="fa-solid fa-user-gear me-1"></i> Staff Management
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
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input id="searchInput" type="text" class="form-control" placeholder="Search title, category, tenant, staff...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="statusFilter" class="form-select">
                        <option value="">All Finished Statuses</option>
                        <option value="resolved">Resolved</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="col-md-3 text-md-end">
                    <span class="badge bg-dark fs-6" id="resultCount">Total: <?= count($historyRequests) ?></span>
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
                            <th>Title</th>
                            <th>Tenant</th>
                            <th>Staff</th>
                            <th>Status</th>
                            <th>Resolved At</th>
                            <th>Completed At</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody">
                        <?php if (empty($historyRequests)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No maintenance history found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($historyRequests as $row): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars((string)$row['request_id']) ?></strong></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($row['title'] ?? 'Untitled') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['category'] ?? 'N/A') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['tenant_name'] ?? 'Unknown Tenant') ?></td>
                                    <td><?= htmlspecialchars($row['staff_name'] ?? 'Unassigned') ?></td>
                                    <td>
                                        <?php $status = strtolower((string)($row['status'] ?? '')); ?>
                                        <span class="badge <?= $status === 'completed' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                            <?= htmlspecialchars(ucfirst($status !== '' ? $status : 'unknown')) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars((string)($row['resolved_at'] ?? 'N/A')) ?></td>
                                    <td><?= htmlspecialchars((string)($row['completed_at'] ?? 'N/A')) ?></td>
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
    const statusFilter = document.getElementById('statusFilter');
    const tableBody = document.getElementById('historyTableBody');
    const resultCount = document.getElementById('resultCount');

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderRows(rows) {
        resultCount.textContent = `Total: ${rows.length}`;

        if (!Array.isArray(rows) || rows.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No matching maintenance history found.</td></tr>';
            return;
        }

        tableBody.innerHTML = rows.map((row) => {
            const status = String(row.status || '').toLowerCase();
            const badgeClass = status === 'completed' ? 'bg-success' : 'bg-warning text-dark';

            return `
                <tr>
                    <td><strong>#${escapeHtml(row.request_id)}</strong></td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(row.title || 'Untitled')}</div>
                        <small class="text-muted">${escapeHtml(row.category || 'N/A')}</small>
                    </td>
                    <td>${escapeHtml(row.tenant_name || 'Unknown Tenant')}</td>
                    <td>${escapeHtml(row.staff_name || 'Unassigned')}</td>
                    <td><span class="badge ${badgeClass}">${escapeHtml(status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Unknown')}</span></td>
                    <td>${escapeHtml(row.resolved_at || 'N/A')}</td>
                    <td>${escapeHtml(row.completed_at || 'N/A')}</td>
                </tr>
            `;
        }).join('');
    }

    function loadData() {
        const params = $.param({
            ajax: 'search',
            q: searchInput.value.trim(),
            status: statusFilter.value
        });

        $.ajax({
            url: `maintenance_history.php?${params}`,
            type: 'GET',
            dataType: 'json',
            success: function (payload) {
                if (payload.success) {
                    renderRows(payload.data || []);
                }
            },
            error: function () {
                console.error('Maintenance history search error.');
            }
        });
    }

    let debounce;
    searchInput.addEventListener('input', function () {
        clearTimeout(debounce);
        debounce = setTimeout(loadData, 300);
    });

    statusFilter.addEventListener('change', loadData);
});
</script>
</body>
</html>
