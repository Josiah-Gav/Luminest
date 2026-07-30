<?php
require_once '../../controllers/maintenance/tenant_maintenance_details_controller.php';
require_once '../layout/header.php';
require_once '../layout/navbar.php';
?>

<div class="container py-4">
	<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
		<div>
			<h1 class="h3 fw-bold mb-1 text-accent-black">Maintenance Details</h1>
			<p class="text-muted mb-0">Review request details and follow up on progress.</p>
		</div>
		<a href="maintenance_history.php" class="btn btn-outline-secondary">Back to History</a>
	</div>

	<?php if (isset($_SESSION['flash_message'])): ?>
		<div class="alert alert-<?php echo htmlspecialchars($_SESSION['flash_type'] ?? 'info'); ?>">
			<?php echo htmlspecialchars($_SESSION['flash_message']); ?>
		</div>
		<?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
	<?php endif; ?>

	<?php if ($request): ?>
		<div class="border rounded-3 p-4 bg-white mb-4">
				<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
					<h2 class="h5 mb-0">Request #<?php echo (int) $request['id']; ?></h2>
					<span class="badge text-bg-light border"><?php echo htmlspecialchars($request['status']); ?></span>
				</div>
				<div class="row g-3">
					<div class="col-md-6"><strong>Property:</strong> <?php echo htmlspecialchars($request['property_address'] ?? 'No owned house'); ?></div>
					<div class="col-md-6"><strong>Assigned Staff:</strong> <?php echo htmlspecialchars($request['assigned_staff_name'] ?? 'Not assigned yet'); ?></div>
					<div class="col-md-6"><strong>Category:</strong> <span class="badge text-bg-light"><?php echo htmlspecialchars($request['category']); ?></span></div>
					<div class="col-md-6"><strong>Priority:</strong> <span class="badge text-bg-warning text-dark"><?php echo htmlspecialchars($request['priority']); ?></span></div>
					<div class="col-md-6"><strong>Status:</strong> <span class="badge text-bg-secondary"><?php echo htmlspecialchars($request['status']); ?></span></div>
					<div class="col-md-6"><strong>Submitted At:</strong> <?php echo htmlspecialchars($request['created_at']); ?></div>
					<div class="col-md-6"><strong>Completion Date:</strong> <?php echo htmlspecialchars($request['completed_at'] ?? 'Not yet completed'); ?></div>
					<div class="col-md-6"><strong>Resolved Date:</strong> <?php echo htmlspecialchars($request['resolved_at'] ?? 'Not yet resolved'); ?></div>
					<div class="col-12"><strong>Title:</strong> <?php echo htmlspecialchars($request['title']); ?></div>
					<div class="col-12"><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($request['description'])); ?></div>
					<div class="col-12"><strong>Resolution Notes:</strong><br><?php echo nl2br(htmlspecialchars($request['resolution_notes'] ?? 'No resolution notes yet.')); ?></div>
				</div>
			</div>
		</div>

		<?php if (($request['status'] ?? '') === 'resolved'): ?>
			<div class="border rounded-3 p-4 bg-white">
				<h3 class="h6 fw-bold mb-3">Resolution Status</h3>
				<p class="text-muted mb-0">This maintenance request is already resolved and cannot be changed.</p>
			</div>
		<?php elseif (($request['status'] ?? '') === 'completed'): ?>
			<div class="border rounded-3 p-4 bg-white">
					<h3 class="h6 fw-bold mb-3">Confirm Resolution</h3>
					<p class="text-secondary mb-3">If the maintenance work is finished to your satisfaction, mark this request as resolved.</p>
					<form method="post" action="../../controllers/maintenance/tenant_maintenance_details_controller.php?id=<?php echo (int) $request['id']; ?>">
						<button type="submit" name="mark_resolved" class="btn btn-success">Mark as Resolved</button>
					</form>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>

<?php
require_once '../layout/footer.php';
?>
