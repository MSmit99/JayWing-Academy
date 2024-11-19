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
    
    if (!isset($data['class_id']) || !isset($data['user_id']) || !isset($data['roleOfClass'])) {
        throw new Exception('Missing required fields');
    }

    $stmt = $connection->prepare("INSERT INTO Enrollment (class_id, user_id, roleOfClass) VALUES (?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $connection->error);
    }

    $stmt->bind_param("iis", 
        $data['class_id'],
        $data['user_id'],
        $data['roleOfClass']
    );

    if (!$stmt->execute()) {
        throw new Exception("Execution failed: " . $stmt->error);
    }

    $newEnrollmentId = $connection->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Enrollment created successfully',
        'id' => $newEnrollmentId
    ]);

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error creating enrollment: ' . $e->getMessage()
    ]);
} finally {
    $connection->close();
}