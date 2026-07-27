<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/maintenance/maintenance_history_controller.php';
?>

<div class="container mt-4 mb-5">
	<h1 class="h3 fw-bold mt-3 mb-2 lm-title">Maintenance History</h1>
	<h6 class="text-secondary mb-0">Completed maintenance requests assigned to you.</h6>

	<table class="table table-striped mt-4">
		<thead>
			<tr>
				<th scope="col">Request ID</th>
				<th scope="col">Tenant Name</th>
				<th scope="col">Property Address</th>
				<th scope="col">Title</th>
				<th scope="col">Priority</th>
				<th scope="col">Completed At</th>
				<th scope="col">Resolved At</th>
				<th scope="col">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if (empty($history_requests)): ?>
				<tr>
					<td colspan="8" class="text-center">No completed maintenance requests found.</td>
				</tr>
			<?php else: ?>
				<?php foreach ($history_requests as $request): ?>
					<tr>
						<td><?php echo (int) $request['id']; ?></td>
						<td><?php echo htmlspecialchars($request['tenant_name'] ?? 'N/A'); ?></td>
						<td><?php echo htmlspecialchars($request['property_address'] ?? 'N/A'); ?></td>
						<td><?php echo htmlspecialchars($request['title']); ?></td>
						<td><?php echo htmlspecialchars($request['priority']); ?></td>
						<td><?php echo htmlspecialchars($request['completed_at'] ?? 'Not completed'); ?></td>
						<td><?php echo htmlspecialchars($request['resolved_at'] ?? 'Not resolved'); ?></td>
						<td><a href="maintenance_details.php?id=<?php echo (int) $request['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<?php
require_once '../layout/footer.php';
?>