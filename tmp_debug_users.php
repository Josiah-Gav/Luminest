<?php
require_once 'database/db.php';

$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->query('SELECT user_id, full_name, email, phone_number FROM users LIMIT 5');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo $row['user_id'] . '|' . $row['full_name'] . '|' . $row['email'] . '|' . $row['phone_number'] . PHP_EOL;
}
?>
