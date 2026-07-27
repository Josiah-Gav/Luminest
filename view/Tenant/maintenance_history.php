<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/maintenance/tenant_maintenance_history_controller.php';
?>

<div class="container mt-4 mb-5">
    <h1 class="h3 fw-bold mt-3 mb-2 lm-title">Maintenance History</h1>
    <h6 class="text-secondary mb-0">Track the maintenance requests you have submitted.</h6>

    <table id="maintenance-history-table" class="table table-striped mt-4">
        <thead>
            <tr>
                <th scope="col">Request ID</th>
                <th scope="col">Property Address</th>
                <th scope="col">Title</th>
                <th scope="col">Category</th>
                <th scope="col">Priority</th>
                <th scope="col">Status</th>
                <th scope="col">Date Submitted</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($requests)): ?>
                <tr>
                    <td colspan="8" class="text-center">No maintenance requests found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><?php echo (int) $request['id']; ?></td>
                        <td><?php echo htmlspecialchars($request['property_address'] ?? 'No owned house'); ?></td>
                        <td><?php echo htmlspecialchars($request['title']); ?></td>
                        <td><?php echo htmlspecialchars($request['category']); ?></td>
                        <td><?php echo htmlspecialchars($request['priority']); ?></td>
                        <td><?php echo htmlspecialchars($request['status']); ?></td>
                        <td><?php echo htmlspecialchars($request['created_at']); ?></td>
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