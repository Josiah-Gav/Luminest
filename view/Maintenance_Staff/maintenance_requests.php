<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Maintenance_Staff') {
    header('Location: ../auth/login.php');
    exit;
}
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-accent-soft">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 fw-bold mb-2 text-accent-black">Maintenance requests</h1>
                    <p class="text-muted mb-0">Work assigned to you is listed below so you can review, update, and resolve issues quickly.</p>
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
                                    echo "<tr><td colspan='8' class='text-center text-muted py-4'>No maintenance requests assigned to you.</td></tr>";
                                } else {
                                    foreach ($requests as $request) {
                                        echo "<tr>";
                                        echo "<td>" . (int) $request['id'] . "</td>";
                                        echo "<td>" . htmlspecialchars($request['tenant_name'] ?? 'N/A') . "</td>";
                                        echo "<td>" . htmlspecialchars($request['property_address'] ?? 'N/A') . "</td>";
                                        echo "<td>" . htmlspecialchars($request['title'] ?? 'N/A') . "</td>";
                                        echo "<td><span class='badge rounded-pill bg-light text-dark'>" . htmlspecialchars($request['status'] ?? 'Pending') . "</span></td>";
                                        echo "<td>" . htmlspecialchars($request['priority'] ?? 'Normal') . "</td>";
                                        echo "<td>" . htmlspecialchars($request['created_at'] ?? 'N/A') . "</td>";
                                        echo "<td><a href='maintenance_details.php?id=" . (int) $request['id'] . "' class='btn btn-primary btn-sm'>View Details</a></td>";
                                        echo "</tr>";
                                    }
                                }
                                ?>
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