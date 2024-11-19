<?php
require_once '../../includes/session_handler.php';
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['enrollment_id']) || !isset($data['class_id']) || 
        !isset($data['user_id']) || !isset($data['roleOfClass'])) {
        throw new Exception('Missing required fields');
    }

    $stmt = $connection->prepare("UPDATE Enrollment SET class_id = ?, user_id = ?, roleOfClass = ? WHERE enrollment_id = ?");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $connection->error);
    }

    $stmt->bind_param("iisi", 
        $data['class_id'],
        $data['user_id'],
        $data['roleOfClass'],
        $data['enrollment_id']
    );

    if (!$stmt->execute()) {
        throw new Exception("Execution failed: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("No enrollment found with the given ID");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Enrollment updated successfully'
    ]);

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error updating enrollment: ' . $e->getMessage()
    ]);
} finally {
    $connection->close();
}