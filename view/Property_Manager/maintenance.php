<?php
require_once __DIR__ . '/../../database/db.php';
require_once __DIR__ . '/../../controllers/maintenance/property_manager_maintenance_controller.php';

$maintenanceController = new PropertyManagerMaintenanceController($pdo);
$maintenanceController->handleRequest();

$tableReady = $maintenanceController->isTableReady();
$errorMsg = $maintenanceController->getErrorMsg();
$requests = $maintenanceController->getRequests();
$staffList = $maintenanceController->getStaffList();
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
                        <option value="completed">Completed</option>
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
                                        <span class="badge bg-<?= htmlspecialchars($maintenanceController->statusBadgeClass($currentStatus)) ?>" data-status-label>
                                            <?= htmlspecialchars($maintenanceController->formatStatusLabel($currentStatus)) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php $currentPriority = strtolower((string)($row['priority'] ?? 'medium')); ?>
                                        <span class="badge bg-<?= htmlspecialchars($maintenanceController->priorityBadgeClass($currentPriority)) ?>" data-priority-label>
                                            <?= htmlspecialchars($maintenanceController->formatPriorityLabel($currentPriority)) ?>
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
window.pmMaintenanceConfig = {
    tableReady: <?= $tableReady ? 'true' : 'false' ?>,
    pageEndpoint: 'maintenance.php'
};
</script>
<script src="../../assets/js/property-manager-maintenance.js"></script>
</body>
</html>
