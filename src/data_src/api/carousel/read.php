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
    $stats = [];

    // Get filters
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

    $paramTypes = 'i'; // For loggedInUserId
    $params = [$loggedInUserId];

    // Construct filters
    if ($userFilter === 'All') {
        $userCondition = "";
    } else {
        $userCondition = "AND user_id = ?";
        $paramTypes .= 'i';
        $params[] = $userFilter;
    }

    if ($classFilter === 'All') {
        $classCondition = "";
    } else {
        $classCondition = "AND class_id = ?";
        $paramTypes .= 'i';
        $params[] = $classFilter;
    }

    if ($startDate && $endDate) {
        $dateCondition = "AND m.timestamp BETWEEN ? AND ?";
        $paramTypes .= 'ss';
        $params[] = $startDate;
        $params[] = $endDate;
    } elseif ($startDate) {
        $dateCondition = "AND m.timestamp >= ?";
        $paramTypes .= 's';
        $params[] = $startDate;
    } elseif ($endDate) {
        $dateCondition = "AND m.timestamp <= ?";
        $paramTypes .= 's';
        $params[] = $endDate;
    } else {
        $dateCondition = "";
    }

    // Query for message counts
    $messageCounts = "SELECT 
            SUM(CASE WHEN ai.feedbackRating = 'up' THEN 1 ELSE 0 END) AS liked_messages,
            SUM(CASE WHEN ai.feedbackRating = 'down' THEN 1 ELSE 0 END) AS disliked_messages,
            COUNT(*) AS message_count
        FROM ai_messages ai
        WHERE ai.enrollment_id IN (
            SELECT enrollment_id
            FROM enrollment
            WHERE class_id IN (
                SELECT class_id
                FROM enrollment
                WHERE user_id = ? -- prof user
            )
            $userCondition
            $classCondition
        )
        $dateCondition
    ";

    $stmt = $connection->prepare($messageCounts);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $connection->error);
    }
    $stmt->bind_param($paramTypes, ...$params);
    if (!$stmt->execute()) {
        throw new Exception("Execution failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Query failed: " . $stmt->error);
    }
    $row = $result->fetch_assoc();
    $stats['message_counts'] = [
        'liked_messages' => (int)$row['liked_messages'],
        'disliked_messages' => (int)$row['disliked_messages'],
        'message_count' => (int)$row['message_count']
    ];
    $stmt->close();

    // Determine which query to do next based on date filters
    if ($startDate === $ogEndDate && $endDate !== null) { // Make sure they are equal and not null
        // Query for most active hour
        $mostActiveHourQuery = "
            SELECT 
                DATE_FORMAT(ai.timestamp, '%Y-%m-%d %H:00:00') AS message_hour, COUNT(*) AS total_messages
            FROM ai_messages ai
            WHERE ai.enrollment_id IN (
                SELECT enrollment_id
                FROM enrollment
                WHERE class_id IN (
                    SELECT class_id
                    FROM enrollment
                    WHERE user_id = ? -- prof user
                )
                $userCondition
                $classCondition
            )
            $dateCondition
            GROUP BY message_hour
            ORDER BY total_messages DESC
            LIMIT 1;
        ";

        $stmt = $connection->prepare($mostActiveHourQuery);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $connection->error);
        }
        $stmt->bind_param($paramTypes, ...$params);
        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Query failed: " . $stmt->error);
        }
        $mostActiveHour = $result->fetch_assoc();
        $stats['most_active_hour'] = [
            'hour' => $mostActiveHour['message_hour'],
            'total_messages' => (int)$mostActiveHour['total_messages']
        ];
        $stmt->close();
    } else {
        // Query for most active day
        $mostActiveDayQuery = "
            SELECT 
                DATE(ai.timestamp) AS message_day, COUNT(*) AS total_messages
            FROM ai_messages ai
            WHERE ai.enrollment_id IN (
                SELECT enrollment_id
                FROM enrollment
                WHERE class_id IN (
                    SELECT class_id
                    FROM enrollment
                    WHERE user_id = ? -- prof user
                )
                $userCondition
                $classCondition
            )
            $dateCondition
            GROUP BY message_day
            ORDER BY total_messages DESC
            LIMIT 1;
        ";

        $stmt = $connection->prepare($mostActiveDayQuery);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $connection->error);
        }
        $stmt->bind_param($paramTypes, ...$params);
        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Query failed: " . $stmt->error);
        }
        $mostActiveDay = $result->fetch_assoc();
        $stats['most_active_day'] = [
            'day' => $mostActiveDay['message_day'],
            'total_messages' => (int)$mostActiveDay['total_messages']
        ];
        $stmt->close();
    }
    
    // Determine which query to do next based on class and user filters
    if ($userFilter !== 'All' && $classFilter !== 'All') {
        // Average words per message for user in a specific class
        $avgMessageQuery = "
            SELECT
                ROUND(AVG(CHAR_LENGTH(ai.question) - CHAR_LENGTH(REPLACE(ai.question, ' ', '')) + 1), 1) AS student_avg_words,
                (
                    SELECT ROUND(AVG(CHAR_LENGTH(ai2.question) - CHAR_LENGTH(REPLACE(ai2.question, ' ', '')) + 1), 1)
                    FROM ai_messages ai2
                    JOIN enrollment e2 ON ai2.enrollment_id = e2.enrollment_id
                    WHERE e2.class_id = ?
                ) AS class_avg_words
            FROM ai_messages ai
            JOIN enrollment e ON ai.enrollment_id = e.enrollment_id
            WHERE e.user_id = ? AND e.class_id = ?
            ";

        $stmt = $connection->prepare($avgMessageQuery);
        $stmt->bind_param("iii", $classFilter, $userFilter, $classFilter);
        $stmt->execute();
        $result = $stmt->get_result();
        $avgMessages = $result->fetch_assoc();
        
        $stats['average_words_per_message'] = [
            'student_avg_words' => (float)$avgMessages['student_avg_words'],
            'class_avg_words' => (float)$avgMessages['class_avg_words']
        ];

        $stmt->close();
    } else if ($userFilter === 'All' && $classFilter !== 'All') {
        // Only class filter is applied so multiple users can be queried
        // Query for most active user in a specific class
        $mostActiveUserQuery = "
            SELECT 
                u.user_id AS user_id,
                u.username AS user_name,
                COUNT(ai.message_id) AS total_messages
            FROM ai_messages ai
            JOIN enrollment e ON ai.enrollment_id = e.enrollment_id
            JOIN user u ON e.user_d = u.user_id
            WHERE e.class_id IN (
                SELECT class_id
                FROM enrollment
                WHERE user_id = ? -- professor's user ID
            ) -- Maybe not necessary
            $classCondition
            $dateCondition
            GROUP BY u.user_id, u.username
            ORDER BY total_messages DESC
            LIMIT 1;
        ";
        $stmt = $connection->prepare($mostActiveUserQuery);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $connection->error);
        }
        $stmt->bind_param($paramTypes, ...$params);
        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Query failed: " . $stmt->error);
        }
        $mostActiveUser = $result->fetch_assoc();
        $stats['most_active_user'] = [
            'user_id' => (int)$mostActiveUser['user_id'],
            'user_name' => $mostActiveUser['user_name'],
            'total_messages' => (int)$mostActiveUser['total_messages']
        ];
        $stmt->close();
    } else {
        // No specific filters applied, most active class query
        $mostActiveClassQuery = "
            SELECT 
                c.className AS class_name, COUNT(*) AS total_messages
            FROM ai_messages ai
            JOIN enrollment e ON ai.enrollment_id = e.enrollment_id
            JOIN class c ON e.class_id = c.class_id
            WHERE ai.enrollment_id IN (
                SELECT enrollment_id
                FROM enrollment
                WHERE class_id IN (
                    SELECT class_id
                    FROM enrollment
                    WHERE user_id = ? -- prof user
                )
                $userCondition
                $classCondition
            )
            $dateCondition
            GROUP BY c.class_id
            ORDER BY total_messages DESC
            LIMIT 1;
        ";
        $stmt = $connection->prepare($mostActiveClassQuery);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $connection->error);
        }
        $stmt->bind_param($paramTypes, ...$params);
        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Query failed: " . $stmt->error);
        }
        $mostActiveClass = $result->fetch_assoc();
        $stats['most_active_class'] = [
            'class_name' => $mostActiveClass['class_name'],
            'total_messages' => (int)$mostActiveClass['total_messages']
        ];
        $stmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching carousel: ' . $e->getMessage()
    ]);
} finally {
    if (isset($connection)) {
        $connection->close();
    }
}
?>