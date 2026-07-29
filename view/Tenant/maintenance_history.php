<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/maintenance/tenant_maintenance_history_controller.php';
$requestCount = count($requests ?? []);
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-accent-black">Maintenance History</h1>
            <p class="text-muted mb-0">Track the maintenance requests you have submitted.</p>
        </div>
        <a href="maintenance_request.php" class="btn btn-primary">New Request</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-accent-soft">
                <div class="card-body p-4">
                    <div class="text-muted small">Total Submitted</div>
                    <div class="h4 fw-bold text-accent-black mb-0"><?= (int) $requestCount ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-muted small">Status note</div>
                    <div class="fw-semibold text-accent-red">Updates appear here as soon as the maintenance team responds.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="maintenance-history-table" class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
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
                                <td colspan="8" class="text-center py-4 text-muted">No maintenance requests found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td>#<?php echo (int) $request['id']; ?></td>
                                    <td><?php echo htmlspecialchars($request['property_address'] ?? 'No owned house'); ?></td>
                                    <td><?php echo htmlspecialchars($request['title']); ?></td>
                                    <td><span class="badge text-bg-light"><?php echo htmlspecialchars($request['category']); ?></span></td>
                                    <td><span class="badge text-bg-warning text-dark"><?php echo htmlspecialchars($request['priority']); ?></span></td>
                                    <td><span class="badge text-bg-secondary"><?php echo htmlspecialchars($request['status']); ?></span></td>
                                    <td><?php echo htmlspecialchars($request['created_at']); ?></td>
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

<?php
require_once '../layout/footer.php';
?>