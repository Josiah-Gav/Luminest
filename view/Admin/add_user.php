<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/admin/admin_controller.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 fw-bold mb-2 text-accent-black">Add user</h1>
                    <p class="text-muted">Create a new platform user and assign the right role instantly.</p>
                    <div id="alertContainer"></div>
                    <form id="addUserForm" action="../../controllers/admin/admin_controller.php" method="post">
                        <input type="hidden" name="ajax" value="1">
                        <input type="hidden" name="create_user" value="1">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label">Role</label>
                                <select id="role" name="role" class="form-select" required>
                                    <option value="Admin">Admin</option>
                                    <option value="Tenant">Tenant</option>
                                    <option value="Maintenance_Staff">Maintenance Staff</option>
                                    <option value="Property_Manager">Property Manager</option>
                                </select>
                            </div>
                            <div id="expertise-group" class="col-12" style="display: none;">
                                <label for="expertise" class="form-label">Expertise</label>
                                <select id="expertise" name="expertise" class="form-select">
                                    <option value="">Select Expertise</option>
                                    <option value="plumbing">Plumbing</option>
                                    <option value="electrical">Electrical</option>
                                    <option value="carpentry">Carpentry</option>
                                    <option value="appliance">Appliance</option>
                                    <option value="general">General</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="text" id="phone_number" name="phone_number" class="form-control" placeholder="e.g. 09191919191" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Add user</button>
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
                    <h2 class="h6 fw-bold text-accent-red mb-3">Helpful notes</h2>
                    <ul class="text-muted small ps-3 mb-0">
                        <li>Maintenance staff accounts can include a specialty.</li>
                        <li>Assign the role that best matches the user’s responsibilities.</li>
                        <li>Phone numbers help support and property coordination.</li>
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

    $('#addUserForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response && response.success) {
                    showAlert(response.message, 'success');
                    $('#addUserForm')[0].reset();
                    window.location.href = response.redirect || 'user_management.php';
                } else {
                    showAlert((response && response.message) || 'Unable to add user.', 'danger');
                }
            },
            error: function () {
                showAlert('Unable to add user right now.', 'danger');
            }
        });
    });
});
</script>