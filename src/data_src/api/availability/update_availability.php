<?php
// /src/data_src/api/availability/update_availability.php
require_once '../../includes/session_handler.php';
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['slots']) || !is_array($data['slots'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data format']);
    exit();
}

$connection->begin_transaction();

try {
    $user_id = getCurrentUserId();

    // Insert new availability slots
    $insertStmt = $connection->prepare("
        INSERT INTO Availability (user_id, weekday, start, end) 
        VALUES (?, ?, ?, ?)
    ");
    
    if (!$insertStmt) {
        throw new Exception("Prepare insert failed: " . $connection->error);
    }

    // Check for duplicate time slots
    $checkStmt = $connection->prepare("
        SELECT COUNT(*) as count 
        FROM Availability 
        WHERE user_id = ? 
        AND weekday = ? 
        AND ((start <= ? AND end >= ?) OR (start <= ? AND end >= ?))
    ");

    if (!$checkStmt) {
        throw new Exception("Prepare check failed: " . $connection->error);
    }

    $insertedSlots = 0;
    $errors = [];

    foreach ($data['slots'] as $slot) {
        // Validate time format
        if (!validateTimeFormat($slot['start']) || !validateTimeFormat($slot['end'])) {
            $errors[] = "Invalid time format for {$slot['weekday']}";
            continue;
        }

        // Validate start time is before end time
        if ($slot['start'] >= $slot['end']) {
            $errors[] = "Start time must be before end time for {$slot['weekday']}";
            continue;
        }

        // Check for overlapping slots
        $checkStmt->bind_param("ssssss", 
            $user_id, 
            $slot['weekday'],
            $slot['end'],
            $slot['start'],
            $slot['start'],
            $slot['end']
        );
        
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            $errors[] = "Overlapping time slot detected for {$slot['weekday']}";
            continue;
        }

        // Insert the new slot
        $insertStmt->bind_param("isss", 
            $user_id, 
            $slot['weekday'], 
            $slot['start'], 
            $slot['end']
        );
        
        if (!$insertStmt->execute()) {
            $errors[] = "Failed to add slot for {$slot['weekday']}";
            continue;
        }

        $insertedSlots++;
    }

    $connection->commit();
    
    if (empty($errors)) {
        echo json_encode([
            'success' => true,
            'message' => "Successfully added $insertedSlots availability slot(s)"
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => "Added $insertedSlots slot(s) with some warnings",
            'warnings' => $errors
        ]);
    }

} catch (Exception $e) {
    $connection->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error updating availability: ' . $e->getMessage()
    ]);
}

function validateTimeFormat($time) {
    // Validate time format (HH:MM)
    return preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $time);
}

$insertStmt->close();
$checkStmt->close();
$connection->close();