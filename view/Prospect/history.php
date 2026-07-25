<?php
require_once '../layout/header.php';
require_once '../../models/House.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$houseModel = new House($db->getConnection());
$reservations = [];
$cancelStatus = $_GET['cancelled'] ?? '';
$errorCode = $_GET['error'] ?? '';

try {
    $stmt = $db->getConnection()->prepare(
        'SELECT reservation_id, house_type, block, lot, status, created_at
         FROM house_reservations
         WHERE user_id = :user_id
         ORDER BY created_at DESC, reservation_id DESC'
    );
    $stmt->execute([
        ':user_id' => (int)$_SESSION['user_id'],
    ]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $reservations = [];
}

function reservationStatusBadgeClass(string $status): string
{
    switch ($status) {
        case 'pending':
            return 'text-bg-warning';
        case 'accepted':
            return 'text-bg-primary';
        case 'completed':
            return 'text-bg-success';
        case 'rejected':
            return 'text-bg-danger';
        case 'cancelled':
            return 'text-bg-secondary';
        default:
            return 'text-bg-light';
    }
}
?>

<style>
    :root {
        --lm-red: #c1121f;
        --lm-blue: #1d4ed8;
        --lm-soft: #eff6ff;
        --lm-ink: #111827;
    }

    body {
        background:
            radial-gradient(circle at top left, rgba(29, 78, 216, 0.12), transparent 32%),
            radial-gradient(circle at top right, rgba(193, 18, 31, 0.1), transparent 28%),
            linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        color: var(--lm-ink);
    }

    .history-shell {
        border: 1px solid #dbeafe;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
    }

    .history-chip {
        background: var(--lm-soft);
        color: var(--lm-blue);
        border: 1px solid #bfdbfe;
    }
</style>

<main class="py-4 py-lg-5">
    <div class="container">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="dashboard.php" class="btn btn-link text-decoration-none ps-0 fw-semibold">&larr; Back to Prospect Dashboard</a>
            <a href="reservation.php" class="btn btn-outline-secondary btn-sm">Create Reservation</a>
        </div>

        <section class="card history-shell rounded-4">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <span class="badge rounded-pill history-chip px-3 py-2">Reservation History</span>
                        <h1 class="h3 mt-3 mb-1">Your Reservations</h1>
                        <p class="text-secondary mb-0">Track current and previous reservation requests.</p>
                    </div>
                    <span class="badge text-bg-light border px-3 py-2"><?php echo count($reservations); ?> total</span>
                </div>

                <?php if ($cancelStatus === '1'): ?>
                    <div class="alert alert-success" role="alert">
                        Pending reservation cancelled. The house is available again.
                    </div>
                <?php elseif ($errorCode === 'cancel_not_allowed'): ?>
                    <div class="alert alert-warning" role="alert">
                        This reservation can no longer be cancelled.
                    </div>
                <?php elseif ($errorCode === 'cancel_failed'): ?>
                    <div class="alert alert-danger" role="alert">
                        Unable to cancel reservation right now. Please try again.
                    </div>
                <?php elseif ($errorCode === 'invalid_cancel'): ?>
                    <div class="alert alert-warning" role="alert">
                        Invalid cancellation request.
                    </div>
                <?php endif; ?>

                <?php if (empty($reservations)): ?>
                    <div class="alert alert-light border" role="alert">
                        You do not have any reservations yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Reservation #</th>
                                    <th scope="col">House</th>
                                    <th scope="col">Block</th>
                                    <th scope="col">Lot</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservations as $reservation): ?>
                                    <?php
                                        $houseType = $houseModel->getByDbType($reservation['house_type']);
                                        $houseLabel = $houseType['title'] ?? $reservation['house_type'];
                                        $status = (string)$reservation['status'];
                                    ?>
                                    <tr>
                                        <td><?php echo (int)$reservation['reservation_id']; ?></td>
                                        <td><?php echo htmlspecialchars($houseLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo (int)$reservation['block']; ?></td>
                                        <td><?php echo (int)$reservation['lot']; ?></td>
                                        <td><span class="badge <?php echo reservationStatusBadgeClass($status); ?>"><?php echo htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td><?php echo htmlspecialchars($reservation['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php if ($status === 'pending'): ?>
                                                <form method="POST" action="../../controllers/auth/reservation_controller.php" class="m-0">
                                                    <input type="hidden" name="cancel_reservation" value="1">
                                                    <input type="hidden" name="reservation_id" value="<?php echo (int)$reservation['reservation_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this pending reservation?');">
                                                        Cancel
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-secondary">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<?php require_once '../layout/footer.php'; ?>