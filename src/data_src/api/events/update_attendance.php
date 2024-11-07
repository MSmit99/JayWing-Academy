<?php
require_once '../../includes/session_handler.php';
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$attendance = $_POST['attendance'] ?? [];

$connection->begin_transaction();

try {
    // Verify user is event creator using created_by column
    $stmt = $connection->prepare("
        SELECT e.*, et.wings 
        FROM Event e
        JOIN Event_Type et ON e.event_type_id = et.event_type_id
        WHERE e.event_id = ? AND e.created_by = ?
    ");
    $stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();

    if (!$event) {
        throw new Exception("Not authorized to update attendance");
    }


    $stmt = $connection->prepare("
        SELECT user_id FROM Attendance WHERE event_id = ?
    ");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $participants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $updateWingsStmt = $connection->prepare("
        UPDATE User SET wings = wings + ? WHERE user_id = ?
    ");

    foreach ($participants as $participant) {
        $userId = $participant['user_id'];
        // If user was checked in the form
        if (isset($attendance[$userId])) {
            $updateWingsStmt->bind_param("ii", $event['wings'], $userId);
            $updateWingsStmt->execute();
        }
    }

    $connection->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $connection->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$connection->close();