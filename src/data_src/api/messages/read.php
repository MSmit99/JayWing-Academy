<?php

require_once '../data_src/includes/session_handler.php';
require_once '../data_src/includes/db_connect.php';

if (!isLoggedIn()) {
    http_response_code(401);
    exit(json_encode(['error' => 'Not authenticated']));
}

$chatId = $_GET['chat_id'] ?? null;
$lastMessageId = $_GET['last_message_id'] ?? 0;
$userId = $_SESSION['user_id'];

if (!$chatId) {
    http_response_code(400);
    exit(json_encode(['error' => 'Chat ID required']));
}

// Verify user is participant in chat
$stmt = $connection->prepare("
    SELECT 1 FROM Chat_Participant 
    WHERE chat_id = ? AND user_id = ?
");
$stmt->bind_param("ii", $chatId, $userId);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) {
    http_response_code(403);
    exit(json_encode(['error' => 'Not authorized']));
}

// Get new messages
$stmt = $connection->prepare("
    SELECT 
        m.message_id,
        m.messageContent,
        m.sender_id,
        u.firstName,
        u.lastName
    FROM Messages m
    JOIN User u ON m.sender_id = u.user_id
    WHERE m.chat_id = ? AND m.message_id > ?
    ORDER BY m.message_id ASC
");
$stmt->bind_param("ii", $chatId, $lastMessageId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'message_id' => $row['message_id'],
        'messageContent' => $row['messageContent'],
        'firstName' => $row['firstName'],
        'lastName' => $row['lastName'],
        'isOwnMessage' => $row['sender_id'] == $userId
    ];
}

echo json_encode(['messages' => $messages]);