<?php
// /src/data_src/api/availability/delete_availability.php
require_once '../../includes/session_handler.php';
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['availability_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Availability ID is required']);
    exit();
}

try {
    $user_id = getCurrentUserId();
    
    // Delete specific availability slot (checking user_id for security)
    $stmt = $connection->prepare("
        DELETE FROM Availability 
        WHERE availability_id = ? AND user_id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $connection->error);
    }
    
    $stmt->bind_param("ii", $data['availability_id'], $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Delete failed: " . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("No availability found or not authorized to delete");
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Availability slot deleted successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting availability: ' . $e->getMessage()
    ]);
}

$stmt->close();
$connection->close();