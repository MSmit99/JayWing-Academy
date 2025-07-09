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
    
    if (!isset($data['class_id']) || !isset($data['className'])) {
        throw new Exception('Class ID and name are required');
    }

    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        throw new Exception("User ID not found in session");
    }
    $name = $data['className'];
    $courseCode = $data['courseCode'] ?? null;
    $description = $data['classDescription'] ?? null; 
    $classId = $data['class_id'];

    // File path is not being updated because it is impossible to change the namespace names in Pinecone
    $stmt = $connection->prepare("UPDATE class SET className = ?, courseCode = ?, classDescription = ? WHERE class_id = ?");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $connection->error);
    }

    $stmt->bind_param("sssi", 
        $name,
        $courseCode,
        $description,
        $classId
    );

    if (!$stmt->execute()) {
        throw new Exception("Execution failed: " . $stmt->error);
    }
    
    // Check if any rows were affected
    if ($stmt->affected_rows === 0) {
        throw new Exception("No class found with the provided information: " . $courseId . " - " . $courseCode . " - " . $description);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Class updated successfully'
    ]);

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error updating class: ' . $e->getMessage()
    ]);
} finally {
    $connection->close();
}