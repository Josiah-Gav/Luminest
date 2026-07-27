<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/maintenance/tenant_maintenance_details_controller.php';
?>

<div class="container mt-4 mb-5">
	<h1 class="h3 fw-bold mb-3">Maintenance Details</h1>

	<?php if ($success !== ''): ?>
		<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
	<?php endif; ?>

	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
	<?php endif; ?>

	<?php if ($request): ?>
		<div class="card mb-4">
			<div class="card-body">
				<h2 class="h5 mb-3">Request #<?php echo (int) $request['id']; ?></h2>
				<div class="row g-2">
					<div class="col-md-6"><strong>Property:</strong> <?php echo htmlspecialchars($request['property_address'] ?? 'No owned house'); ?></div>
					<div class="col-md-6"><strong>Category:</strong> <?php echo htmlspecialchars($request['category']); ?></div>
					<div class="col-md-6"><strong>Priority:</strong> <?php echo htmlspecialchars($request['priority']); ?></div>
					<div class="col-md-6"><strong>Status:</strong> <?php echo htmlspecialchars($request['status']); ?></div>
					<div class="col-md-6"><strong>Submitted At:</strong> <?php echo htmlspecialchars($request['created_at']); ?></div>
					<div class="col-md-6"><strong>Completed At:</strong> <?php echo htmlspecialchars($request['completed_at'] ?? 'Not yet completed'); ?></div>
					<div class="col-md-6"><strong>Resolved At:</strong> <?php echo htmlspecialchars($request['resolved_at'] ?? 'Not yet resolved'); ?></div>
					<div class="col-12"><strong>Title:</strong> <?php echo htmlspecialchars($request['title']); ?></div>
					<div class="col-12"><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($request['description'])); ?></div>
					<div class="col-12"><strong>Resolution Notes:</strong><br><?php echo nl2br(htmlspecialchars($request['resolution_notes'] ?? 'No resolution notes yet.')); ?></div>
				</div>
			</div>
		</div>

		<?php if ($request['status'] === 'completed'): ?>
			<div class="card">
				<div class="card-body">
					<h3 class="h6 fw-bold mb-3">Confirm Resolution</h3>
					<p class="text-secondary mb-3">If the maintenance work is finished to your satisfaction, mark this request as resolved.</p>
					<form method="post">
						<button type="submit" name="mark_resolved" class="btn btn-success">Mark as Resolved</button>
						<a href="maintenance_history.php" class="btn btn-outline-secondary">Back to History</a>
					</form>
				</div>
			</div>
		<?php else: ?>
			<div class="d-flex gap-2">
				<a href="maintenance_history.php" class="btn btn-outline-secondary">Back to History</a>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>

<?php
require_once '../layout/footer.php';
?>
