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
    // First verify user is event creator and get event details
    $stmt = $connection->prepare("
        SELECT e.*, et.wings 
        FROM Event e
        JOIN Event_Type et ON e.type_id = et.event_type_id
        JOIN Attendance a ON e.event_id = a.event_id
        WHERE e.event_id = ? AND a.user_id = ? AND a.isCreator = 1
    ");
    $stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();

    if (!$event) {
        throw new Exception("Not authorized to update attendance");
    }

    // First, remove all previously awarded wings for this event
    // This ensures we start fresh and prevents double-counting
    $stmt = $connection->prepare("
        UPDATE User u
        JOIN Attendance a ON u.user_id = a.user_id
        SET u.wings = u.wings - ?
        WHERE a.event_id = ? AND u.wings >= ?
    ");
    $stmt->bind_param("iii", $event['wings'], $event_id, $event['wings']);
    $stmt->execute();

    // Now award wings to currently checked attendees
    if (!empty($attendance)) {
        $stmt = $connection->prepare("
            UPDATE User 
            SET wings = wings + ? 
            WHERE user_id IN (" . implode(',', array_map('intval', array_keys($attendance))) . ")
        ");
        $stmt->bind_param("i", $event['wings']);
        $stmt->execute();
    }

    $connection->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $connection->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$connection->close();
?>