<?php
require_once '../../../includes/session_handler.php';
require_once '../../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

$loggedInUserId = $_SESSION['user_id'];

try {
    $stmt = $connection->prepare("
        SELECT 
            c.*, 
            u.username AS createdByUsername 
        FROM class c
        JOIN enrollment e ON c.class_id = e.class_id
        JOIN user u ON c.createdBy = u.user_id
        WHERE e.user_id = ?
    ");
    $stmt->bind_param("i", $loggedInUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Query failed: " . $stmt->error);
    }

    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $classes
    ]);

    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching classes: ' . $e->getMessage()
    ]);
} finally {
    if (isset($connection)) {
        $connection->close();
    }
}
?>