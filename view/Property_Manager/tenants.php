<?php
session_start();
require_once __DIR__ . '/../../database/db.php';

if (isset($_SESSION['role']) && $_SESSION['role'] !== 'Property_Manager') {
    header('Location: ../../index.php');
    exit;
}

if (isset($_GET['ajax_search'])) {
    header('Content-Type: application/json');
    $search = trim((string)($_GET['q'] ?? ''));

    try {
        $sql = "
            SELECT
                u.user_id AS tenant_id,
                u.full_name,
                u.email,
                u.phone_number,
                hr.reservation_id,
                hr.house_type,
                hr.block,
                hr.lot,
                hr.status AS reservation_status,
                hr.created_at AS reserved_date
            FROM users u
            LEFT JOIN house_reservations hr ON u.user_id = hr.user_id
            WHERE u.role = 'Tenant'
        ";

        if ($search !== '') {
            $sql .= "
                AND (
                    u.full_name LIKE :q OR
                    u.email LIKE :q OR
                    u.phone_number LIKE :q OR
                    hr.house_type LIKE :q OR
                    CAST(hr.block AS CHAR) LIKE :q OR
                    CAST(hr.lot AS CHAR) LIKE :q OR
                    hr.status LIKE :q
                )
            ";
        }

        $sql .= " ORDER BY u.user_id DESC, hr.created_at DESC";

        $stmt = $pdo->prepare($sql);
        if ($search !== '') {
            $stmt->bindValue(':q', '%' . $search . '%');
        }
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
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

$initialTenants = [];
$errorMsg = null;

try {
    $stmt = $pdo->query(" 
        SELECT
            u.user_id AS tenant_id,
            u.full_name,
            u.email,
            u.phone_number,
            hr.reservation_id,
            hr.house_type,
            hr.block,
            hr.lot,
            hr.status AS reservation_status,
            hr.created_at AS reserved_date
        FROM users u
        LEFT JOIN house_reservations hr ON u.user_id = hr.user_id
        WHERE u.role = 'Tenant'
        ORDER BY u.user_id DESC, hr.created_at DESC
    ");
    $initialTenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $errorMsg = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Management - Luminest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .search-box {
            max-width: 420px;
        }
    </style>
</head>
<body class="bg-light">

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0"><i class="fa-solid fa-users text-primary me-2"></i>Tenant Management</h2>
                <p class="text-muted small mb-0">AJAX search for tenant accounts and lease details</p>
            </div>
            <div class="d-flex gap-2">
                <a href="reservation_list.php" class="btn btn-outline-success btn-sm">
                    <i class="fa-solid fa-file-signature me-1"></i> Reservations
                </a>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>

        <?php if ($errorMsg): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group search-box">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" id="tenantSearchInput" class="form-control border-start-0 ps-0" placeholder="Search tenant name, email, house type, block, lot, status...">
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <span class="badge bg-primary text-wrap fs-6" id="tenantCounter">
                            Total Records: <?= count($initialTenants) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Tenant</th>
                                <th>Contact</th>
                                <th>House Type</th>
                                <th>Block / Lot</th>
                                <th>Reservation Status</th>
                                <th>Reserved Date</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tenantTableBody">
                            <?php if (empty($initialTenants)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No tenant records found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($initialTenants as $tenant): ?>
                                    <?php
                                        $status = strtolower((string)($tenant['reservation_status'] ?? ''));
                                        $statusLabel = $status !== '' ? ucfirst($status) : 'No Reservation';
                                        $badgeClass = 'bg-light text-dark border';
                                        if (in_array($status, ['accepted', 'paid', 'completed'], true)) {
                                            $badgeClass = 'bg-success';
                                        } elseif ($status === 'pending') {
                                            $badgeClass = 'bg-warning text-dark';
                                        } elseif (in_array($status, ['rejected', 'cancelled'], true)) {
                                            $badgeClass = 'bg-danger';
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($tenant['full_name']) ?></div>
                                            <small class="text-muted">#<?= htmlspecialchars((string)$tenant['tenant_id']) ?></small>
                                        </td>
                                        <td>
                                            <small class="d-block text-muted"><?= htmlspecialchars($tenant['email']) ?></small>
                                            <small><?= htmlspecialchars($tenant['phone_number'] ?? 'N/A') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($tenant['house_type'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($tenant['block'] !== null && $tenant['lot'] !== null): ?>
                                                <span class="badge bg-secondary">Block <?= htmlspecialchars((string)$tenant['block']) ?> / Lot <?= htmlspecialchars((string)$tenant['lot']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                                        <td><?= !empty($tenant['reserved_date']) ? htmlspecialchars(date('M d, Y h:i A', strtotime($tenant['reserved_date']))) : 'N/A' ?></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-view"
                                                data-name="<?= htmlspecialchars($tenant['full_name']) ?>"
                                                data-email="<?= htmlspecialchars($tenant['email']) ?>"
                                                data-phone="<?= htmlspecialchars($tenant['phone_number'] ?? 'N/A') ?>"
                                                data-house-type="<?= htmlspecialchars($tenant['house_type'] ?? 'N/A') ?>"
                                                data-block="<?= htmlspecialchars((string)($tenant['block'] ?? 'N/A')) ?>"
                                                data-lot="<?= htmlspecialchars((string)($tenant['lot'] ?? 'N/A')) ?>"
                                                data-status="<?= htmlspecialchars($statusLabel) ?>"
                                                data-date="<?= !empty($tenant['reserved_date']) ? htmlspecialchars(date('M d, Y h:i A', strtotime($tenant['reserved_date']))) : 'N/A' ?>">
                                                <i class="fa-solid fa-eye me-1"></i> Details
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

    <div class="modal fade" id="tenantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-id-card me-2 text-primary"></i>Tenant Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Name:</strong> <span id="mName"></span></li>
                        <li class="list-group-item"><strong>Email:</strong> <span id="mEmail"></span></li>
                        <li class="list-group-item"><strong>Phone:</strong> <span id="mPhone"></span></li>
                        <li class="list-group-item"><strong>House Type:</strong> <span id="mHouseType"></span></li>
                        <li class="list-group-item"><strong>Block / Lot:</strong> <span id="mBlockLot"></span></li>
                        <li class="list-group-item"><strong>Status:</strong> <span id="mStatus"></span></li>
                        <li class="list-group-item"><strong>Reserved Date:</strong> <span id="mDate"></span></li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('tenantSearchInput');
            const tableBody = document.getElementById('tenantTableBody');
            const counter = document.getElementById('tenantCounter');
            const tenantModal = new bootstrap.Modal(document.getElementById('tenantModal'));

            let debounceTimer;
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(async () => {
                    const query = this.value.trim();
                    const params = new URLSearchParams({
                        ajax_search: '1',
                        q: query
                    });

                    try {
                        const response = await fetch(`tenants.php?${params.toString()}`);
                        const payload = await response.json();

                        if (payload.success) {
                            renderTable(payload.data || []);
                        } else {
                            console.error('Tenant search error:', payload.message || 'Unknown error');
                        }
                    } catch (error) {
                        console.error('Tenant search error:', error);
                    }
                }, 300);
            });

            function escapeHtml(str) {
                return String(str ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function statusBadge(status) {
                const normalized = String(status || '').toLowerCase();
                if (['accepted', 'paid', 'completed'].includes(normalized)) {
                    return '<span class="badge bg-success">' + escapeHtml(normalized.charAt(0).toUpperCase() + normalized.slice(1)) + '</span>';
                }
                if (normalized === 'pending') {
                    return '<span class="badge bg-warning text-dark">Pending</span>';
                }
                if (['rejected', 'cancelled'].includes(normalized)) {
                    return '<span class="badge bg-danger">' + escapeHtml(normalized.charAt(0).toUpperCase() + normalized.slice(1)) + '</span>';
                }
                return '<span class="badge bg-light text-dark border">No Reservation</span>';
            }

            function renderTable(data) {
                counter.textContent = `Total Records: ${data.length}`;

                if (!Array.isArray(data) || data.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No matching tenants found.</td></tr>';
                    return;
                }

                const html = data.map(t => {
                    const dateValue = t.reserved_date ? new Date(t.reserved_date).toLocaleString() : 'N/A';
                    const blockLot = (t.block !== null && t.lot !== null)
                        ? `<span class="badge bg-secondary">Block ${escapeHtml(t.block)} / Lot ${escapeHtml(t.lot)}</span>`
                        : '<span class="text-muted">N/A</span>';

                    const statusLabel = t.reservation_status
                        ? t.reservation_status.charAt(0).toUpperCase() + t.reservation_status.slice(1)
                        : 'No Reservation';

                    return `
                        <tr>
                            <td>
                                <div class="fw-bold">${escapeHtml(t.full_name)}</div>
                                <small class="text-muted">#${escapeHtml(t.tenant_id)}</small>
                            </td>
                            <td>
                                <small class="d-block text-muted">${escapeHtml(t.email)}</small>
                                <small>${escapeHtml(t.phone_number || 'N/A')}</small>
                            </td>
                            <td>${escapeHtml(t.house_type || 'N/A')}</td>
                            <td>${blockLot}</td>
                            <td>${statusBadge(t.reservation_status)}</td>
                            <td>${escapeHtml(dateValue)}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-view"
                                    data-name="${escapeHtml(t.full_name)}"
                                    data-email="${escapeHtml(t.email)}"
                                    data-phone="${escapeHtml(t.phone_number || 'N/A')}"
                                    data-house-type="${escapeHtml(t.house_type || 'N/A')}"
                                    data-block="${escapeHtml((t.block ?? 'N/A'))}"
                                    data-lot="${escapeHtml((t.lot ?? 'N/A'))}"
                                    data-status="${escapeHtml(statusLabel)}"
                                    data-date="${escapeHtml(dateValue)}">
                                    <i class="fa-solid fa-eye me-1"></i> Details
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');

                tableBody.innerHTML = html;
            }

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.btn-view');
                if (!btn) {
                    return;
                }

                document.getElementById('mName').textContent = btn.getAttribute('data-name') || '';
                document.getElementById('mEmail').textContent = btn.getAttribute('data-email') || '';
                document.getElementById('mPhone').textContent = btn.getAttribute('data-phone') || '';
                document.getElementById('mHouseType').textContent = btn.getAttribute('data-house-type') || '';
                document.getElementById('mBlockLot').textContent = `Block ${btn.getAttribute('data-block') || 'N/A'} / Lot ${btn.getAttribute('data-lot') || 'N/A'}`;
                document.getElementById('mStatus').textContent = btn.getAttribute('data-status') || '';
                document.getElementById('mDate').textContent = btn.getAttribute('data-date') || '';

                tenantModal.show();
            });
        });
    </script>
</body>
</html>
