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
    
    $connection->begin_transaction();
    try {
        // Get new chat_id (since it's not AUTO_INCREMENT in your schema)
        $result = $connection->query("SELECT MAX(chat_id) as max_id FROM Chat");
        $row = $result->fetch_assoc();
        $newChatId = ($row['max_id'] ?? 0) + 1;
        
        // Create new chat
        $stmt = $connection->prepare("INSERT INTO Chat (chat_id, chatName, chatDescription) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $newChatId, $chatName, $chatDescription);
        $stmt->execute();
        
        $connection->commit();
        header("Location: ?chat_id=" . $newChatId);
        exit();
    } catch (Exception $e) {
        $connection->rollback();
        $error = "Failed to create chat";
    }
}

$currentChat = isset($_GET['chat_id']) ? $_GET['chat_id'] : null;
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

    <!-- custom css -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <header>
        <?php include '../components/navbar.php'; ?>
    </header>  

    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-4 gap-6">
            <!-- Sidebar with chats list -->
            <div class="col-span-1 bg-white rounded-lg shadow-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Chats</h2>
                    <button onclick="showNewChatModal()" class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm">
                        New Chat
                    </button>
                </div>
                
                <!-- List of existing chats -->
                <div class="space-y-2">
                    <?php
                    $stmt = $connection->prepare("
                        SELECT DISTINCT c.chat_id, c.chatName, c.chatDescription
                        FROM Chat c
                        JOIN Messages m ON c.chat_id = m.chat_id
                        WHERE m.sender_id = ? OR EXISTS (
                            SELECT 1 FROM Messages 
                            WHERE chat_id = c.chat_id 
                            AND sender_id = ?
                        )
                        ORDER BY c.chat_id DESC
                    ");
                    $stmt->bind_param("ii", $userId, $userId);
                    $stmt->execute();
                    $chats = $stmt->get_result();
                    
                    while ($chat = $chats->fetch_assoc()):
                    ?>
                        <a href="?chat_id=<?php echo $chat['chat_id']; ?>" 
                           class="block p-3 rounded hover:bg-gray-100 <?php echo $currentChat == $chat['chat_id'] ? 'bg-gray-100' : ''; ?>">
                            <div class="font-medium"><?php echo htmlspecialchars($chat['chatName']); ?></div>
                            <?php if ($chat['chatDescription']): ?>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($chat['chatDescription']); ?></div>
                            <?php endif; ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <!-- Main chat area -->
            <div class="col-span-3 bg-white rounded-lg shadow-lg p-4">
                <?php if ($currentChat): ?>
                    <?php
                    // Get chat details
                    $stmt = $connection->prepare("SELECT * FROM Chat WHERE chat_id = ?");
                    $stmt->bind_param("i", $currentChat);
                    $stmt->execute();
                    $chatDetails = $stmt->get_result()->fetch_assoc();
                    ?>
                    
                    <div class="flex flex-col h-[600px]">
                        <!-- Chat header -->
                        <div class="border-b pb-4 mb-4">
                            <h2 class="text-xl font-bold"><?php echo htmlspecialchars($chatDetails['chatName']); ?></h2>
                            <?php if ($chatDetails['chatDescription']): ?>
                                <div class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($chatDetails['chatDescription']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Messages area -->
                        <div class="flex-1 overflow-y-auto mb-4 space-y-4">
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
                                <div class="flex <?php echo $isOwnMessage ? 'justify-end' : 'justify-start'; ?>">
                                    <div class="max-w-[70%] <?php echo $isOwnMessage ? 'bg-blue-500 text-white' : 'bg-gray-100'; ?> rounded-lg p-3">
                                        <?php if (!$isOwnMessage): ?>
                                            <div class="text-sm font-medium <?php echo $isOwnMessage ? 'text-white' : 'text-gray-900'; ?>">
                                                <?php echo htmlspecialchars($message['firstName'] . ' ' . $message['lastName']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div><?php echo nl2br(htmlspecialchars($message['messageContent'])); ?></div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <!-- Message input -->
                        <form method="POST" class="mt-auto">
                            <input type="hidden" name="action" value="send_message">
                            <input type="hidden" name="chat_id" value="<?php echo $currentChat; ?>">
                            <div class="flex gap-2">
                                <textarea 
                                    name="message" 
                                    class="flex-1 border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Type your message..."
                                    rows="2"
                                    required
                                ></textarea>
                                <button 
                                    type="submit"
                                    class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    Send
                                </button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="h-[600px] flex items-center justify-center text-gray-500">
                        Select a chat or create a new one to start messaging
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- New Chat Modal -->
    <div id="newChatModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-96">
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
                        class="w-full border rounded-lg p-2"
                        rows="3"
                    ></textarea>
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

    <script>
        function showNewChatModal() {
            document.getElementById('newChatModal').classList.remove('hidden');
        }

        function hideNewChatModal() {
            document.getElementById('newChatModal').classList.add('hidden');
        }

        // Auto-scroll to bottom of messages
        const messagesDiv = document.querySelector('.overflow-y-auto');
        if (messagesDiv) {
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
    </script>

    <footer id="footer"></footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="../js/global.js"></script>
</body>
</html>