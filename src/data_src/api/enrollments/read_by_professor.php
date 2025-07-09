<?php
require_once '../../includes/session_handler.php';
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

$loggedInUserId = $_SESSION['user_id'];

try {
    $query = "
        SELECT 
            e.enrollment_id, 
            e.class_id, 
            e.user_id,
            e.roleOfClass,
            c.className, 
            c.courseCode, 
            u.username, 
            u.admin,
            creator_user.username AS createdByUsername 
        FROM enrollment e
        JOIN class c ON e.class_id = c.class_id
        JOIN user u ON e.user_id = u.user_id
        LEFT JOIN user creator_user ON c.createdBy = creator_user.user_id
        WHERE 
            e.class_id IN (
                SELECT class_id 
                FROM enrollment 
                WHERE user_id = ? 
            )
            AND e.user_id != ?
            AND e.user_id != c.createdBy
    ";

    $stmt = $connection->prepare($query);
    // Bind the loggedInUserId twice for the two placeholders in the WHERE clause
    $stmt->bind_param("ii", $loggedInUserId, $loggedInUserId); 
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Query failed: " . $stmt->error);
    }

    $enrollments = [];
    while ($row = $result->fetch_assoc()) {
        $enrollments[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $enrollments
    ]);

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching enrollments: ' . $e->getMessage()
    ]);
} finally {
    if (isset($connection)) {
        $connection->close();
    }
}
?>