<?php
require_once '../../includes/session_handler.php';
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

try {
    if (!isLoggedIn()) {
        http_response_code(403);
        throw new Exception("Not authorized");
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['chatId'])) {
        http_response_code(400);
        throw new Exception("chatId is required");
    }

    $chatId = intval($data['chatId']);
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        http_response_code(403);
        throw new Exception("User not authenticated");
    }

    $connection->begin_transaction();

    // Count participants
    $stmt = $connection->prepare("SELECT COUNT(*) FROM Chat_Participant WHERE chat_id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $connection->error);
    $stmt->bind_param("i", $chatId);
    $stmt->execute();
    $stmt->bind_result($participantCount);
    $stmt->fetch();
    $stmt->close();

    if ($participantCount > 1) {
        // Just leave the chat
        $stmt = $connection->prepare("DELETE FROM Chat_Participant WHERE chat_id = ? AND user_id = ?");
        if (!$stmt) throw new Exception("Prepare failed: " . $connection->error);
        $stmt->bind_param("ii", $chatId, $userId);
        $stmt->execute();
        $stmt->close();

        $connection->commit();

        echo json_encode([
            'success' => true,
            'message' => 'You have left the chat.'
        ]);
    } else {
        // Delete everything: messages, participants, chat
        $stmt = $connection->prepare("DELETE FROM Messages WHERE chat_id = ?");
        if (!$stmt) throw new Exception("Prepare failed: " . $connection->error);
        $stmt->bind_param("i", $chatId);
        $stmt->execute();
        $stmt->close();

        $stmt = $connection->prepare("DELETE FROM Chat_Participant WHERE chat_id = ?");
        if (!$stmt) throw new Exception("Prepare failed: " . $connection->error);
        $stmt->bind_param("i", $chatId);
        $stmt->execute();
        $stmt->close();

        $stmt = $connection->prepare("DELETE FROM Chat WHERE chat_id = ?");
        if (!$stmt) throw new Exception("Prepare failed: " . $connection->error);
        $stmt->bind_param("i", $chatId);
        $stmt->execute();
        $stmt->close();

        $connection->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Chat deleted because you were the last participant.'
        ]);
    }

} catch (Exception $e) {
    $connection->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($connection)) {
        $connection->close();
    }
}