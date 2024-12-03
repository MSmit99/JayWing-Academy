<?php
// /src/data_src/api/availability/toggle_availability.php
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
    $user_id = getCurrentUserId();

    // First get current status
    $checkStmt = $connection->prepare("SELECT Unavailable FROM User WHERE user_id = ?");
    if (!$checkStmt) {
        throw new Exception("Prepare check failed: " . $connection->error);
    }
    
    $checkStmt->bind_param("i", $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $currentStatus = $result->fetch_assoc();
    
    // Calculate new status (if currently 1, make 0 and vice versa)
    $newStatus = $currentStatus['Unavailable'] ? 0 : 1;
    
    // Update the status
    $updateStmt = $connection->prepare("UPDATE User SET Unavailable = ? WHERE user_id = ?");
    if (!$updateStmt) {
        throw new Exception("Prepare update failed: " . $connection->error);
    }
    
    $updateStmt->bind_param("ii", $newStatus, $user_id);
    if (!$updateStmt->execute()) {
        throw new Exception("Update failed: " . $updateStmt->error);
    }

    $connection->commit();
    
    // Return the inverse of Unavailable status for isAvailable
    echo json_encode([
        'success' => true,
        'isAvailable' => !$newStatus
    ]);

} catch (Exception $e) {
    $connection->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error toggling availability: ' . $e->getMessage()
    ]);
}

$checkStmt->close();
$updateStmt->close();
$connection->close();