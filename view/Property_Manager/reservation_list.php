<?php
session_start();

// Adjust database connection path if needed
require_once __DIR__ . '/../../database/db.php'; 

// --- AJAX POST HANDLER: Update Reservation Status & User Role ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    $reservation_id = filter_input(INPUT_POST, 'reservation_id', FILTER_VALIDATE_INT);
    $new_status_raw = trim((string)($_POST['status'] ?? ''));
    $new_status     = strtolower($new_status_raw);

    $allowed_statuses = ['pending', 'accepted', 'rejected', 'paid', 'cancelled', 'completed'];

    if ($reservation_id && in_array($new_status, $allowed_statuses, true)) {
        try {
            $pdo->beginTransaction();

            $reservationStmt = $pdo->prepare("SELECT reservation_id, user_id, house_type, block, lot FROM house_reservations WHERE reservation_id = :id LIMIT 1");
            $reservationStmt->execute([':id' => $reservation_id]);
            $reservation = $reservationStmt->fetch(PDO::FETCH_ASSOC);

            if (!$reservation) {
                throw new RuntimeException('Reservation not found.');
            }

            // 1. Update reservation status using lowercase values that match the enum.
            $stmt = $pdo->prepare("UPDATE house_reservations SET status = :status WHERE reservation_id = :id");
            $stmt->execute([':status' => $new_status, ':id' => $reservation_id]);

            // 2. Keep house inventory state in sync with reservation state.
            if (in_array($new_status, ['accepted', 'paid'], true)) {
                $houseStatus = $new_status === 'paid' ? 'sold' : 'reserved';
                $updateHouseStmt = $pdo->prepare(" 
                    UPDATE house
                    SET status = :house_status,
                        owner_id = :owner_id
                    WHERE house_type = :house_type
                      AND block = :block
                      AND lot = :lot
                    LIMIT 1
                ");
                $updateHouseStmt->execute([
                    ':house_status' => $houseStatus,
                    ':owner_id' => $reservation['user_id'],
                    ':house_type' => $reservation['house_type'],
                    ':block' => $reservation['block'],
                    ':lot' => $reservation['lot'],
                ]);
            }

            // 3. If status is ACCEPTED or PAID, update user's role to Tenant.
            if (in_array($new_status, ['accepted', 'paid'], true)) {
                $userRoleStmt = $pdo->prepare(" 
                    UPDATE users
                    SET role = 'Tenant'
                    WHERE user_id = :user_id
                      AND LOWER(role) = 'prospect'
                ");
                $userRoleStmt->execute([':user_id' => $reservation['user_id']]);
            }

            $pdo->commit();

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => "Reservation #{$reservation_id} updated to " . strtoupper($new_status) . "." . (in_array($new_status, ['accepted', 'paid'], true) ? " User updated to Tenant." : ""),
                    'new_status' => strtoupper($new_status)
                ]);
                exit();
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit();
            }
        }
    } else {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => "Invalid input data."]);
            exit();
        }
    }
}

// --- FETCH RESERVATIONS ---
$query = "
    SELECT 
        r.reservation_id,
        r.house_type,
        r.block,
        r.lot,
        r.status,
        r.created_at,
        u.full_name,
        u.email,
        u.phone_number,
        u.role
    FROM house_reservations r
    LEFT JOIN users u ON r.user_id = u.user_id
    ORDER BY r.created_at DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusBadgeHtml($status) {
    switch (strtolower((string)$status)) {
        case 'accepted': return '<span class="badge bg-success">Accepted</span>';
        case 'paid':     return '<span class="badge bg-info text-dark">Paid</span>';
        case 'pending':  return '<span class="badge bg-warning text-dark">Pending</span>';
        case 'rejected': return '<span class="badge bg-danger">Rejected</span>';
        case 'cancelled': return '<span class="badge bg-secondary">Cancelled</span>';
        case 'completed': return '<span class="badge bg-primary">Completed</span>';
        default:         return '<span class="badge bg-secondary">' . htmlspecialchars((string)$status) . '</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Management - Luminest</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    
    <div id="alertContainer"></div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">House Reservations</h2>
            <p class="text-muted mb-0">Manage property bookings and update reservation statuses</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>
    </div>

    <!-- Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th># ID</th>
                            <th>Guest Name</th>
                            <th>House Details</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reservations)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No reservations found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reservations as $row): ?>
                                <tr id="row-<?= $row['reservation_id']; ?>">
                                    <td class="fw-bold">#<?= htmlspecialchars($row['reservation_id']); ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($row['full_name'] ?? 'N/A'); ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['email'] ?? 'No Email'); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($row['house_type'] ?? 'N/A'); ?></div>
                                        <small class="text-muted">Block <?= htmlspecialchars($row['block']); ?>, Lot <?= htmlspecialchars($row['lot']); ?></small>
                                    </td>
                                    <td><?= !empty($row['created_at']) ? date('M d, Y g:i A', strtotime($row['created_at'])) : 'N/A'; ?></td>
                                    <td id="status-cell-<?= $row['reservation_id']; ?>">
                                        <?= getStatusBadgeHtml($row['status']); ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#updateModal<?= $row['reservation_id']; ?>">
                                            <i class="bi bi-pencil-square"></i> Update
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

