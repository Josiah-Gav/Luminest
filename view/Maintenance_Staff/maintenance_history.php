<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/maintenance/maintenance_history_controller.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-accent-soft">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 fw-bold mb-2 text-accent-black">Maintenance history</h1>
                    <p class="text-muted mb-0">Completed requests are listed here so you can review resolved work and track past updates.</p>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
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
                                        <td colspan="8" class="text-center text-muted py-4">No completed maintenance requests found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($history_requests as $request): ?>
                                        <tr>
                                            <td><?php echo (int) $request['id']; ?></td>
                                            <td><?php echo htmlspecialchars($request['tenant_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($request['property_address'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($request['title'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($request['priority'] ?? 'Normal'); ?></td>
                                            <td><?php echo htmlspecialchars($request['completed_at'] ?? 'Not completed'); ?></td>
                                            <td><?php echo htmlspecialchars($request['resolved_at'] ?? 'Not resolved'); ?></td>
                                            <td><a href="maintenance_details.php?id=<?php echo (int) $request['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../layout/footer.php';
?>