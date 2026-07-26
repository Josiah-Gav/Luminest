<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
?>

<h1>Maintenance Request</h1>
<table id="maintenance-history-table" class="table table-striped">
    <thead>
        <tr>
            <th>Request ID</th>
            <th>Category</th>
            <th>Description</th>
            <th>Status</th>
            <th>Date Submitted</th>
        </tr>
    </thead>
    <tbody>
        <?php
        require_once '../../controllers/maintenance/maintenance_request_controller.php';
        $requests = $maintenance->getRequestsByTenant($_SESSION['user_id']);
        foreach ($requests as $request) {
            echo "<tr>";
            echo "<td>{$request['id']}</td>";
            echo "<td>{$request['category']}</td>";
            echo "<td>{$request['description']}</td>";
            echo "<td>{$request['status']}</td>";
            echo "<td>{$request['created_at']}</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

<?php
require_once '../layout/footer.php';
?>