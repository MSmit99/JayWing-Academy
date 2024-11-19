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
    $query = "SELECT e.enrollment_id, e.class_id, e.user_id, e.roleOfClass, 
              c.className, u.username, u.firstName, u.lastName 
              FROM Enrollment e
              JOIN Class c ON e.class_id = c.class_id
              JOIN User u ON e.user_id = u.user_id";

    $result = $connection->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $connection->error);
    }

    $enrollments = [];
    while ($row = $result->fetch_assoc()) {
        $enrollments[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $enrollments
    ]);

    $result->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching enrollments: ' . $e->getMessage()
    ]);
} finally {
    $connection->close();
}