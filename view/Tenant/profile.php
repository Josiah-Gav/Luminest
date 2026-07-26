<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
?>

<h1>Profile</h1>
<p>Welcome, <?=$_SESSION['username']?>! Here you can view and update your profile information.</p>


<?php
require_once '../layout/footer.php';
?>