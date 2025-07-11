<?php
require_once '../data_src/includes/session_handler.php';
require_once '../data_src/includes/db_connect.php';

$isUserLoggedIn = isLoggedIn();
$userEmail = '';
$userId = '';
$username = '';

if ($isUserLoggedIn) {
    $userId = $_SESSION['user_id'];
    $stmt = $connection->prepare("SELECT email, username, firstName, lastName FROM User WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $userEmail = $row['email'];
        $username = $row['username'];
        $fullName = $row['firstName'] . ' ' . $row['lastName'];
    }
    $stmt->close();
}

// Handle new message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $messageContent = $_POST['message'];
    $chatId = $_POST['chat_id'];

    // Validate that the user is a participant of the chat before inserting the message
    $stmt = $connection->prepare("
        SELECT 1 
        FROM Chat_Participant 
        WHERE chat_id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $chatId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // User is not part of this chat, redirect them or show an error
        header("Location: index.php"); // Redirect to a safe page
        exit();
    }

    // Insert the message if validation passes
    $stmt = $connection->prepare("INSERT INTO Messages (chat_id, sender_id, messageContent) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $chatId, $userId, $messageContent);
    $stmt->execute();
    $stmt->close();
    
    header("Location: ?chat_id=" . $chatId);
    exit();
}

