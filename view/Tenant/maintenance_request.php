<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
?>

<h1>Maintenance Request</h1>
<h6>Please fill out the form below to submit your maintenance request.</h6>

<form action="../../controllers/maintenance/maintenance_request_controller.php" method="post">
    <label for="title">Title:</label>
    <input type="text" id="title" name="title" required>

    <label for="description">Description:</label>
    <textarea id="description" name="description" required></textarea>

    <label for="category">Category:</label>
    <select id="category" name="category" required>
        <option value="plumbing">Plumbing</option>
        <option value="electrical">Electrical</option>
        <option value="carpentry">Carpentry</option>
        <option value="appliance">Appliance</option>
        <option value="general">General</option>
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