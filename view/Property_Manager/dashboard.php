<?php
session_start();
require_once __DIR__ . '/../../database/db.php';

if (isset($_SESSION['role']) && $_SESSION['role'] !== 'Property_Manager') {
    header('Location: ../../index.php');
    exit;
}

function tableExists(PDO $pdo, string $tableName): bool
{
    $stmt = $pdo->prepare('SHOW TABLES LIKE :table_name');
    $stmt->execute([':table_name' => $tableName]);
    return (bool)$stmt->fetchColumn();
}

function getColumns(PDO $pdo, string $tableName): array
{
    $stmt = $pdo->query("SHOW COLUMNS FROM {$tableName}");
    $columns = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $columns[] = $row['Field'];
    }
    return $columns;
}

function getDashboardData(PDO $pdo): array
{
    $house = ['total' => 0, 'available' => 0, 'reserved' => 0, 'sold' => 0];
    $maintenance = ['pending' => 0, 'in_progress' => 0, 'completed' => 0];
    $recentMaintenance = [];

    if (tableExists($pdo, 'house')) {
        $stmt = $pdo->query('SELECT status, COUNT(*) AS total_count FROM house GROUP BY status');
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $status = strtolower((string)$row['status']);
            $count = (int)$row['total_count'];
            if (array_key_exists($status, $house)) {
                $house[$status] = $count;
            }
            $house['total'] += $count;
        }
    }

    if (tableExists($pdo, 'maintenance_requests')) {
        $mColumns = getColumns($pdo, 'maintenance_requests');

        if (in_array('status', $mColumns, true)) {
            $stmt = $pdo->query('SELECT status, COUNT(*) AS total_count FROM maintenance_requests GROUP BY status');
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $status = strtolower((string)$row['status']);
                $count = (int)$row['total_count'];

                if (in_array($status, ['pending', 'open'], true)) {
                    $maintenance['pending'] += $count;
                } elseif (in_array($status, ['in_progress', 'ongoing'], true)) {
                    $maintenance['in_progress'] += $count;
                } elseif (in_array($status, ['completed', 'resolved', 'closed'], true)) {
                    $maintenance['completed'] += $count;
                }
            }
        }

        $idColumn = in_array('request_id', $mColumns, true) ? 'request_id' : (in_array('id', $mColumns, true) ? 'id' : null);
        $orderColumn = in_array('created_at', $mColumns, true) ? 'created_at' : ($idColumn ?? 'status');

        if ($orderColumn !== null) {
            $selectRequest = $idColumn ? "m.{$idColumn} AS request_id" : 'NULL AS request_id';
            $selectTitle = in_array('title', $mColumns, true) ? 'm.title AS title' : 'NULL AS title';
            $selectStatus = in_array('status', $mColumns, true) ? 'm.status AS status' : 'NULL AS status';
            $selectCreated = in_array('created_at', $mColumns, true) ? 'm.created_at AS created_at' : 'NULL AS created_at';
            $selectBlock = in_array('block', $mColumns, true) ? 'm.block AS block' : 'NULL AS block';
            $selectLot = in_array('lot', $mColumns, true) ? 'm.lot AS lot' : 'NULL AS lot';
            $joinTenant = in_array('tenant_id', $mColumns, true) ? 'm.tenant_id = u.user_id' : '1 = 0';

            $sql = "
                SELECT
                    {$selectRequest},
                    {$selectTitle},
                    {$selectStatus},
                    {$selectCreated},
                    {$selectBlock},
                    {$selectLot},
                    u.full_name AS tenant_name
                FROM maintenance_requests m
                LEFT JOIN users u ON {$joinTenant}
                ORDER BY m.{$orderColumn} DESC
                LIMIT 5
            ";

            $stmt = $pdo->query($sql);
            $recentMaintenance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    return [
        'house' => $house,
        'maintenance' => $maintenance,
        'recentMaintenance' => $recentMaintenance,
        'generated_at' => date('c'),
    ];
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'dashboard_data') {
    header('Content-Type: application/json');

    try {
        echo json_encode(['success' => true, 'data' => getDashboardData($pdo)]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

$dbError = null;
try {
    $dashboardData = getDashboardData($pdo);
} catch (Throwable $e) {
    $dbError = $e->getMessage();
    $dashboardData = [
        'house' => ['total' => 0, 'available' => 0, 'reserved' => 0, 'sold' => 0],
        'maintenance' => ['pending' => 0, 'in_progress' => 0, 'completed' => 0],
        'recentMaintenance' => [],
    ];
}

$totalHouses = $dashboardData['house']['total'];
$availableHouses = $dashboardData['house']['available'];
$reservedHouses = $dashboardData['house']['reserved'];
$soldHouses = $dashboardData['house']['sold'];
$pendingMaint = $dashboardData['maintenance']['pending'];
$inProgressMaint = $dashboardData['maintenance']['in_progress'];
$completedMaint = $dashboardData['maintenance']['completed'];
$recentMaintenance = $dashboardData['recentMaintenance'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Manager Dashboard | Luminest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#"><i class="fa-solid fa-building-user me-2"></i>Luminest</a>
        <div class="collapse navbar-collapse show" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="maintenance.php">Maintenance</a></li>
                <li class="nav-item"><a class="nav-link" href="tenants.php">Tenants</a></li>
                <li class="nav-item"><a class="nav-link" href="listings.php">Listings</a></li>
                <li class="nav-item"><a class="nav-link" href="maintenance_staff.php">Maintenance Staff</a></li>
            </ul>
            <div class="d-flex align-items-center text-white">
                <span class="me-3"><i class="fa-solid fa-user me-1"></i> <?= htmlspecialchars($_SESSION['username'] ?? 'Property Manager') ?></span>
                <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </div>
</nav>

<div class="container my-4">
    <?php if ($dbError): ?>
        <div class="alert alert-warning">
            Database warning: <?= htmlspecialchars($dbError) ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Property Manager Dashboard</h2>
        <button id="refreshDashboardBtn" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-rotate me-1"></i>Refresh</button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm bg-primary text-white"><div class="card-body"><h6>Total Units</h6><h3 id="totalHousesValue"><?= $totalHouses ?></h3></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm bg-success text-white"><div class="card-body"><h6>Available Units</h6><h3 id="availableHousesValue"><?= $availableHouses ?></h3></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm bg-warning text-dark"><div class="card-body"><h6>Pending Maintenance</h6><h3 id="pendingMaintValue"><?= $pendingMaint ?></h3></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm bg-info text-white"><div class="card-body"><h6>In Progress</h6><h3 id="inProgressMaintValue"><?= $inProgressMaint ?></h3></div></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><h6>Reserved Units</h6><h4 id="reservedHousesValue"><?= $reservedHouses ?></h4></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><h6>Sold Units</h6><h4 id="soldHousesValue"><?= $soldHouses ?></h4></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><h6>Completed Repairs</h6><h4 id="completedMaintValue"><?= $completedMaint ?></h4></div></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-wrench text-warning me-2"></i>Recent Maintenance Requests</span>
            <a href="maintenance.php" class="btn btn-sm btn-light">Open Module</a>
        </div>
        <div id="recentMaintenanceContainer" class="card-body p-0">
            <?php if (empty($recentMaintenance)): ?>
                <p class="text-muted p-3 mb-0 text-center">No maintenance requests found.</p>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentMaintenance as $req): ?>
                        <?php $status = strtolower((string)($req['status'] ?? '')); ?>
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($req['title'] ?? 'Untitled Request') ?></div>
                                <small class="text-muted">By <?= htmlspecialchars($req['tenant_name'] ?? 'Unknown Tenant') ?></small>
                            </div>
                            <span class="badge bg-<?= $status === 'pending' ? 'danger' : ($status === 'in_progress' ? 'warning' : 'success') ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $status ?: 'unknown'))) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const totalEl = document.getElementById('totalHousesValue');
    const availableEl = document.getElementById('availableHousesValue');
    const reservedEl = document.getElementById('reservedHousesValue');
    const soldEl = document.getElementById('soldHousesValue');
    const pendingEl = document.getElementById('pendingMaintValue');
    const inProgressEl = document.getElementById('inProgressMaintValue');
    const completedEl = document.getElementById('completedMaintValue');
    const recentContainer = document.getElementById('recentMaintenanceContainer');
    const refreshBtn = document.getElementById('refreshDashboardBtn');

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderRecent(items) {
        if (!Array.isArray(items) || items.length === 0) {
            recentContainer.innerHTML = '<p class="text-muted p-3 mb-0 text-center">No maintenance requests found.</p>';
            return;
        }

        const html = items.map((item) => {
            const status = String(item.status || '').toLowerCase();
            let badgeClass = 'success';
            if (status === 'pending') {
                badgeClass = 'danger';
            } else if (status === 'in_progress') {
                badgeClass = 'warning text-dark';
            }

            return `
                <div class="list-group-item d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-bold">${escapeHtml(item.title || 'Untitled Request')}</div>
                        <small class="text-muted">By ${escapeHtml(item.tenant_name || 'Unknown Tenant')}</small>
                    </div>
                    <span class="badge bg-${badgeClass}">${escapeHtml((status || 'unknown').replace('_', ' '))}</span>
                </div>
            `;
        }).join('');

        recentContainer.innerHTML = `<div class="list-group list-group-flush">${html}</div>`;
    }

    async function refreshDashboard() {
        try {
            const res = await fetch('dashboard.php?ajax=dashboard_data', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const payload = await res.json();
            if (!payload.success) {
                return;
            }

            const data = payload.data || {};
            const house = data.house || {};
            const maintenance = data.maintenance || {};

            totalEl.textContent = Number(house.total || 0);
            availableEl.textContent = Number(house.available || 0);
            reservedEl.textContent = Number(house.reserved || 0);
            soldEl.textContent = Number(house.sold || 0);
            pendingEl.textContent = Number(maintenance.pending || 0);
            inProgressEl.textContent = Number(maintenance.in_progress || 0);
            completedEl.textContent = Number(maintenance.completed || 0);
            renderRecent(data.recentMaintenance || []);
        } catch (error) {
            console.error('Dashboard AJAX refresh failed:', error);
        }
    }

    refreshBtn.addEventListener('click', refreshDashboard);
    setInterval(refreshDashboard, 30000);
});
</script>
</body>
</html>