// Handle new chat creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_chat') {
    $chatName = $_POST['chat_name'];
    $chatDescription = $_POST['chat_description'];
    $participants = isset($_POST['participants']) ? $_POST['participants'] : [];
    
    $connection->begin_transaction();
    try {
        // Get new chat_id
        $result = $connection->query("SELECT MAX(chat_id) as max_id FROM Chat");
        $row = $result->fetch_assoc();
        $newChatId = ($row['max_id'] ?? 0) + 1;
        
        // Create new chat
        $stmt = $connection->prepare("INSERT INTO Chat (chat_id, chatName, chatDescription) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $newChatId, $chatName, $chatDescription);
        $stmt->execute();
        
        // Get new participant_id base
        $result = $connection->query("SELECT MAX(participant_id) as max_id FROM Chat_Participant");
        $row = $result->fetch_assoc();
        $newParticipantId = ($row['max_id'] ?? 0) + 1;
        
        // Add creator as a participant
        $currentTime = date('Y-m-d H:i:s');
        $stmt = $connection->prepare("INSERT INTO Chat_Participant (participant_id, chat_id, user_id, joinedAt) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $newParticipantId, $newChatId, $userId, $currentTime);
        $stmt->execute();
        
        // Add selected participants
        foreach ($participants as $participantId) {
            $newParticipantId++;
            $stmt = $connection->prepare("INSERT INTO Chat_Participant (participant_id, chat_id, user_id, joinedAt) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $newParticipantId, $newChatId, $participantId, $currentTime);
            $stmt->execute();
        }
        
        // Add a system message
        $systemMessage = "Chat created by " . $fullName;
        $stmt = $connection->prepare("INSERT INTO Messages (chat_id, sender_id, messageContent) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $newChatId, $userId, $systemMessage);
        $stmt->execute();
        
        $connection->commit();
        header("Location: ?chat_id=" . $newChatId);
        exit();
    } catch (Exception $e) {
        $connection->rollback();
        $error = "Failed to create chat: " . $e->getMessage();
    }
}

if (isset($_GET['chat_id']) && filter_var($_GET['chat_id'], FILTER_VALIDATE_INT)) {
    $currentChat = (int) $_GET['chat_id'];
    
    // Validate that the user is a participant of the chat
    $stmt = $connection->prepare("
        SELECT 1 
        FROM Chat_Participant 
        WHERE chat_id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $currentChat, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // If no result, user is not a participant of the chat
        header("Location: index.php"); // Redirect to a safe page, like the home page
        exit();
    }
} else {
    $currentChat = null;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>

    <!-- tailwind css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">

    <!-- bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- custom css -->
    <link rel="stylesheet" href="../css/message.css">

</head>
<body>
    <header>
        <?php include '../components/navbar.php'; ?>
    </header>  
    <div style="height: 56px;"></div>
    <main class="flex flex-col h-screen gap-0 overflow-hidden">
        <div id="my-content" class="flex flex-row flex-grow w-full mt-0 overflow-hidden">
            
            <!-- Left sidebar with chats list -->
            <div id="left-sidebar" class="d-flex flex-column flex-shrink-0 bg-gray-100 h-full overflow-hidden w-full">
                <div class="flex justify-between items-center mb-4 px-3 pt-3">
                    <!-- Left: Chats Heading -->
                    <h2 class="text-xl font-bold">Chats</h2>

                    <!-- Right: New Chat Button -->
                    <button onclick="showNewChatModal()" class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm">
                        New Chat
                    </button>
                </div>

                <!-- List of existing chats -->
                <div id="sidebar-chats" class="space-y-2 flex-grow ps-3 pb-3 overflow-y-auto overflow-x-hidden left-sidebar-content-hide p-sidebar-noshow">
                    <div id="chat-div" class="d-grid gap-2">
                        <?php
                            $stmt = $connection->prepare("
                            SELECT DISTINCT c.chat_id, c.chatName, c.chatDescription
                            FROM Chat c
                            LEFT JOIN Messages m ON c.chat_id = m.chat_id
                            LEFT JOIN Chat_Participant cp ON c.chat_id = cp.chat_id
                            WHERE cp.user_id = ?
                            ORDER BY c.chat_id DESC
                            ");
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $chats = $stmt->get_result();
                            
                            if (isset($error)) {
                                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">';
                                echo '<strong class="font-bold">Error!</strong>';
                                echo '<span class="block sm:inline"> ' . htmlspecialchars($error) . '</span>';
                                echo '</div>';
                            }

                            while ($chat = $chats->fetch_assoc()):
                        ?>
                            <a href="?chat_id=<?php echo htmlspecialchars($chat['chat_id'], ENT_QUOTES, 'UTF-8'); ?>" 
                            class="flex items-center justify-between p-3 rounded hover:bg-gray-200 <?php echo $currentChat == $chat['chat_id'] ? 'bg-gray-200' : ''; ?> message-container">

                                <!-- Left: Chat name and description -->
                                <div class="flex-1 overflow-hidden">
                                    <div class="font-medium truncate"><?php echo htmlspecialchars($chat['chatName'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php if ($chat['chatDescription']): ?>
                                        <div class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($chat['chatDescription'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Right: Delete button (uses JavaScript to prevent following the link) -->
                                <button 
                                    type="button" 
                                    onclick="event.stopPropagation(); event.preventDefault(); deleteChat(<?php echo $chat['chat_id']; ?>);" 
                                    class="ml-2 text-blue-500 hover:text-blue-700 px-2 py-1 rounded"
                                    title="Delete Chat"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Main chat area JAYWING -->
            <div id="chat-container" class="flex flex-col bg-white overflow-hidden">
                <?php if ($currentChat): ?>
                    <?php
                    // Get chat details
                    $stmt = $connection->prepare("SELECT * FROM Chat WHERE chat_id = ?");
                    $stmt->bind_param("i", $currentChat);
                    $stmt->execute();
                    $chatDetails = $stmt->get_result()->fetch_assoc();
                    ?>
                    
                        <!-- Chat header -->
                        <div id="chat-header" class="flex items-center justify-between p-3 w-full border-b-4 border-gray-50">
                            <h2 class="text-xl font-bold text-left m-0"><?php echo htmlspecialchars($chatDetails['chatName'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            
                            <!-- Button to show participants modal positioned right of the text -->
                            <button onclick="showParticipantsModal()" class="bg-green-500 text-white px-3 py-1 rounded-full text-sm">
                                View Participants
                            </button>
                        </div>

                        <!-- Participants Modal -->
                        <div id="participantsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center" style="z-index: 1050;">
                            <div class="bg-white rounded-lg p-6 w-[32rem]">
                                <h3 class="text-xl font-bold mb-4">Chat Participants</h3>
                                
                                <!-- List of participants -->
                                <div class="space-y-2">
                                    <?php
                                    // Fetch participants for the current chat
                                    $stmt = $connection->prepare("
                                        SELECT u.firstName, u.lastName, u.username
                                        FROM Chat_Participant cp
                                        JOIN User u ON cp.user_id = u.user_id
                                        WHERE cp.chat_id = ?
                                    ");
                                    $stmt->bind_param("i", $currentChat);
                                    $stmt->execute();
                                    $participants = $stmt->get_result();
                                    
                                    while ($participant = $participants->fetch_assoc()):
                                    ?>
                                    <div class="p-2 border-b">
                                        <div class="font-medium">
                                            <?php echo htmlspecialchars($participant['firstName'] . ' ' . $participant['lastName']); ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            (@<?php echo htmlspecialchars($participant['username']); ?>)
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>

                                <!-- Close button -->
                                <button onclick="hideParticipantsModal()" class="bg-red-500 text-white px-4 py-2 rounded-lg mt-4">
                                    Close
                                </button>
                            </div>
                        </div>
                        
                        <!-- Messages area -->
                        <div id="conversation" class="flex-1 overflow-y-auto space-y-4 -mb-3 -mt-2 w-full p-chat-noshow">
                            <div id="chat-location" class="sm:px-3 md:px-12 lg:px-24 xl:px-36">
                                <?php
                                $stmt = $connection->prepare("
                                    SELECT m.*, u.username, u.firstName, u.lastName
                                    FROM Messages m
                                    JOIN User u ON m.sender_id = u.user_id
                                    WHERE m.chat_id = ?
                                    ORDER BY m.message_id ASC
                                ");
                                $stmt->bind_param("i", $currentChat);
                                $stmt->execute();
                                $messages = $stmt->get_result();
                                
                                while ($message = $messages->fetch_assoc()):
                                    $isOwnMessage = $message['sender_id'] == $userId;
                                ?>
                                    <div data-message-id="<?php echo $message['message_id']; ?>" class="flex pb-2 <?php echo $isOwnMessage ? 'justify-end' : 'justify-start'; ?>">
                                        <div class="max-w-2xl <?php echo $isOwnMessage ? 'bg-blue-500 text-white' : 'bg-gray-100'; ?> rounded-lg p-2">
                                            <?php if (!$isOwnMessage): ?>
                                                <div class="text-sm font-medium <?php echo $isOwnMessage ? 'text-white' : 'text-gray-900'; ?>">
                                                    <?php echo htmlspecialchars($message['firstName'] . ' ' . $message['lastName']); ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-sm font-medium <?php echo $isOwnMessage ? 'text-white' : 'text-gray-900'; ?>">
                                                    You
                                                </div>
                                            <?php endif; ?>
                                            <div><?php echo nl2br(htmlspecialchars($message['messageContent'])); ?></div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        
                        <!-- Message input -->
                        <form id="message-input" method="POST" name="messageForm" class="mt-auto w-full pr-4 pb-3">
                            <input type="hidden" name="action" value="send_message">
                            <input type="hidden" name="chat_id" value="<?php echo $currentChat; ?>">

                            <!-- Wrap textarea in a relative container -->
                            <div id="input-container" class="flex gap-2 sm:px-3 md:px-12 lg:px-24 xl:px-36 w-full">
                                <div class="relative w-full">
                                <!-- Textarea -->
                                <textarea
                                    id="student-message"
                                    name="message"
                                    class="w-full border rounded-lg py-2 pr-12 m-0 break-words resize-none max-h-48"
                                    placeholder="Type your message..."
                                    rows="1"
                                    required
                                ></textarea>

                                <!-- Floating Send Button -->
                                <button id="send-button" type="submit" class="absolute bottom-1.5 right-4 p-0 bg-transparent transition-opacity duration-150">
                                    <div id="send-circle" class="w-8 h-8 flex items-center justify-center rounded-full
                                                border-2 border-blue-600 text-blue-600 shadow-md
                                                hover:bg-blue-600 hover:text-white
                                                transition-colors duration-200">
                                    <i id="send-icon" class="fas fa-arrow-up"></i>
                                    </div>
                                </button>
                                </div>
                            </div>
                        </form>
                <?php else: ?>
                    <div class="pt-4 flex items-center justify-center text-gray-500">
                        Select or create a chat to start messaging
                    </div>
                    <img src="../images/etownEngineeringCSSticker.png" alt="ETown CS Sticker" class="h-full object-contain">
                <?php endif; ?>
            </div>  
        </div>
    </main>

    <!-- New Chat Modal -->
    <div id="newChatModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center" style="z-index: 1050;">
        <div class="bg-white rounded-lg p-6 w-[32rem]">
            <h3 class="text-xl font-bold mb-4">New Chat</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create_chat">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Chat Name
                    </label>
                    <input 
                        type="text" 
                        name="chat_name" 
                        required
                        class="w-full border rounded-lg p-2"
                    >
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Description (optional)
                    </label>
                    <textarea 
                        name="chat_description"
                        class="w-full border rounded-lg p-2 break-words"
                        rows="3"
                    ></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Add Participants
                    </label>
                    <div class="border rounded-lg p-2 max-h-48 overflow-y-auto">
                        <?php
                        // Fetch all users except current user
                        $stmt = $connection->prepare("
                            SELECT user_id, username, firstName, lastName 
                            FROM User 
                            WHERE user_id != ?
                            ORDER BY firstName, lastName
                        ");
                        $stmt->bind_param("i", $userId);
                        $stmt->execute();
                        $users = $stmt->get_result();
                        
                        while ($user = $users->fetch_assoc()):
                        ?>
                            <div class="flex items-center p-2 hover:bg-gray-50">
                                <input 
                                    type="checkbox"
                                    name="participants[]"
                                    value="<?php echo $user['user_id']; ?>"
                                    class="mr-3"
                                >
                                <label>
                                    <?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?>
                                    <span class="text-sm text-gray-500">
                                        (@<?php echo htmlspecialchars($user['username']); ?>)
                                    </span>
                                </label>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button 
                        type="button"
                        onclick="hideNewChatModal()"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-100"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                    >
                        Create Chat
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div style="height: 58px;"></div> <!-- Spacer for fixed footer - footer is 59px high -->
    <footer id="footer"></footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="../js/global.js"></script>
    <script src="../js/chat.js"></script>
</body>
</html>