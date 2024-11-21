<?php
// /src/data_src/api/classes/get_classes.php
require_once '../../includes/session_handler.php';
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

try {
    // Get classes with their details
    $query = "
        SELECT 
            c.class_id, 
            c.className, 
            c.courseCode, 
            c.classDescription
        FROM Class c
    ";
    
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $classes_result = $stmt->get_result();
    $classes = [];

    while ($class = $classes_result->fetch_assoc()) {
        // Get tutors for each class
        $tutor_query = "
            SELECT 
                u.user_id, 
                u.firstName, 
                u.lastName, 
                u.email
            FROM User u
            JOIN Enrollment e ON u.user_id = e.user_id
            WHERE e.class_id = ? AND e.roleOfClass = 'Tutor'
        ";
        
        $tutor_stmt = $connection->prepare($tutor_query);
        $tutor_stmt->bind_param("i", $class['class_id']);
        $tutor_stmt->execute();
        $tutors_result = $tutor_stmt->get_result();
        
        $class['tutors'] = $tutors_result->fetch_all(MYSQLI_ASSOC);
        $classes[] = $class;
        
        $tutor_stmt->close();
    }

    echo json_encode([
        'success' => true,
        'classes' => $classes,
        'current_user_id' => isLoggedIn() ? getCurrentUserId() : null
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error fetching classes: ' . $e->getMessage()
    ]);
}

$connection->close();