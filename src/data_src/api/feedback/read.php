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
    $classFilter = $_GET['class_id'] ?? 'All';
    $userFilter = $_GET['user_id'] ?? 'All';
    $startDate = $_GET['start_date'] ?? null;
    if ($startDate === 'null') {
        $startDate = null; // Handle 'null' string as null
    }
    $endDate = $_GET['end_date'] ?? null;
    $ogEndDate = $endDate; // Store original end date for later use
    if ($endDate === 'null') {
        $endDate = null; // Handle 'null' string as null
    } else {
        $endDate = $endDate . ' 23:59:59';
    }
    $feedbackRating = $_GET['rating'] ?? null;

    if (!$feedbackRating) {
        echo json_encode(['success' => false, 'message' => 'Missing feedback rating']);
        exit;
    }

    $conditions = [];
    $paramTypes = 'si'; // feedbackRating + loggedInUserId
    $params = [$feedbackRating, $loggedInUserId];

    if ($userFilter !== 'All') {
        $conditions[] = "AND user_id = ?";
        $paramTypes .= 'i';
        $params[] = $userFilter;
    }

    if ($classFilter !== 'All') {
        $conditions[] = "AND class_id = ?";
        $paramTypes .= 'i';
        $params[] = $classFilter;
    }

    if ($startDate && $endDate) {
        $dateCondition = "AND ai.feedbackTimestamp BETWEEN ? AND ?";
        $paramTypes .= 'ss';
        $params[] = $startDate;
        $params[] = $endDate;
    } elseif ($startDate) {
        $dateCondition = "AND ai.feedbackTimestamp >= ?";
        $paramTypes .= 's';
        $params[] = $startDate;
    } elseif ($endDate) {
        $dateCondition = "AND ai.feedbackTimestamp <= ?";
        $paramTypes .= 's';
        $params[] = $endDate;
    } else {
        $dateCondition = "";
    }

    // Assemble user/class subconditions
    $userClassConditions = implode(" ", $conditions);

    $sql = "
        SELECT 
            ai.message_id,
            ai.question,
            ai.answer,
            ai.feedbackRating,
            ai.feedbackExplanation,
            ai.feedbackTimestamp,
            u.username,
            ai.timestamp AS messageTimestamp
        FROM ai_messages ai
        JOIN enrollment e ON ai.enrollment_id = e.enrollment_id
        JOIN user u ON e.user_id = u.user_id
        WHERE ai.feedbackRating = ?
        AND ai.enrollment_id IN (
            SELECT enrollment_id
            FROM enrollment
            WHERE class_id IN (
                SELECT class_id
                FROM enrollment
                WHERE user_id = ?
            )
            $userClassConditions
        )
        $dateCondition
        ORDER BY ai.feedbackTimestamp DESC;
    ";

    $stmt = $connection->prepare($sql);
    $stmt->bind_param($paramTypes, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $entries = [];

    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }

    echo json_encode(['success' => true, 'entries' => $entries]);




} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching feedback: ' . $e->getMessage()
    ]);
} finally {
    if (isset($connection)) {
        $connection->close();
    }
}
?>