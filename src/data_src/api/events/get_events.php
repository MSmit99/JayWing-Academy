<?php
// /src/data_src/api/events/get_events.php
require_once '../../includes/session_handler.php';
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

try {
    $events = [];
    $query = "
        SELECT DISTINCT 
            e.event_id,
            e.event_name,
            e.location,
            e.start,
            e.end,
            et.type_name,
            et.wings
        FROM Event e
        JOIN Event_Type et ON e.event_type_id = et.event_type_id
    ";
    
    if (isLoggedIn()) {
        $stmt = $connection->prepare($query);
    } else {
        $stmt = $connection->prepare($query);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'id' => $row['event_id'],
            'title' => $row['event_name'],
            'location' => $row['location'],
            'start' => date('Y-m-d\TH:i:s', strtotime($row['start'])),
            'end' => date('Y-m-d\TH:i:s', strtotime($row['end'])),
            'allDay' => false,
            'extendedProps' => [
                'type' => $row['type_name'],
                'wings' => $row['wings']
            ]
        ];
    }
    
    echo json_encode($events);
    
} catch (Exception $e) {
    error_log("Error in get_events.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error fetching events: ' . $e->getMessage()
    ]);
}

$connection->close();