<!-- Modals rendered safely outside the table -->
<?php if (!empty($reservations)): ?>
    <?php foreach ($reservations as $row): ?>
        <div class="modal fade" id="updateModal<?= $row['reservation_id']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form class="ajax-status-form" data-reservation-id="<?= $row['reservation_id']; ?>">
                        <div class="modal-header">
                            <h5 class="modal-title">Update Reservation #<?= $row['reservation_id']; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-start">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="reservation_id" value="<?= $row['reservation_id']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Guest</label>
                                <p class="mb-0"><?= htmlspecialchars($row['full_name'] ?? 'N/A'); ?> (<?= htmlspecialchars($row['email'] ?? 'No email'); ?>)</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Property Location</label>
                                <p class="mb-0"><?= htmlspecialchars($row['house_type'] ?? 'N/A'); ?> - Block <?= htmlspecialchars($row['block']); ?>, Lot <?= htmlspecialchars($row['lot']); ?></p>
                            </div>

                            <div class="mb-3">
                                <label for="statusSelect<?= $row['reservation_id']; ?>" class="form-label fw-bold">Select Status</label>
                                <select name="status" id="statusSelect<?= $row['reservation_id']; ?>" class="form-select" required>
                                    <option value="pending" <?= strtolower((string)$row['status']) === 'pending' ? 'selected' : ''; ?>>PENDING</option>
                                    <option value="accepted" <?= strtolower((string)$row['status']) === 'accepted' ? 'selected' : ''; ?>>ACCEPTED</option>
                                    <option value="paid" <?= strtolower((string)$row['status']) === 'paid' ? 'selected' : ''; ?>>PAID</option>
                                    <option value="rejected" <?= strtolower((string)$row['status']) === 'rejected' ? 'selected' : ''; ?>>REJECTED</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary submit-btn">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    function getStatusBadgeHtml(status) {
        switch (String(status || '').toLowerCase()) {
            case 'accepted': return '<span class="badge bg-success">Accepted</span>';
            case 'paid':     return '<span class="badge bg-info text-dark">Paid</span>';
            case 'pending':  return '<span class="badge bg-warning text-dark">Pending</span>';
            case 'rejected': return '<span class="badge bg-danger">Rejected</span>';
            case 'cancelled': return '<span class="badge bg-secondary">Cancelled</span>';
            case 'completed': return '<span class="badge bg-primary">Completed</span>';
            default:         return `<span class="badge bg-secondary">${status}</span>`;
        }
    }

    function showAlert(message, type = 'success') {
        const container = document.getElementById('alertContainer');
        container.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }

    document.querySelectorAll('.ajax-status-form').forEach(form => {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const reservationId = this.getAttribute('data-reservation-id');
            const submitBtn = this.querySelector('.submit-btn');
            const formData = new FormData(this);

            submitBtn.disabled = true;

            try {
                const response = await fetch('reservation_list.php', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    const statusCell = document.getElementById(`status-cell-${reservationId}`);
                    if (statusCell) {
                        statusCell.innerHTML = getStatusBadgeHtml(result.new_status);
                    }

                    const modalElem = document.getElementById(`updateModal${reservationId}`);
                    const modalInstance = bootstrap.Modal.getInstance(modalElem);
                    if (modalInstance) {
                        modalInstance.hide();
                    }

                    showAlert(result.message, 'success');
                } else {
                    showAlert(result.message || 'An error occurred.', 'danger');
                }
            } catch (error) {
                console.error('AJAX Error:', error);
                showAlert('Failed to process the request.', 'danger');
            } finally {
                submitBtn.disabled = false;
            }
        });
    });
});
</script>
</body>
</html>