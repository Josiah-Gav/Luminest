<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/admin/admin_controller.php';

$user = $admin->getUserById($_GET['id']);
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-accent-soft">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 fw-bold mb-2 text-accent-black">User details</h1>
                    <p class="text-muted mb-0">Review account information and keep access records clear and easy to manage.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="small text-muted">User ID</div>
                                <div class="fw-semibold"><?= (int) ($user['user_id'] ?? 0) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="small text-muted">Full Name</div>
                                <div class="fw-semibold"><?= htmlspecialchars($user['full_name'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="small text-muted">Email</div>
                                <div class="fw-semibold"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="small text-muted">Role</div>
                                <div class="fw-semibold text-accent-blue"><?= htmlspecialchars($user['role'] ?? 'Unknown') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="small text-muted">Status</div>
                                <div class="fw-semibold"><?= htmlspecialchars($user['status'] ?? 'Pending') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="small text-muted">Phone Number</div>
                                <div class="fw-semibold"><?= htmlspecialchars($user['phone_number'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 border rounded-3 bg-light">
                                <div class="small text-muted">Created At</div>
                                <div class="fw-semibold"><?= htmlspecialchars($user['created_at'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold text-accent-red mb-3">Quick actions</h2>
                    <div class="d-grid gap-2">
                        <a href="edit_user.php?id=<?= (int) ($user['user_id'] ?? 0) ?>" class="btn btn-primary">Edit user</a>
                        <a href="user_management.php" class="btn btn-outline-secondary">Back to user management</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>