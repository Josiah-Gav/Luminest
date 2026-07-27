<?php
session_start();
require_once __DIR__ . '/../../database/db.php';

if (isset($_SESSION['role']) && $_SESSION['role'] !== 'Property_Manager') {
    header('Location: ../../index.php');
    exit;
}

function fetchListings(PDO $pdo, string $search = '', string $status = ''): array
{
    $sql = "
        SELECT
            h.house_id,
            h.house_type,
            h.block,
            h.lot,
            h.status,
            h.owner_id,
            h.created_at,
            u.full_name AS owner_name,
            u.email AS owner_email
        FROM house h
        LEFT JOIN users u ON h.owner_id = u.user_id
        WHERE 1 = 1
    ";

    $params = [];

    if ($search !== '') {
        $sql .= "
            AND (
                h.house_type LIKE :search
                OR CAST(h.block AS CHAR) LIKE :search
                OR CAST(h.lot AS CHAR) LIKE :search
                OR h.status LIKE :search
                OR u.full_name LIKE :search
                OR u.email LIKE :search
            )
        ";
        $params[':search'] = '%' . $search . '%';
    }

    if ($status !== '') {
        $sql .= " AND h.status = :status ";
        $params[':status'] = $status;
    }

    $sql .= " ORDER BY h.created_at DESC, h.house_id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    header('Content-Type: application/json');

    try {
        $houseId = filter_input(INPUT_POST, 'house_id', FILTER_VALIDATE_INT);
        $status = trim((string)($_POST['status'] ?? ''));
        $allowedStatuses = ['available', 'reserved', 'sold'];

        if (!$houseId || !in_array($status, $allowedStatuses, true)) {
            throw new RuntimeException('Invalid house status update request.');
        }

        $stmt = $pdo->prepare("UPDATE house SET status = :status WHERE house_id = :house_id");
        $stmt->execute([
            ':status' => $status,
            ':house_id' => $houseId,
        ]);

        echo json_encode([
            'success' => true,
            'message' => "House #{$houseId} updated to {$status}.",
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
        echo json_encode([
            'success' => true,
            'data' => fetchListings($pdo, $search, $status),
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
$listings = [];

try {
    $listings = fetchListings($pdo);
} catch (Throwable $e) {
    $errorMsg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Listing Management - Luminest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div id="alertContainer"></div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0"><i class="fa-solid fa-list-check text-primary me-2"></i>Property Listing Management</h2>
            <p class="text-muted mb-0">AJAX search and status management for house inventory</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input id="searchInput" type="text" class="form-control" placeholder="Search house type, block, lot, owner...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="statusFilter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="available">Available</option>
                        <option value="reserved">Reserved</option>
                        <option value="sold">Sold</option>
                    </select>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-primary fs-6" id="resultCount">Total Listings: <?= count($listings) ?></span>
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
                            <th>House Type</th>
                            <th>Block / Lot</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th class="text-center">Update Status</th>
                        </tr>
                    </thead>
                    <tbody id="listingTableBody">
                        <?php if (empty($listings)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No listing records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($listings as $row): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars((string)$row['house_id']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['house_type']) ?></td>
                                    <td>Block <?= htmlspecialchars((string)$row['block']) ?> / Lot <?= htmlspecialchars((string)$row['lot']) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($row['owner_name'] ?? 'Unassigned') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['owner_email'] ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <?php
                                            $status = strtolower((string)$row['status']);
                                            $badge = 'bg-secondary';
                                            if ($status === 'available') {
                                                $badge = 'bg-success';
                                            } elseif ($status === 'reserved') {
                                                $badge = 'bg-warning text-dark';
                                            } elseif ($status === 'sold') {
                                                $badge = 'bg-danger';
                                            }
                                        ?>
                                        <span class="badge <?= $badge ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <form class="statusForm d-flex justify-content-center gap-2" data-house-id="<?= htmlspecialchars((string)$row['house_id']) ?>">
                                            <select name="status" class="form-select form-select-sm" style="max-width: 130px;">
                                                <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Available</option>
                                                <option value="reserved" <?= $status === 'reserved' ? 'selected' : '' ?>>Reserved</option>
                                                <option value="sold" <?= $status === 'sold' ? 'selected' : '' ?>>Sold</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                        </form>
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
    const statusFilter = document.getElementById('statusFilter');
    const tableBody = document.getElementById('listingTableBody');
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

    function statusBadge(status) {
        const normalized = String(status || '').toLowerCase();
        if (normalized === 'available') {
            return '<span class="badge bg-success">Available</span>';
        }
        if (normalized === 'reserved') {
            return '<span class="badge bg-warning text-dark">Reserved</span>';
        }
        if (normalized === 'sold') {
            return '<span class="badge bg-danger">Sold</span>';
        }
        return `<span class="badge bg-secondary">${escapeHtml(normalized || 'unknown')}</span>`;
    }

    function renderRows(rows) {
        resultCount.textContent = `Total Listings: ${rows.length}`;

        if (!Array.isArray(rows) || rows.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No matching listings found.</td></tr>';
            return;
        }

        tableBody.innerHTML = rows.map((row) => {
            const status = String(row.status || '').toLowerCase();
            return `
                <tr>
                    <td><strong>#${escapeHtml(row.house_id)}</strong></td>
                    <td>${escapeHtml(row.house_type || 'N/A')}</td>
                    <td>Block ${escapeHtml(row.block)} / Lot ${escapeHtml(row.lot)}</td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(row.owner_name || 'Unassigned')}</div>
                        <small class="text-muted">${escapeHtml(row.owner_email || 'N/A')}</small>
                    </td>
                    <td>${statusBadge(status)}</td>
                    <td class="text-center">
                        <form class="statusForm d-flex justify-content-center gap-2" data-house-id="${escapeHtml(row.house_id)}">
                            <select name="status" class="form-select form-select-sm" style="max-width: 130px;">
                                <option value="available" ${status === 'available' ? 'selected' : ''}>Available</option>
                                <option value="reserved" ${status === 'reserved' ? 'selected' : ''}>Reserved</option>
                                <option value="sold" ${status === 'sold' ? 'selected' : ''}>Sold</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                        </form>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function loadData() {
        try {
            const params = new URLSearchParams({
                ajax: 'search',
                q: searchInput.value.trim(),
                status: statusFilter.value
            });

            const res = await fetch(`listings.php?${params.toString()}`);
            const payload = await res.json();

            if (payload.success) {
                renderRows(payload.data || []);
            }
        } catch (err) {
            console.error('Listing search error:', err);
        }
    }

    let debounce;
    searchInput.addEventListener('input', function () {
        clearTimeout(debounce);
        debounce = setTimeout(loadData, 300);
    });
    statusFilter.addEventListener('change', loadData);

    document.addEventListener('submit', async function (event) {
        const form = event.target.closest('.statusForm');
        if (!form) {
            return;
        }

        event.preventDefault();
        const houseId = form.getAttribute('data-house-id');
        const status = form.querySelector('select[name="status"]').value;

        const formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('house_id', houseId);
        formData.append('status', status);

        try {
            const res = await fetch('listings.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const payload = await res.json();

            if (payload.success) {
                showAlert(payload.message, 'success');
                loadData();
            } else {
                showAlert(payload.message || 'Update failed.', 'danger');
            }
        } catch (err) {
            console.error('Listing update error:', err);
            showAlert('Failed to update listing status.', 'danger');
        }
    });
});
</script>
</body>
</html>
