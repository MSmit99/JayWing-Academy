<?php
// /src/data_src/api/events/get_event_details.php
require_once '../../includes/session_handler.php';
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

$event_id = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);

if (!$event_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid event ID']);
    exit();
}

try {
    // Get event details
    $query = "
        SELECT e.*, et.type_name, et.wings,
               (SELECT user_id FROM Attendance WHERE event_id = e.event_id LIMIT 1) as creator_id
        FROM Event e
        JOIN Event_Type et ON e.event_type_id = et.event_type_id
        WHERE e.event_id = ?
    ";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
    
    // Get participants with emails
    $query = "
        SELECT u.username, u.email, a.role_in_event
        FROM Attendance a
        JOIN User u ON a.user_id = u.user_id
        WHERE a.event_id = ?
        ORDER BY CASE WHEN a.user_id = ? THEN 0 ELSE 1 END
    ";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
    $stmt->execute();
    $participants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'event' => $event,
        'participants' => $participants,
        'current_user_id' => isLoggedIn() ? getCurrentUserId() : null
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching event details']);
}

$connection->close();
?>