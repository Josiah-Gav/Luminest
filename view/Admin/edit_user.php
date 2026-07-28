<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/admin/admin_controller.php';

$user = $admin->getUserById($_GET['id']);
?>

<h1 class="h3 fw-bold mb-3">Edit User</h1>
<form action="../../controllers/admin/admin_controller.php" method="post">
    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">

    <label for="full_name">Full Name:</label><br>
    <input type="text" id="full_name" name="full_name" value="<?= $user['full_name'] ?>" required><br><br>

    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" value="<?= $user['email'] ?>" required><br><br>

    <label for="role">Role:</label><br>
    <select id="role" name="role" required>
        <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
        <option value="Tenant" <?= $user['role'] === 'Tenant' ? 'selected' : '' ?>>Tenant</option>
        <option value="Maintenance_Staff" <?= $user['role'] === 'Maintenance_Staff' ? 'selected' : '' ?>>Maintenance Staff</option>
        <option value="Property_Manager" <?= $user['role'] === 'Property_Manager' ? 'selected' : '' ?>>Property Manager</option>
    </select><br><br>

    <div id="expertise-group" style="display: none;">
        <label for="expertise">Expertise:</label><br>
        <select id="expertise" name="expertise">
            <option value="">Select Expertise</option>
            <option value="plumbing" <?= $user['expertise'] === 'plumbing' ? 'selected' : '' ?>>Plumbing</option>
            <option value="electrical" <?= $user['expertise'] === 'electrical' ? 'selected' : '' ?>>Electrical</option>
            <option value="carpentry" <?= $user['expertise'] === 'carpentry' ? 'selected' : '' ?>>Carpentry</option>
            <option value="appliance" <?= $user['expertise'] === 'appliance' ? 'selected' : '' ?>>Appliance</option>
            <option value="general" <?= $user['expertise'] === 'general' ? 'selected' : '' ?>>General</option>
        </select><br><br>
    </div>

    <label for="phone_number">Phone Number:</label><br>
    <input type="text" id="phone_number" name="phone_number" value="<?= $user['phone_number'] ?>" required><br><br>

    <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
    <a href="user_management.php" class="btn btn-secondary">Cancel</a>  
</form>

<script>
$(document).ready(function () {
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
});
</script>
