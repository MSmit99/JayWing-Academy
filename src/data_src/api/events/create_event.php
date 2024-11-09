<?php
// /src/data_src/api/events/create_event.php
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
    // Create the event with new column names
    $stmt = $connection->prepare("
        INSERT INTO Event (eventName, eventStartTime, eventEndTime, location, type_id, eventDescription)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $connection->error);
    }

    $description = $_POST['description'] ?? ''; // Optional description field
    
    $stmt->bind_param("ssssss",
        $_POST['event_name'],
        $_POST['start_time'],
        $_POST['end_time'],
        $_POST['location'],
        $_POST['event_type_id'], // This is now type_id in the new schema
        $description
    );

    if (!$stmt->execute()) {
        throw new Exception("Event creation failed: " . $stmt->error);
    }

    $eventId = $connection->insert_id;
    $stmt->close();

    // Add the creator with isCreator flag
    $stmt = $connection->prepare("
        INSERT INTO Attendance (roleOfEvent, user_id, event_id, isCreator)
        VALUES (?, ?, ?, 1)
    ");

    // Get the creator's role from the first participant or default to 'professor'
    $creatorRole = isset($_POST['participants'][0]['role']) ? 
        $_POST['participants'][0]['role'] : 'professor';

    $stmt->bind_param("sii",
        $creatorRole,
        $_SESSION['user_id'],
        $eventId
    );
    $stmt->execute();

    // Add additional participants
    if (isset($_POST['participants']) && is_array($_POST['participants'])) {
        $stmt = $connection->prepare("
            INSERT INTO Attendance (roleOfEvent, user_id, event_id, isCreator)
            VALUES (?, ?, ?, 0)
        ");

        $userStmt = $connection->prepare("
            SELECT user_id FROM User WHERE email = ?
        ");

        // Start from index 1 to skip the creator's entry
        for ($i = 1; $i < count($_POST['participants']); $i++) {
            $participant = $_POST['participants'][$i];
            if (empty($participant['email'])) continue;

            $userStmt->bind_param("s", $participant['email']);
            $userStmt->execute();
            $result = $userStmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && $user['user_id'] != $_SESSION['user_id']) {
                $stmt->bind_param("sii",
                    $participant['role'],
                    $user['user_id'],
                    $eventId
                );
                $stmt->execute();
            }
        }

        $userStmt->close();
        $stmt->close();
    }

    $connection->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Event created successfully',
        'eventId' => $eventId
    ]);

} catch (Exception $e) {
    $connection->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error creating event: ' . $e->getMessage()
    ]);
}

$connection->close();