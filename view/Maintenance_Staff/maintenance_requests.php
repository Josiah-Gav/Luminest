<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Maintenance_Staff') {
	header('Location: ../auth/login.php');
	exit;
}
?>

<h1 class="h3 fw-bold mt-3 mb-2 lm-title">Maintenance Requests</h1>
<h6 class="text-secondary mb-0">Maintenance requests assigned to you.</h6>

<table class="table table-striped mt-4">
    <thead>
        <tr>
            <th scope="col">Request ID</th>
            <th scope="col">Tenant Name</th>
            <th scope="col">Property Address</th>
            <th scope="col">Issue Description</th>
            <th scope="col">Status</th>
            <th scope="col">Priority</th>
            <th scope="col">Created At</th>
            <th scope="col">Actions</th>
        </tr>
    </thead>
    <tbody id="maintenance-requests-table-body">
        <?php
        require_once '../../models/Maintenance.php';
        $maintenance = new Maintenance($db->getConnection());
        $requests = $maintenance->getRequestsByStaff($_SESSION['user_id']);
        if (empty($requests)) {
            echo "<tr><td colspan='8' class='text-center'>No maintenance requests assigned to you.</td></tr>";
        } else {
            foreach ($requests as $request) {
                echo "<tr>";
                echo "<td>{$request['id']}</td>";
                echo "<td>{$request['tenant_name']}</td>";
                echo "<td>{$request['property_address']}</td>";
                echo "<td>{$request['title']}</td>";
                echo "<td>{$request['status']}</td>";
                echo "<td>{$request['priority']}</td>";
                echo "<td>{$request['created_at']}</td>";
                echo "<td><a href='maintenance_details.php?id={$request['id']}' class='btn btn-primary btn-sm'>View Details</a></td>";
                echo "</tr>";
            }
        }
        ?>
    </tbody>
</table>

<?php
require_once '../layout/footer.php';
?>