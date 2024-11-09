<?php
// /src/data_src/api/events/delete_event.php
require_once '../../includes/session_handler.php';
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$eventId = $data['event_id'] ?? null;

if (!$eventId) {
    echo json_encode(['success' => false, 'message' => 'Event ID is required']);
    exit();
}

// Verify user is the creator using Attendance table
$stmt = $connection->prepare("
    SELECT user_id 
    FROM Attendance 
    WHERE event_id = ? AND user_id = ? AND isCreator = 1
");

$stmt->bind_param('ii', $eventId, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if (!$result->fetch_assoc()) {
    echo json_encode(['success' => false, 'message' => 'Not authorized to delete this event']);
    exit();
}

$connection->begin_transaction();

try {
    // Delete attendance records first
    $stmt = $connection->prepare("DELETE FROM Attendance WHERE event_id = ?");
    $stmt->bind_param('i', $eventId);
    $stmt->execute();

    // Then delete the event
    $stmt = $connection->prepare("DELETE FROM Event WHERE event_id = ?");
    $stmt->bind_param('i', $eventId);
    $stmt->execute();

    $connection->commit();
    echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);

} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'message' => 'Error deleting event: ' . $e->getMessage()]);
}

$connection->close();