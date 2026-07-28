<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../controllers/admin/admin_controller.php';
?>

<h1 class="h3 fw-bold mb-3">Add User</h1>
<form action="../../controllers/admin/admin_controller.php" method="post">
    <label for="full_name">Full Name:</label><br>
    <input type="text" id="full_name" name="full_name" required><br><br>

    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required><br><br>

    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password" required><br><br>

    <label for="role">Role:</label><br>
    <select id="role" name="role" required>
        <option value="Admin">Admin</option>
        <option value="Tenant">Tenant</option>
        <option value="Maintenance_Staff">Maintenance Staff</option>
        <option value="Property_Manager">Property Manager</option>
    </select><br><br>

    <div id="expertise-group" style="display: none;">
        <label for="expertise">Expertise:</label><br>
        <select id="expertise" name="expertise">
            <option value="">Select Expertise</option>
            <option value="plumbing">Plumbing</option>
            <option value="electrical">Electrical</option>
            <option value="carpentry">Carpentry</option>
            <option value="appliance">Appliance</option>
            <option value="general">General</option>
        </select><br><br>
    </div>

    <label for="phone_number">Phone Number:</label><br>
    <input type="text" id="phone_number" name="phone_number" required><br><br>

    <button type="submit" name="create_user" class="btn btn-primary">Add User</button>
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