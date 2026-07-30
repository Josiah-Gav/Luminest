<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/controllers/BaseController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/luminest/models/House.php';

function sendJsonResponse($payload, $statusCode = 200) {
	http_response_code($statusCode);
	header('Content-Type: application/json');
	echo json_encode($payload);
	exit;
}

$isAjaxRequest = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
	|| !empty($_POST['ajax'])
	|| !empty($_GET['ajax']);

$user = new User();
$house = new House();

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION['user_id'])) {
	if ($isAjaxRequest) {
		sendJsonResponse(['success' => false, 'message' => 'Please sign in before reserving a house.'], 401);
	}
	header('Location: ../../view/auth/login.php');
	exit;
}

$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	if ($isAjaxRequest) {
		sendJsonResponse(['success' => false, 'message' => 'Invalid request.'], 400);
	}
	header('Location: ../../view/Prospect/reservation.php?error=invalid_request');
	exit;
}

// CANCEL RESERVATION

if (isset($_POST['cancel_reservation'])) {
	$reservationId = isset($_POST['reservation_id']) ? (int)$_POST['reservation_id'] : 0;

	if ($reservationId <= 0) {
		if ($isAjaxRequest) {
			sendJsonResponse(['success' => false, 'message' => 'Invalid reservation id.'], 400);
		}
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
		if ($isAjaxRequest) {
			sendJsonResponse(['success' => false, 'message' => 'This reservation cannot be cancelled at the moment.'], 400);
		}
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

		if (!$house->markAsAvailableByUnit($reservation['house_type'], (int)$reservation['block'], (int)$reservation['lot'])) {
			throw new RuntimeException('Unable to reopen house unit.');
		}

		$conn->commit();
		if ($isAjaxRequest) {
			sendJsonResponse(['success' => true, 'message' => 'Reservation cancelled successfully.', 'redirect' => '/luminest/view/Prospect/history.php?cancelled=1']);
		}
		header('Location: ../../view/Prospect/history.php?cancelled=1');
		exit;
	} catch (Throwable $e) {
		if ($conn->inTransaction()) {
			$conn->rollBack();
		}

		if ($isAjaxRequest) {
			sendJsonResponse(['success' => false, 'message' => 'Unable to cancel reservation right now.'], 400);
		}
		header('Location: ../../view/Prospect/history.php?error=cancel_failed');
		exit;
	}
}

//HOUSE RESERVATION

if (!isset($_POST['reserve_house'])) {
	if ($isAjaxRequest) {
		sendJsonResponse(['success' => false, 'message' => 'Invalid reservation request.'], 400);
	}
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
	if ($isAjaxRequest) {
		sendJsonResponse(['success' => false, 'message' => 'Please choose a valid block and lot before reserving.'], 400);
	}
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
	if ($isAjaxRequest) {
		sendJsonResponse(['success' => false, 'message' => 'You already have a pending reservation.'], 400);
	}
	header('Location: ../../view/Prospect/reservation.php?house=' . urlencode($selectedHouseKey) . '&error=has_pending');
	exit;
}

$houseRow = $house->getHouseByTypeBlockLot($selectedDbType, $selectedBlock, $selectedLot);

if (!$houseRow || ($houseRow['status'] ?? '') !== 'available') {
	if ($isAjaxRequest) {
		sendJsonResponse(['success' => false, 'message' => 'That unit is no longer available.'], 400);
	}
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

	if (!$house->markAsReserved((int)$houseRow['house_id'])) {
		throw new RuntimeException('House is no longer available.');
	}

	$conn->commit();
	if ($isAjaxRequest) {
		sendJsonResponse([
			'success' => true,
			'message' => 'Reservation saved successfully.',
			'redirect' => '/luminest/view/Prospect/reservation.php?house=' . urlencode($selectedHouseKey) . '&saved=1&block=' . (int)$selectedBlock . '&lot=' . (int)$selectedLot,
		]);
	}
	header(
		'Location: ../../view/Prospect/reservation.php?house=' . urlencode($selectedHouseKey)
		. '&saved=1&block=' . (int)$selectedBlock . '&lot=' . (int)$selectedLot
	);
	exit;
} catch (Throwable $e) {
	if ($conn->inTransaction()) {
		$conn->rollBack();
	}

	if ($isAjaxRequest) {
		sendJsonResponse(['success' => false, 'message' => 'Unable to save reservation right now.'], 400);
	}
	header('Location: ../../view/Prospect/reservation.php?house=' . urlencode($selectedHouseKey) . '&error=save_failed');
	exit;
}

?>