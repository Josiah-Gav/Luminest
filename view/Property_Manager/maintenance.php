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

function getStaffList(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT user_id, full_name, expertise FROM users WHERE role = 'Maintenance_Staff' ORDER BY full_name ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function formatStatusLabel(string $status): string
{
    return ucwords(str_replace(['-', '_'], ' ', strtolower($status)));
}

function statusBadgeClass(string $status): string
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

function formatPriorityLabel(string $priority): string
{
    return ucwords(str_replace(['-', '_'], ' ', strtolower($priority)));
}

function priorityBadgeClass(string $priority): string
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

function fetchMaintenanceRequests(PDO $pdo, string $search = '', string $status = '', string $priority = ''): array
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
        selectExpr($columns, 'description', 'description'),
        selectExpr($columns, 'category', 'category'),
        selectExpr($columns, 'priority', 'priority'),
        selectExpr($columns, 'status', 'status'),
        selectExpr($columns, 'tenant_id', 'tenant_id'),
        $assignedStaffColumn !== null ? "m.{$assignedStaffColumn} AS assigned_staff_id" : "NULL AS assigned_staff_id",
        selectExpr($columns, 'block', 'block'),
        selectExpr($columns, 'lot', 'lot'),
        selectExpr($columns, 'created_at', 'created_at'),
        selectExpr($columns, 'updated_at', 'updated_at'),
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

    if ($search !== '') {
        $searchFilters = [];

        if (hasColumn($columns, 'title')) {
            $searchFilters[] = 'm.title LIKE :search';
        }
        if (hasColumn($columns, 'description')) {
            $searchFilters[] = 'm.description LIKE :search';
        }
        if (hasColumn($columns, 'category')) {
            $searchFilters[] = 'm.category LIKE :search';
        }
        if (hasColumn($columns, 'status')) {
            $searchFilters[] = 'm.status LIKE :search';
        }
        $searchFilters[] = 't.full_name LIKE :search';
        $searchFilters[] = 's.full_name LIKE :search';

        if (!empty($searchFilters)) {
            $sql .= ' AND (' . implode(' OR ', $searchFilters) . ') ';
            $params[':search'] = '%' . $search . '%';
        }
    }

    if ($status !== '' && hasColumn($columns, 'status')) {
        $sql .= ' AND m.status = :status ';
        $params[':status'] = $status;
    }

    if (hasColumn($columns, 'status')) {
        $sql .= " AND m.status <> 'completed' ";
    }

    if ($priority !== '' && hasColumn($columns, 'priority')) {
        $sql .= ' AND m.priority = :priority ';
        $params[':priority'] = $priority;
    }

    $orderByColumn = hasColumn($columns, 'created_at') ? 'created_at' : $idColumn;
    $sql .= " ORDER BY m.{$orderByColumn} DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchMaintenanceRequestById(PDO $pdo, int $requestId): ?array
{
    if (!tableExists($pdo, 'maintenance_requests')) {
        return null;
    }

    $columns = getColumns($pdo, 'maintenance_requests');
    $idColumn = hasColumn($columns, 'request_id') ? 'request_id' : (hasColumn($columns, 'id') ? 'id' : null);

    if ($idColumn === null) {
        return null;
    }

    $assignedStaffColumn = getAssignedStaffColumn($columns);

    $selectParts = [
        "m.{$idColumn} AS request_id",
        selectExpr($columns, 'title', 'title'),
        selectExpr($columns, 'description', 'description'),
        selectExpr($columns, 'category', 'category'),
        selectExpr($columns, 'priority', 'priority'),
        selectExpr($columns, 'status', 'status'),
        selectExpr($columns, 'tenant_id', 'tenant_id'),
        $assignedStaffColumn !== null ? "m.{$assignedStaffColumn} AS assigned_staff_id" : "NULL AS assigned_staff_id",
        selectExpr($columns, 'created_at', 'created_at'),
        selectExpr($columns, 'updated_at', 'updated_at'),
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
    $sql .= " WHERE m.{$idColumn} = :request_id LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':request_id' => $requestId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_request') {
    header('Content-Type: application/json');

    try {
        if (!tableExists($pdo, 'maintenance_requests')) {
            throw new RuntimeException('maintenance_requests table not found.');
        }

        $columns = getColumns($pdo, 'maintenance_requests');
        $idColumn = hasColumn($columns, 'request_id') ? 'request_id' : (hasColumn($columns, 'id') ? 'id' : null);

        if ($idColumn === null) {
            throw new RuntimeException('No request identifier column found.');
        }

        $requestId = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
        if (!$requestId) {
            throw new RuntimeException('Invalid request ID.');
        }

        $sets = [];
        $params = [':request_id' => $requestId];

        $requestLookup = $pdo->prepare("SELECT * FROM maintenance_requests WHERE {$idColumn} = :request_id LIMIT 1");
        $requestLookup->execute([':request_id' => $requestId]);
        $requestRow = $requestLookup->fetch(PDO::FETCH_ASSOC);

        if (!$requestRow) {
            throw new RuntimeException('Maintenance request not found.');
        }

        $assignedStaffColumn = getAssignedStaffColumn($columns);

        if ($assignedStaffColumn !== null) {
            $staffIdRaw = trim((string)($_POST['assigned_staff_id'] ?? $_POST['assigned_staff'] ?? ''));
            if ($staffIdRaw === '') {
                $sets[] = "{$assignedStaffColumn} = NULL";

                if (hasColumn($columns, 'status') && strtolower((string)($requestRow['status'] ?? 'pending')) === 'accepted') {
                    $sets[] = 'status = :status';
                    $params[':status'] = 'pending';
                }
            } else {
                $staffId = filter_var($staffIdRaw, FILTER_VALIDATE_INT);
                if ($staffId === false) {
                    throw new RuntimeException('Invalid assigned staff ID.');
                }

                $requestCategory = strtolower((string)($requestRow['category'] ?? ''));

                $staffQuery = $pdo->prepare("SELECT role, expertise FROM users WHERE user_id = :staff_id LIMIT 1");
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

                if (hasColumn($columns, 'status') && strtolower((string)($requestRow['status'] ?? 'pending')) === 'pending') {
                    $sets[] = 'status = :status';
                    $params[':status'] = 'accepted';
                }
            }
        }

        if (empty($sets)) {
            throw new RuntimeException('No valid fields to update.');
        }

        if (hasColumn($columns, 'updated_at')) {
            $sets[] = 'updated_at = NOW()';
        }

        $sql = "UPDATE maintenance_requests SET " . implode(', ', $sets) . " WHERE {$idColumn} = :request_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $updatedRow = fetchMaintenanceRequestById($pdo, $requestId);

        echo json_encode([
            'success' => true,
            'message' => "Maintenance request #{$requestId} updated.",
            'data' => $updatedRow,
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
        $status = trim((string)($_GET['status'] ?? ''));
        $priority = trim((string)($_GET['priority'] ?? ''));

        echo json_encode([
            'success' => true,
            'data' => fetchMaintenanceRequests($pdo, $search, $status, $priority),
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

$tableReady = tableExists($pdo, 'maintenance_requests');
$errorMsg = null;
$requests = [];
$staffList = [];

if ($tableReady) {
    try {
        $requests = fetchMaintenanceRequests($pdo);
        $staffList = getStaffList($pdo);
    } catch (Throwable $e) {
        $errorMsg = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Request Module - Luminest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div id="alertContainer"></div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0"><i class="fa-solid fa-screwdriver-wrench text-warning me-2"></i>Maintenance Request Module</h2>
            <p class="text-muted mb-0">Track, search, assign, and update maintenance concerns</p>
        </div>
        <div class="d-flex gap-2">
            <a href="maintenance_history.php" class="btn btn-outline-dark btn-sm">
                <i class="fa-solid fa-clock-rotate-left me-1"></i> History
            </a>
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <?php if (!$tableReady): ?>
        <div class="alert alert-warning">
            maintenance_requests table is not available yet. Run migrations first to activate this module.
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input id="searchInput" type="text" class="form-control" placeholder="Search title, tenant, staff, status..." <?= !$tableReady ? 'disabled' : '' ?>>
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="statusFilter" class="form-select" <?= !$tableReady ? 'disabled' : '' ?>>
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="accepted">Accepted</option>
                        <option value="in-progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="on-hold">On Hold</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="priorityFilter" class="form-select" <?= !$tableReady ? 'disabled' : '' ?>>
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="col-md-2 text-md-end">
                    <span class="badge bg-warning text-dark fs-6" id="resultCount">Total: <?= count($requests) ?></span>
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
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Assigned Staff</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody id="maintenanceTableBody">
                        <?php if (empty($requests)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No maintenance requests found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($requests as $row): ?>
                                <tr data-request-id="<?= htmlspecialchars((string)$row['request_id']) ?>">
                                    <td><strong>#<?= htmlspecialchars((string)$row['request_id']) ?></strong></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($row['title'] ?? 'Untitled') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['category'] ?? 'N/A') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['tenant_name'] ?? 'Unknown Tenant') ?></td>
                                    <td>
                                        <?php $currentStatus = strtolower((string)($row['status'] ?? 'pending')); ?>
                                        <span class="badge bg-<?= htmlspecialchars(statusBadgeClass($currentStatus)) ?>" data-status-label>
                                            <?= htmlspecialchars(formatStatusLabel($currentStatus)) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php $currentPriority = strtolower((string)($row['priority'] ?? 'medium')); ?>
                                        <span class="badge bg-<?= htmlspecialchars(priorityBadgeClass($currentPriority)) ?>" data-priority-label>
                                            <?= htmlspecialchars(formatPriorityLabel($currentPriority)) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm update-field" data-request-id="<?= htmlspecialchars((string)$row['request_id']) ?>" data-field="assigned_staff_id">
                                            <option value="">Unassigned</option>
                                            <?php foreach ($staffList as $staff): ?>
                                                <option value="<?= htmlspecialchars((string)$staff['user_id']) ?>" <?= (string)$row['assigned_staff_id'] === (string)$staff['user_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($staff['full_name'] . (!empty($staff['expertise']) ? ' (' . $staff['expertise'] . ')' : '')) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td data-updated-at><?= !empty($row['updated_at']) ? htmlspecialchars((string)$row['updated_at']) : htmlspecialchars((string)($row['created_at'] ?? 'N/A')) ?></td>
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
    const tableReady = <?= $tableReady ? 'true' : 'false' ?>;
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const tableBody = document.getElementById('maintenanceTableBody');
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

    function staffOptions(selectedId) {
        const options = ["<option value=''>Unassigned</option>"];
        document.querySelectorAll('#maintenanceTableBody tr select[data-field="assigned_staff_id"] option').forEach((opt) => {
            if (opt.value === '') {
                return;
            }
            const selected = String(opt.value) === String(selectedId) ? 'selected' : '';
            options.push(`<option value="${escapeHtml(opt.value)}" ${selected}>${escapeHtml(opt.textContent)}</option>`);
        });
        return options.join('');
    }

    function getStatusBadgeClass(status) {
        const normalized = String(status || 'pending').toLowerCase();
        if (normalized === 'resolved') {
            return 'success';
        }
        if (normalized === 'accepted') {
            return 'primary';
        }
        if (normalized === 'in-progress') {
            return 'info';
        }
        if (normalized === 'on-hold') {
            return 'warning text-dark';
        }
        if (normalized === 'cancelled' || normalized === 'rejected') {
            return 'danger';
        }
        return 'secondary';
    }

    function formatStatusLabel(status) {
        return String(status || 'pending')
            .toLowerCase()
            .replace(/[-_]/g, ' ')
            .replace(/\b\w/g, (char) => char.toUpperCase());
    }

    function getPriorityBadgeClass(priority) {
        const normalized = String(priority || 'medium').toLowerCase();
        if (normalized === 'urgent') {
            return 'danger';
        }
        if (normalized === 'high') {
            return 'warning text-dark';
        }
        if (normalized === 'medium') {
            return 'primary';
        }
        return 'secondary';
    }

    function formatPriorityLabel(priority) {
        return String(priority || 'medium')
            .toLowerCase()
            .replace(/[-_]/g, ' ')
            .replace(/\b\w/g, (char) => char.toUpperCase());
    }

    function findRowByRequestId(requestId) {
        return Array.from(tableBody.querySelectorAll('tr')).find((tr) => String(tr.getAttribute('data-request-id')) === String(requestId)) || null;
    }

    function updateRowInPlace(row) {
        if (!row || !row.request_id) {
            return;
        }

        const tr = findRowByRequestId(row.request_id);
        if (!tr) {
            return;
        }

        const status = String(row.status || 'pending').toLowerCase();
        const statusBadge = tr.querySelector('[data-status-label]');
        if (statusBadge) {
            statusBadge.className = `badge bg-${getStatusBadgeClass(status)}`;
            statusBadge.textContent = formatStatusLabel(status);
        }

        const staffSelect = tr.querySelector('select[data-field="assigned_staff_id"]');
        if (staffSelect) {
            const assignedId = row.assigned_staff_id == null ? '' : String(row.assigned_staff_id);
            staffSelect.value = assignedId;
        }

        const priority = String(row.priority || 'medium').toLowerCase();
        const priorityBadge = tr.querySelector('[data-priority-label]');
        if (priorityBadge) {
            priorityBadge.className = `badge bg-${getPriorityBadgeClass(priority)}`;
            priorityBadge.textContent = formatPriorityLabel(priority);
        }

        const updatedAtCell = tr.querySelector('[data-updated-at]');
        if (updatedAtCell) {
            updatedAtCell.textContent = String(row.updated_at || row.created_at || 'N/A');
        }
    }

    function renderRows(rows) {
        resultCount.textContent = `Total: ${rows.length}`;

        if (!Array.isArray(rows) || rows.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No matching maintenance requests found.</td></tr>';
            return;
        }

        tableBody.innerHTML = rows.map((row) => {
            const status = String(row.status || 'pending').toLowerCase();
            const priority = String(row.priority || 'medium').toLowerCase();

            return `
                <tr data-request-id="${escapeHtml(row.request_id)}">
                    <td><strong>#${escapeHtml(row.request_id)}</strong></td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(row.title || 'Untitled')}</div>
                        <small class="text-muted">${escapeHtml(row.category || 'N/A')}</small>
                    </td>
                    <td>${escapeHtml(row.tenant_name || 'Unknown Tenant')}</td>
                    <td><span class="badge bg-${getStatusBadgeClass(status)}" data-status-label>${escapeHtml(formatStatusLabel(status))}</span></td>
                    <td><span class="badge bg-${getPriorityBadgeClass(priority)}" data-priority-label>${escapeHtml(formatPriorityLabel(priority))}</span></td>
                    <td>
                        <select class="form-select form-select-sm update-field" data-request-id="${escapeHtml(row.request_id)}" data-field="assigned_staff_id">
                            ${staffOptions(row.assigned_staff_id)}
                        </select>
                    </td>
                    <td data-updated-at>${escapeHtml(row.updated_at || row.created_at || 'N/A')}</td>
                </tr>
            `;
        }).join('');
    }

    async function loadData() {
        if (!tableReady) {
            return;
        }

        try {
            const params = new URLSearchParams({
                ajax: 'search',
                q: searchInput.value.trim(),
                status: statusFilter.value,
                priority: priorityFilter.value
            });

            const res = await fetch(`maintenance.php?${params.toString()}`);
            const payload = await res.json();

            if (payload.success) {
                renderRows(payload.data || []);
            }
        } catch (err) {
            console.error('Maintenance search error:', err);
        }
    }

    if (tableReady) {
        let debounce;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounce);
            debounce = setTimeout(loadData, 300);
        });
        statusFilter.addEventListener('change', loadData);
        priorityFilter.addEventListener('change', loadData);
    }

    document.addEventListener('change', async function (event) {
        const field = event.target.closest('.update-field');
        if (!field || !tableReady) {
            return;
        }

        const requestId = field.getAttribute('data-request-id');
        const fieldName = field.getAttribute('data-field');
        const fieldValue = field.value;

        const formData = new FormData();
        formData.append('action', 'update_request');
        formData.append('request_id', requestId);
        formData.append(fieldName, fieldValue);

        try {
            const res = await fetch('maintenance.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const payload = await res.json();

            if (payload.success) {
                showAlert(payload.message, 'success');
                if (payload.data) {
                    updateRowInPlace(payload.data);
                }

                const hasActiveFilters = searchInput.value.trim() !== '' || statusFilter.value !== '' || priorityFilter.value !== '';
                if (hasActiveFilters) {
                    loadData();
                }
            } else {
                showAlert(payload.message || 'Update failed.', 'danger');
            }
        } catch (err) {
            console.error('Maintenance update error:', err);
            showAlert('Failed to update maintenance request.', 'danger');
        }
    });

    if (tableReady) {
        setInterval(loadData, 10000);
    }
});
</script>
</body>
</html>
