<?php
require_once 'database/db.php';
require_once 'models/Admin.php';

$db = new Database();
$admin = new Admin($db->getConnection());

$result = $admin->updateUser(1, 'Prospect User', 'prospect@luminest.com', 'Prospect', '', '09191919191');
var_dump($result);
?>
