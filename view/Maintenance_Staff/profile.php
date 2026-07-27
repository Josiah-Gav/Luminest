<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Maintenance_Staff') {
	header('Location: ../auth/login.php');
	exit;
}
?>

Hello Maintenance Staff <?=$_SESSION['username']?>

<?php
require_once '../layout/footer.php';
?>