<?php
// delete_class.php
require_once '../../includes/session_handler.php';
require_once '../../includes/db_connect.php';

if (!isAdmin()) {
    http_response_code(403);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

// First delete related enrollments
$stmt = $connection->prepare("DELETE FROM Enrollment WHERE class_id = ?");
$stmt->bind_param("i", $data['class_id']);
$stmt->execute();

// Then delete the class
$stmt = $connection->prepare("DELETE FROM Class WHERE class_id = ?");
$stmt->bind_param("i", $data['class_id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => $connection->error]);
}