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

$houseModel = new House();
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
        case 'paid':
            return 'text-bg-success';
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

    /* Payment Modal Styles */
    .pay-modal-header {
        background: linear-gradient(135deg, var(--lm-red), #8f0f18);
        color: #fff;
        border-bottom: none;
    }

    .pay-modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .pay-price {
        font-size: 2rem;
        font-weight: 800;
        color: var(--lm-red);
    }

    .pay-detail-label {
        color: #6c757d;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .pay-detail-value {
        font-size: 1.1rem;
        font-weight: 600;
    }

    .btn-lm-primary {
        background: linear-gradient(135deg, var(--lm-red), #8f0f18);
        border-color: var(--lm-red);
        color: #fff;
    }

    .btn-lm-primary:hover {
        color: #fff;
        background: linear-gradient(135deg, #a0101a, #6f0b12);
        border-color: #8f0f18;
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

                <div id="alertContainer"></div>

                <?php 
                $paidStatus = $_GET['paid'] ?? '';
                $paymentError = $_GET['error'] ?? '';
                ?>
                <?php if ($cancelStatus === '1'): ?>
                    <div class="alert alert-success" role="alert">
                        Pending reservation cancelled. The house is available again.
                    </div>
                <?php elseif ($paidStatus === '1'): ?>
                    <div class="alert alert-success" role="alert">
                        Payment successful! The house is now yours. Welcome to your new home.
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
                <?php elseif ($errorCode === 'payment_not_allowed'): ?>
                    <div class="alert alert-warning" role="alert">
                        This reservation is not eligible for payment.
                    </div>
                <?php elseif ($errorCode === 'payment_failed'): ?>
                    <div class="alert alert-danger" role="alert">
                        Unable to process payment right now. Please try again.
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
                                                <form class="m-0 cancel-reservation-form" method="POST" action="../../controllers/auth/reservation_controller.php">
                                                    <input type="hidden" name="ajax" value="1">
                                                    <input type="hidden" name="cancel_reservation" value="1">
                                                    <input type="hidden" name="reservation_id" value="<?php echo (int)$reservation['reservation_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Cancel
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-secondary">-</span>
                                            <?php endif; ?>
                                            <?php if ($status === 'accepted'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success pay-btn" 
                                                    data-reservation-id="<?php echo (int)$reservation['reservation_id']; ?>"
                                                    data-house="<?php echo htmlspecialchars($houseLabel, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-block="<?php echo (int)$reservation['block']; ?>"
                                                    data-lot="<?php echo (int)$reservation['lot']; ?>"
                                                    data-house-type="<?php echo htmlspecialchars($houseType['db_type'] ?? $reservation['house_type'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    Pay
                                                </button>
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

<!-- Payment Confirmation Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header pay-modal-header border-0 py-3">
                <h5 class="modal-title fw-bold" id="paymentModalLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-credit-card me-2" viewBox="0 0 16 16">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                        <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"/>
                    </svg>
                    Payment Confirmation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <p class="pay-detail-label mb-1">Total Amount Due</p>
                    <div class="pay-price">₱<span id="modalPrice">0</span></div>
                </div>

                <div class="bg-light rounded-3 p-3 mb-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="pay-detail-label">House</div>
                            <div class="pay-detail-value" id="modalHouse">-</div>
                        </div>
                        <div class="col-3">
                            <div class="pay-detail-label">Block</div>
                            <div class="pay-detail-value" id="modalBlock">-</div>
                        </div>
                        <div class="col-3">
                            <div class="pay-detail-label">Lot</div>
                            <div class="pay-detail-value" id="modalLot">-</div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info border-0 rounded-3 small mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle me-1" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                    </svg>
                    By proceeding with payment, you confirm that you would like to purchase this property.
                </div>
            </div>
            <div class="modal-footer border-0 bg-light p-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <form id="payForm" method="POST" action="../../controllers/auth/reservation_controller.php">
                    <input type="hidden" name="ajax" value="1">
                    <input type="hidden" name="pay_reservation" value="1">
                    <input type="hidden" name="reservation_id" id="payReservationId" value="">
                    <button type="submit" class="btn btn-lm-primary px-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle me-1" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                        </svg>
                        Pay Now
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    function showAlert(message, type) {
        $('#alertContainer').html('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    }

    $('.cancel-reservation-form').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            success: function (response) {
                if (response && response.success) {
                    showAlert(response.message, 'success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 700);
                } else {
                    showAlert((response && response.message) || 'Unable to cancel reservation.', 'danger');
                }
            },
            error: function () {
                showAlert('Unable to cancel reservation right now.', 'danger');
            }
        });
    });

    // Payment modal - handle Pay button click
    $('.pay-btn').on('click', function () {
        var $btn = $(this);
        var reservationId = $btn.data('reservation-id');
        var house = $btn.data('house');
        var block = $btn.data('block');
        var lot = $btn.data('lot');
        var houseType = $btn.data('house-type');

        // Get the price from the house type data
        // We pass the house types as a JSON object embedded in the page
        var houseTypes = window.housePrices || {};
        var price = houseTypes[houseType] || '0';

        $('#payReservationId').val(reservationId);
        $('#modalHouse').text(house);
        $('#modalBlock').text(block);
        $('#modalLot').text(lot);
        $('#modalPrice').text(price);

        var modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
    });

    // Handle pay form submission
    $('#payForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                var modalEl = document.getElementById('paymentModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }

                if (response && response.success) {
                    showAlert(response.message, 'success');
                    setTimeout(function () {
                        window.location.reload();
                    }, 1200);
                } else {
                    showAlert((response && response.message) || 'Unable to process payment right now.', 'danger');
                }
            },
            error: function () {
                var modalEl = document.getElementById('paymentModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
                showAlert('Unable to process payment right now.', 'danger');
            }
        });
    });
});
</script>

<script>
// House price data passed from PHP to JavaScript
window.housePrices = <?php echo json_encode(
    array_reduce($houseModel->all(), function ($carry, $h) {
        $carry[$h['db_type']] = $h['price'] ?? '0';
        return $carry;
    }, [])
); ?>;
</script>


