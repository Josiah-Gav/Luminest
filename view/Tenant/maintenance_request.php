<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../database/db.php';
require_once '../../models/Maintenance.php';

$maintenance = new Maintenance($db->getConnection());
$categories = $maintenance->getRequestCategoryOptions();
?>

<h1>Maintenance Request</h1>
<h6>Please fill out the form below to submit your maintenance request.</h6>

<form action="../../controllers/maintenance/maintenance_request_controller.php" method="post">
    <label for="title">Title:</label>
    <input type="text" id="title" name="title" required>

    <label for="description">Description:</label>
    <textarea id="description" name="description" required></textarea>

    <label for="category">Required Staff Role:</label>
    <select id="category" name="category" required>
        <?php foreach ($categories as $category): ?>
            <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $category))); ?></option>
        <?php endforeach; ?>
    </select>

    <label for="priority">Priority:</label>
    <select id="priority" name="priority" required>
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
        <option value="urgent">Urgent</option>
    </select>

    <button type="submit" name="submit_request" class="btn btn-primary">Submit Request</button>
</form>

<?php
require_once '../layout/footer.php';
?>