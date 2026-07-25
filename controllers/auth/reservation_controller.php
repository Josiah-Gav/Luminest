<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/database/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/House.php';

$db = new Database();
$user = new User($db->getConnection());
$house = new House($db->getConnection());

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION['user_id'])) {
	header('Location: ../../view/auth/login.php');
	exit;
}

$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: ../../view/Prospect/reservation.php?error=invalid_request');
	exit;
}

// CANCEL RESERVATION

if (isset($_POST['cancel_reservation'])) {
	$reservationId = isset($_POST['reservation_id']) ? (int)$_POST['reservation_id'] : 0;

	if ($reservationId <= 0) {
		header('Location: ../../view/Prospect/history.php?error=invalid_cancel');
		exit;
	}

	$reservationStmt = $conn->prepare(
		'SELECT reservation_id, house_type, block, lot, status
		 FROM house_reservations
		 WHERE reservation_id = :reservation_id AND user_id = :user_id
		 LIMIT 1'
	);
	$reservationStmt->execute([
		':reservation_id' => $reservationId,
		':user_id' => (int)$_SESSION['user_id'],
	]);
	$reservation = $reservationStmt->fetch(PDO::FETCH_ASSOC);

	if (!$reservation || ($reservation['status'] ?? '') !== 'pending') {
		header('Location: ../../view/Prospect/history.php?error=cancel_not_allowed');
		exit;
	}

	try {
		$conn->beginTransaction();

		$cancelStmt = $conn->prepare(
			'UPDATE house_reservations
			 SET status = :new_status, updated_at = NOW()
			 WHERE reservation_id = :reservation_id AND user_id = :user_id AND status = :current_status'
		);
		$cancelStmt->execute([
			':new_status' => 'cancelled',
			':reservation_id' => (int)$reservation['reservation_id'],
			':user_id' => (int)$_SESSION['user_id'],
			':current_status' => 'pending',
		]);

		if ($cancelStmt->rowCount() !== 1) {
			throw new RuntimeException('Reservation status update failed.');
		}

		$reopenStmt = $conn->prepare(
			'UPDATE house
			 SET status = :available_status, owner_id = NULL, updated_at = NOW()
			 WHERE house_type = :house_type
			   AND block = :block
			   AND lot = :lot
			   AND status = :reserved_status
			   AND owner_id = :owner_id'
		);
		$reopenStmt->execute([
			':available_status' => 'available',
			':house_type' => $reservation['house_type'],
			':block' => (int)$reservation['block'],
			':lot' => (int)$reservation['lot'],
			':reserved_status' => 'reserved',
			':owner_id' => (int)$_SESSION['user_id'],
		]);

		if ($reopenStmt->rowCount() !== 1) {
			throw new RuntimeException('Unable to reopen house unit.');
		}

		$conn->commit();
		header('Location: ../../view/Prospect/history.php?cancelled=1');
		exit;
	} catch (Throwable $e) {
		if ($conn->inTransaction()) {
			$conn->rollBack();
		}

		header('Location: ../../view/Prospect/history.php?error=cancel_failed');
		exit;
	}
}

//HOUSE RESERVATION

if (!isset($_POST['reserve_house'])) {
	header('Location: ../../view/Prospect/reservation.php?error=invalid_request');
	exit;
}

$selectedHouseKey = $_POST['house'] ?? '';
$selectedBlock = isset($_POST['block']) ? (int)$_POST['block'] : 0;
$selectedLot = isset($_POST['lot']) ? (int)$_POST['lot'] : 0;

$selectedHouse = $house->getBySlug($selectedHouseKey);
$selectedHouseKey = $selectedHouse['slug'];
$selectedDbType = $selectedHouse['db_type'];

if ($selectedBlock <= 0 || $selectedLot <= 0) {
	header('Location: ../../view/Prospect/reservation.php?house=' . urlencode($selectedHouseKey) . '&error=invalid_selection');
	exit;
}

$pendingCheckStmt = $conn->prepare(
	'SELECT reservation_id FROM house_reservations WHERE user_id = :user_id AND status = :status LIMIT 1'
);
$pendingCheckStmt->execute([
	':user_id' => (int)$_SESSION['user_id'],
	':status' => 'pending',
]);

if ($pendingCheckStmt->fetch(PDO::FETCH_ASSOC)) {
	header('Location: ../../view/Prospect/reservation.php?house=' . urlencode($selectedHouseKey) . '&error=has_pending');
	exit;
}

$houseRow = $house->getHouseByTypeBlockLot($selectedDbType, $selectedBlock, $selectedLot);

if (!$houseRow || ($houseRow['status'] ?? '') !== 'available') {
	header('Location: ../../view/Prospect/reservation.php?house=' . urlencode($selectedHouseKey) . '&error=not_available');
	exit;
}

try {
	$conn->beginTransaction();

	$reservationSql = 'INSERT INTO house_reservations (user_id, house_type, block, lot, status, created_at, updated_at)
		VALUES (:user_id, :house_type, :block, :lot, :status, NOW(), NOW())';
	$reservationStmt = $conn->prepare($reservationSql);
	$reservationStmt->execute([
		':user_id' => (int)$_SESSION['user_id'],
		':house_type' => $selectedDbType,
		':block' => $selectedBlock,
		':lot' => $selectedLot,
		':status' => 'pending',
	]);

	$updateSql = 'UPDATE house
		SET status = :status, owner_id = :owner_id, updated_at = NOW()
		WHERE house_id = :house_id AND status = :expected_status';
	$updateStmt = $conn->prepare($updateSql);
	$updateStmt->execute([
		':status' => 'reserved',
		':owner_id' => (int)$_SESSION['user_id'],
		':house_id' => (int)$houseRow['house_id'],
		':expected_status' => 'available',
	]);

	if ($updateStmt->rowCount() !== 1) {
		throw new RuntimeException('House is no longer available.');
	}

	$conn->commit();
	header(
		'Location: ../../view/Prospect/reservation.php?house=' . urlencode($selectedHouseKey)
		. '&saved=1&block=' . (int)$selectedBlock . '&lot=' . (int)$selectedLot
	);
	exit;
} catch (Throwable $e) {
	if ($conn->inTransaction()) {
		$conn->rollBack();
	}

	header('Location: ../../view/Prospect/reservation.php?house=' . urlencode($selectedHouseKey) . '&error=save_failed');
	exit;
}

?>