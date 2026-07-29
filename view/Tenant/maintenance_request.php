<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../database/db.php';
require_once '../../models/Maintenance.php';

$maintenance = new Maintenance($db->getConnection());
$categories = $maintenance->getRequestCategoryOptions();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 bg-accent-soft">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold text-accent-red mb-2">How it works</h2>
                    <p class="text-muted small mb-3">Let us know what needs attention so the right team can assist quickly.</p>
                    <ul class="small text-muted ps-3 mb-0">
                        <li>Choose the staff category that fits the issue.</li>
                        <li>Describe the problem clearly and include urgency.</li>
                        <li>Track updates in your maintenance history.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 fw-bold mb-2 text-accent-black">Maintenance Request</h1>
                    <p class="text-muted">Please fill out the form below to submit your maintenance request.</p>
                    <div id="alertContainer"></div>
                    <form id="maintenanceRequestForm" action="../../controllers/maintenance/maintenance_request_controller.php" method="post">
                        <input type="hidden" name="ajax" value="1">
                        <input type="hidden" name="submit_request" value="1">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" id="title" name="title" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="5" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="category" class="form-label">Required Staff Role</label>
                                <select id="category" name="category" class="form-select" required>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $category))); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="priority" class="form-label">Priority</label>
                                <select id="priority" name="priority" class="form-select" required>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Submit Request</button>
                                <a href="maintenance_history.php" class="btn btn-outline-secondary">View History</a>
                            </div>
                        </div>
                    </form>
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

    $('#maintenanceRequestForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response && response.success) {
                    showAlert(response.message, 'success');
                    $('#maintenanceRequestForm')[0].reset();
                    window.location.href = response.redirect || 'maintenance_history.php';
                } else {
                    showAlert((response && response.message) || 'Unable to submit request.', 'danger');
                }
            },
            error: function () {
                showAlert('Unable to submit request right now.', 'danger');
            }
        });
    });
});
</script>

<?php
require_once '../layout/footer.php';
?>