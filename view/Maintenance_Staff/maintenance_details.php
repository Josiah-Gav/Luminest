<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/maintenance/maintenance_details_controller.php';
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
					<div class="col-md-6"><strong>Tenant:</strong> <?php echo htmlspecialchars($request['tenant_name'] ?? 'N/A'); ?></div>
					<div class="col-md-6"><strong>Property:</strong> <?php echo htmlspecialchars($request['property_address'] ?? 'N/A'); ?></div>
					<div class="col-md-6"><strong>Category:</strong> <?php echo htmlspecialchars($request['category']); ?></div>
					<div class="col-md-6"><strong>Priority:</strong> <?php echo htmlspecialchars($request['priority']); ?></div>
					<div class="col-md-6"><strong>Status:</strong> <?php echo htmlspecialchars($request['status']); ?></div>
					<div class="col-md-6"><strong>Created:</strong> <?php echo htmlspecialchars($request['created_at']); ?></div>
					<div class="col-12"><strong>Title:</strong> <?php echo htmlspecialchars($request['title']); ?></div>
					<div class="col-12"><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($request['description'])); ?></div>
					<div class="col-12"><strong>Marked Done At:</strong> <?php echo htmlspecialchars($request['resolved_at'] ?? 'Not yet marked done'); ?></div>
					<div class="col-12"><strong>Tenant Completed At:</strong> <?php echo htmlspecialchars($request['completed_at'] ?? 'Not yet completed'); ?></div>
				</div>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<h3 class="h6 fw-bold mb-3">Update Request</h3>
				<form method="post">
					<div class="mb-3">
						<label for="status" class="form-label">Status</label>
						<select id="status" name="status" class="form-select" required>
							<?php
							$statuses = ['in-progress', 'resolved'];
							foreach ($statuses as $status_option):
							?>
								<option value="<?php echo $status_option; ?>" <?php echo ($request['status'] === $status_option) ? 'selected' : ''; ?>>
									<?php echo htmlspecialchars(ucfirst($status_option)); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="mb-3">
						<label for="resolution_notes" class="form-label">Resolution Notes</label>
						<textarea id="resolution_notes" name="resolution_notes" class="form-control" rows="4"><?php echo htmlspecialchars($request['resolution_notes'] ?? ''); ?></textarea>
					</div>

					<button type="submit" name="update_request" class="btn btn-primary">Save Changes</button>
					<a href="maintenance_requests.php" class="btn btn-outline-secondary">Back to Requests</a>
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>


<?php
require_once '../layout/footer.php';
?>