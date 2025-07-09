<?php
require_once '../../includes/session_handler.php';
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

// Ensure user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'] ?? null;

// Get request method and parse input
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? $input['action'] ?? null;

switch ($action) {
    case 'get':
        // Return archived courses
        $stmt = $connection->prepare("
            SELECT c.className AS className, e.enrollment_id
            FROM Enrollment e
            JOIN Class c ON c.class_id = e.class_id
            WHERE e.user_id = ? AND e.archived = 1
            ORDER BY c.className ASC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $archivedCourses = [];
        while ($row = $result->fetch_assoc()) {
            $archivedCourses[] = [
                'id' => $row['enrollment_id'],
                'name' => $row['className']
            ];
        }

        echo json_encode($archivedCourses);
        break;

    case 'restore':
        // Change archived status to false (0)
        $name = $input['courseName'] ?? null;
        if (!$name) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing Name']);
            break;
        }

        // Get enrollment_id based on course name and userId
        $stmt = $connection->prepare("
            SELECT e.enrollment_id, c.class_id AS class_id
            FROM Enrollment e
            JOIN Class c ON e.class_id = c.class_id
            WHERE c.className = ? AND e.user_id = ? AND e.archived = 1
            LIMIT 1
        ");
        $stmt->bind_param("si", $name, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Course not found or already active']);
            break;
        }

        $enrollment_id = $row['enrollment_id'];
        $class_id = $row['class_id'];

        // Update archived status to false (0)
        $stmt = $connection->prepare("
            UPDATE enrollment SET archived = 0 WHERE enrollment_id = ?
        ");
        $stmt->bind_param("i", $enrollment_id);
        $success = $stmt->execute();

        // Get latest message from course
        $stmt = $connection->prepare("
            SELECT question FROM ai_messages
            WHERE enrollment_id = ?
            ORDER BY timestamp DESC
            LIMIT 1
        ");
        $stmt->bind_param("i", $enrollment_id);
        $stmt->execute();
        $msgResult = $stmt->get_result();
        $messageRow = $msgResult->fetch_assoc();
        $latestMessage = $messageRow['question'] ?? null;

        // Return relevant information for the restored course
        echo json_encode([
            'success' => $success,
            'enrollment_id' => $enrollment_id,
            'latestMessage' => $latestMessage,
            'className' => $name
        ]);
        break;

    case 'archive':
        // Change archived status to true (1)
        $id = $input['enrollment_id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing ID']);
            break;
        }

        $stmt = $connection->prepare("
            UPDATE enrollment SET archived = 1
            WHERE enrollment_id = ? AND user_id = ?
        ");
        $stmt->bind_param("ii", $id, $userId);
        $success = $stmt->execute();
        echo json_encode(['success' => $success]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
