<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/admin/admin_controller.php';

$user = $admin->getUserById($_GET['id']);
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 fw-bold mb-2 text-accent-black">Edit user</h1>
                    <p class="text-muted">Update account details while keeping the information consistent across the platform.</p>
                    <div id="alertContainer"></div>
                    <form id="editUserForm" action="../../controllers/admin/admin_controller.php" method="post">
                        <input type="hidden" name="ajax" value="1">
                        <input type="hidden" name="update_user" value="1">
                        <input type="hidden" name="user_id" value="<?= (int) ($user['user_id'] ?? 0) ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label">Role</label>
                                <select id="role" name="role" class="form-select" required>
                                    <option value="Admin" <?= (($user['role'] ?? '') === 'Admin') ? 'selected' : '' ?>>Admin</option>
                                    <option value="Tenant" <?= (($user['role'] ?? '') === 'Tenant') ? 'selected' : '' ?>>Tenant</option>
                                    <option value="Maintenance_Staff" <?= (($user['role'] ?? '') === 'Maintenance_Staff') ? 'selected' : '' ?>>Maintenance Staff</option>
                                    <option value="Property_Manager" <?= (($user['role'] ?? '') === 'Property_Manager') ? 'selected' : '' ?>>Property Manager</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="text" id="phone_number" name="phone_number" class="form-control" value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>" placeholder="e.g. 09191919191" required>
                            </div>
                            <div id="expertise-group" class="col-12" style="display: none;">
                                <label for="expertise" class="form-label">Expertise</label>
                                <select id="expertise" name="expertise" class="form-select">
                                    <option value="">Select Expertise</option>
                                    <option value="plumbing" <?= (($user['expertise'] ?? '') === 'plumbing') ? 'selected' : '' ?>>Plumbing</option>
                                    <option value="electrical" <?= (($user['expertise'] ?? '') === 'electrical') ? 'selected' : '' ?>>Electrical</option>
                                    <option value="carpentry" <?= (($user['expertise'] ?? '') === 'carpentry') ? 'selected' : '' ?>>Carpentry</option>
                                    <option value="appliance" <?= (($user['expertise'] ?? '') === 'appliance') ? 'selected' : '' ?>>Appliance</option>
                                    <option value="general" <?= (($user['expertise'] ?? '') === 'general') ? 'selected' : '' ?>>General</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <button type="submit" name="update_user" class="btn btn-primary">Update user</button>
                                <a href="user_management.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold text-accent-blue mb-3">Account guidance</h2>
                    <ul class="text-muted small ps-3 mb-0">
                        <li>Update phone details whenever contact information changes.</li>
                        <li>Maintenance staff can keep a specialist field when needed.</li>
                        <li>Use the role selection to reflect the user’s current access level.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    function showAlert(message, type) {
        $('#alertContainer').html('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    }

    function toggleExpertiseField() {
        var isMaintenanceStaff = $('#role').val() === 'Maintenance_Staff';
        $('#expertise-group').toggle(isMaintenanceStaff);
        $('#expertise').prop('disabled', !isMaintenanceStaff);
        $('#expertise').prop('required', isMaintenanceStaff);

        if (!isMaintenanceStaff) {
            $('#expertise').val('');
        }
    }

    $('#role').on('change', toggleExpertiseField);
    toggleExpertiseField();

    $('#editUserForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response && response.success) {
                    showAlert(response.message, 'success');
                    setTimeout(function () {
                        window.location.href = response.redirect || 'user_management.php';
                    }, 700);
                } else {
                    showAlert((response && response.message) || 'Unable to update user.', 'danger');
                }
            },
            error: function () {
                showAlert('Unable to update user right now.', 'danger');
            }
        });
    });
});
</script>
