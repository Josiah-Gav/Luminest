<?php
require_once '../../controllers/maintenance/maintenance_details_controller.php';
require_once '../layout/header.php';
require_once '../layout/navbar.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-accent-soft">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 fw-bold mb-2 text-accent-black">Maintenance details</h1>
                    <p class="text-muted mb-0">Review issue information and update the current status from this screen.</p>
                </div>
            </div>
        </div>

        <?php if ($success !== ''): ?>
            <div class="col-12">
                <div class="alert alert-success mb-0"><?php echo htmlspecialchars($success); ?></div>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="col-12">
                <div class="alert alert-danger mb-0"><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <?php if ($request): ?>
            <div class="col-12">
                <div class="border rounded-3 p-4 bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
                            <div>
                                <h2 class="h5 fw-bold mb-1 text-accent-black">Request #<?php echo (int) $request['id']; ?></h2>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($request['title'] ?? 'Maintenance Request'); ?></p>
                            </div>
                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis"><?php echo htmlspecialchars($request['status'] ?? 'Pending'); ?></span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="small text-muted">Tenant</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($request['tenant_name'] ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="small text-muted">Property</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($request['property_address'] ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="small text-muted">Category</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($request['category'] ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="small text-muted">Priority</div>
                                    <div class="fw-semibold text-accent-red"><?php echo htmlspecialchars($request['priority'] ?? 'Normal'); ?></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="small text-muted">Description</div>
                                    <div class="fw-semibold"><?php echo nl2br(htmlspecialchars($request['description'] ?? 'No description provided.')); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="small text-muted">Created</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($request['created_at'] ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="small text-muted">Completion Date</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($request['completed_at'] ?? 'Not completed yet'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="small text-muted">Resolved Date</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($request['resolved_at'] ?? 'Not resolved yet'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="border rounded-3 p-4 bg-white">
                    <h3 class="h6 fw-bold mb-3 text-accent-blue">Update request</h3>
                        <div id="alertContainer"></div>
                        <form method="post" action="maintenance_details.php?id=<?php echo (int) $request['id']; ?>">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-select" required>
                                    <?php
                                    $statuses = ['in-progress', 'completed'];
                                    foreach ($statuses as $status_option):
                                    ?>
                                        <option value="<?php echo $status_option; ?>" <?php echo ($request['status'] === $status_option) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $status_option))); ?>
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
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once '../layout/footer.php';
?>