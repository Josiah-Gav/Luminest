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

$selectedHouseKey = $_GET['house'] ?? House::DEFAULT_SLUG;
$selectedHouse = $houseModel->getBySlug($selectedHouseKey);
$selectedHouseKey = $selectedHouse['slug'];
$selectedDbType = $selectedHouse['db_type'];
$selectedBlock = isset($_GET['block']) ? (int)$_GET['block'] : 0;
$selectedLot = isset($_GET['lot']) ? (int)$_GET['lot'] : 0;
$saveStatus = $_GET['saved'] ?? '';
$errorCode = $_GET['error'] ?? '';

$blocks = [];
$lots = [];

try {
	$blocks = $houseModel->getAvailableBlocks($selectedDbType);

	if (!in_array($selectedBlock, $blocks, true)) {
		$selectedBlock = !empty($blocks) ? $blocks[0] : 0;
	}

	if ($selectedBlock > 0) {
		$lots = $houseModel->getAvailableLots($selectedDbType, $selectedBlock);
	}

	if (!in_array($selectedLot, $lots, true)) {
		$selectedLot = !empty($lots) ? $lots[0] : 0;
	}
} catch (Throwable $e) {
	$blocks = [];
	$lots = [];
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
			radial-gradient(circle at 10% 10%, rgba(29, 78, 216, 0.12), transparent 32%),
			radial-gradient(circle at 90% 10%, rgba(193, 18, 31, 0.12), transparent 30%),
			linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
		color: var(--lm-ink);
	}

	.reservation-shell {
		border: 1px solid #dbeafe;
		box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
		background: rgba(255, 255, 255, 0.92);
	}

	.reservation-chip {
		background: var(--lm-soft);
		color: var(--lm-blue);
		border: 1px solid #bfdbfe;
	}

	.reservation-image {
		object-fit: cover;
		height: 100%;
		min-height: 280px;
	}

	.btn-lm-primary {
		background: linear-gradient(135deg, var(--lm-red), #8f0f18);
		border-color: var(--lm-red);
		color: #fff;
	}

	.btn-lm-primary:hover {
		color: #fff;
	}
</style>

<main class="py-4 py-lg-5">
	<div class="container">
		<div class="d-flex flex-wrap gap-2 mb-3">
			<a href="dashboard.php" class="btn btn-link text-decoration-none ps-0 fw-semibold">&larr; Back to Prospect Dashboard</a>
			<a href="history.php" class="btn btn-outline-secondary btn-sm">View Reservation History</a>
		</div>

		<section class="card reservation-shell rounded-4 overflow-hidden">
			<div class="row g-0">
				<div class="col-lg-5">
					<img src="<?php echo htmlspecialchars($selectedHouse['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($selectedHouse['title'], ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid w-100 reservation-image">
				</div>

				<div class="col-lg-7">
					<div class="card-body p-4 p-lg-5">
						<span class="badge rounded-pill reservation-chip px-3 py-2 mb-3">Reservation Form</span>
						<h1 class="h2 fw-bold mb-2">Reserve <?php echo htmlspecialchars($selectedHouse['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
						<p class="text-secondary mb-4">Choose an available block and lot for your selected house type. WE ADVICE ON VISITING US PERSONALLY TO VIEW THE HOUSE.</p>

						<?php if ($saveStatus === '1'): ?>
							<div class="alert alert-success" role="alert">
								Reservation saved successfully. The selected house is now marked as reserved.
							</div>
						<?php elseif ($errorCode === 'not_available'): ?>
							<div class="alert alert-warning" role="alert">
								That house is no longer available. Please choose a different block and lot.
							</div>
						<?php elseif ($errorCode === 'invalid_selection'): ?>
							<div class="alert alert-warning" role="alert">
								Please select a valid block and lot before reserving.
							</div>
						<?php elseif ($errorCode === 'save_failed'): ?>
							<div class="alert alert-danger" role="alert">
								Unable to save reservation right now. Please try again.
							</div>
						<?php elseif ($errorCode === 'has_pending'): ?>
							<div class="alert alert-warning" role="alert">
								You already have a pending reservation. Please wait for review before creating another one.
							</div>
						<?php endif; ?>

						<form method="GET" action="reservation.php" class="row g-3">
							<input type="hidden" name="house" value="<?php echo htmlspecialchars($selectedHouseKey, ENT_QUOTES, 'UTF-8'); ?>">

							<div class="col-12 col-md-6">
								<label for="block" class="form-label fw-semibold">Block</label>
								<select class="form-select" id="block" name="block" onchange="this.form.submit()" <?php echo empty($blocks) ? 'disabled' : ''; ?>>
									<?php if (empty($blocks)): ?>
										<option value="">No available blocks</option>
									<?php else: ?>
										<?php foreach ($blocks as $block): ?>
											<option value="<?php echo (int)$block; ?>" <?php echo $selectedBlock === (int)$block ? 'selected' : ''; ?>>
												Block <?php echo (int)$block; ?>
											</option>
										<?php endforeach; ?>
									<?php endif; ?>
								</select>
							</div>

							<div class="col-12 col-md-6">
								<label for="lot" class="form-label fw-semibold">Lot</label>
								<select class="form-select" id="lot" name="lot" <?php echo empty($lots) ? 'disabled' : ''; ?>>
									<?php if (empty($lots)): ?>
										<option value="">No available lots</option>
									<?php else: ?>
										<?php foreach ($lots as $lot): ?>
											<option value="<?php echo (int)$lot; ?>" <?php echo $selectedLot === (int)$lot ? 'selected' : ''; ?>>
												Lot <?php echo (int)$lot; ?>
											</option>
										<?php endforeach; ?>
									<?php endif; ?>
								</select>
							</div>

							<div class="col-12 d-flex flex-wrap gap-2 align-items-center mt-2">
								<button type="submit" class="btn btn-lm-primary">Select This Unit</button>
								<?php if ($selectedBlock > 0 && $selectedLot > 0): ?>
									<span class="badge text-bg-light border">Selected: Block <?php echo (int)$selectedBlock; ?>, Lot <?php echo (int)$selectedLot; ?></span>
								<?php endif; ?>
							</div>
						</form>

<div id="alertContainer" class="mt-3"></div>
					<form id="reservationForm" method="POST" action="../../controllers/auth/reservation_controller.php" class="mt-3">
						<input type="hidden" name="ajax" value="1">
							<input type="hidden" name="house" value="<?php echo htmlspecialchars($selectedHouseKey, ENT_QUOTES, 'UTF-8'); ?>">
							<input type="hidden" name="block" value="<?php echo (int)$selectedBlock; ?>">
							<input type="hidden" name="lot" value="<?php echo (int)$selectedLot; ?>">
							<input type="hidden" name="reserve_house" value="1">
							<button type="submit" class="btn btn-success" <?php echo ($selectedBlock <= 0 || $selectedLot <= 0) ? 'disabled' : ''; ?>>
								Confirm Reservation
							</button>
						</form>

						<hr class="my-4">

						<div class="small text-secondary">
							House type in database: <strong><?php echo htmlspecialchars($selectedDbType, ENT_QUOTES, 'UTF-8'); ?></strong><br>
							Available blocks: <strong><?php echo count($blocks); ?></strong><br>
							Available lots in selected block: <strong><?php echo count($lots); ?></strong>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</main>

<script>
$(document).ready(function () {
    function showAlert(message, type) {
        $('#alertContainer').html('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    }

    $('#reservationForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response && response.success) {
                    showAlert(response.message, 'success');
                    window.location.href = response.redirect || 'reservation.php?house=' + encodeURIComponent('<?php echo htmlspecialchars($selectedHouseKey, ENT_QUOTES, 'UTF-8'); ?>');
                } else {
                    showAlert((response && response.message) || 'Unable to reserve the unit right now.', 'danger');
                }
            },
            error: function () {
                showAlert('Unable to reserve the unit right now.', 'danger');
            }
        });
    });
});
</script>

<?php require_once '../layout/footer.php'; ?>

