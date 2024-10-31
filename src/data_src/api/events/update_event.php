<?php
// /src/data_src/api/events/update_event.php
require_once '../../includes/session_handler.php';
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Start transaction
$connection->begin_transaction();

try {
    $eventId = $_POST['event_id'] ?? null;
    
    if (!$eventId) {
        throw new Exception("Event ID is required");
    }

    // Verify user is the creator
    $stmt = $connection->prepare("
        SELECT user_id FROM Attendance 
        WHERE event_id = ? 
        LIMIT 1
    ");
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $creator = $stmt->get_result()->fetch_assoc();

    if (!$creator || $creator['user_id'] != $_SESSION['user_id']) {
        throw new Exception("Not authorized to edit this event");
    }

    // Update event details
    $stmt = $connection->prepare("
        UPDATE Event 
        SET event_name = ?, 
            location = ?,
            start = ?, 
            end = ?, 
            event_type_id = ?
        WHERE event_id = ?
    ");

    $stmt->bind_param("ssssii", 
        $_POST['event_name'],
        $_POST['location'],
        $_POST['start_time'],
        $_POST['end_time'],
        $_POST['event_type_id'],
        $eventId
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to update event");
    }

    // Delete old attendance records
    $stmt = $connection->prepare("DELETE FROM Attendance WHERE event_id = ?");
    $stmt->bind_param("i", $eventId);
    $stmt->execute();

    // Add updated participants
    if (isset($_POST['participants']) && is_array($_POST['participants'])) {
        $stmt = $connection->prepare("
            INSERT INTO Attendance (role_in_event, user_id, event_id)
            VALUES (?, ?, ?)
        ");
        
        // First add the creator
        $stmt->bind_param("sii", 
            $_POST['participants'][0]['role'],
            $_SESSION['user_id'],
            $eventId
        );
        $stmt->execute();

        // Then add other participants
        $userStmt = $connection->prepare("SELECT user_id FROM User WHERE email = ?");
        
        foreach ($_POST['participants'] as $index => $participant) {
            if ($index === 0 || empty($participant['email'])) continue; // Skip creator and empty emails
            
            $userStmt->bind_param("s", $participant['email']);
            $userStmt->execute();
            $user = $userStmt->get_result()->fetch_assoc();
            
            if ($user) {
                $stmt->bind_param("sii",
                    $participant['role'],
                    $user['user_id'],
                    $eventId
                );
                $stmt->execute();
            }
        }
        $userStmt->close();
    }

    $connection->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Event updated successfully'
    ]);

} catch (Exception $e) {
    $connection->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error updating event: ' . $e->getMessage()
    ]);
}

$connection->close();