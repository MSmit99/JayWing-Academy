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

$connection->begin_transaction();

try {
    $eventId = $_POST['event_id'] ?? null;
    
    if (!$eventId) {
        throw new Exception("Event ID is required");
    }

    // Verify user is the creator using Attendance table
    $stmt = $connection->prepare("
        SELECT 1 FROM Attendance 
        WHERE event_id = ? AND user_id = ? AND isCreator = 1
    ");
    $stmt->bind_param('ii', $eventId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Not authorized to edit this event");
    }

    // Update event details with new column names
    $stmt = $connection->prepare("
        UPDATE Event 
        SET eventName = ?, 
            location = ?,
            eventStartTime = ?, 
            eventEndTime = ?, 
            type_id = ?
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
            INSERT INTO Attendance (roleOfEvent, user_id, event_id, isCreator)
            VALUES (?, ?, ?, ?)
        ");
        
        // First add the creator
        $isCreator = 1;
        $stmt->bind_param("siii", 
            $_POST['participants'][0]['role'],
            $_SESSION['user_id'],
            $eventId,
            $isCreator
        );
        $stmt->execute();

        // Then add other participants
        $userStmt = $connection->prepare("SELECT user_id FROM User WHERE email = ?");
        $isCreator = 0;
        
        foreach ($_POST['participants'] as $index => $participant) {
            if ($index === 0 || empty($participant['email'])) continue;
            
            $userStmt->bind_param("s", $participant['email']);
            $userStmt->execute();
            $user = $userStmt->get_result()->fetch_assoc();
            
            if ($user) {
                $stmt->bind_param("siii",
                    $participant['role'],
                    $user['user_id'],
                    $eventId,
                    $isCreator
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