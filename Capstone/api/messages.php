<?php
require_once '../config/firebase_config.php'; // Include Firebase configuration
header('Content-Type: application/json');

// Check the request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['conversation_id'])) {
            // Fetch messages for a specific conversation
            fetchMessages($_GET['conversation_id']);
        }
        break;

    case 'POST':
        // Handle adding a new message to a conversation
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['conversation_id'], $data['sender_id'], $data['message_content'])) {
            addMessageToConversation($data['conversation_id'], $data['sender_id'], $data['message_content']);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid request method']);
        break;
}

// Fetch all messages in a specific conversation
function fetchMessages($conversationId) {
    global $firestore;
    $messagesRef = $firestore->collection('messages');
    $query = $messagesRef->where('conversation_id', '=', $conversationId)->orderBy('timestamp', 'ASC');

    $messages = [];
    $documents = $query->documents();
    foreach ($documents as $document) {
        $message = $document->data();
        $message['id'] = $document->id();
        $messages[] = $message;
    }

    echo json_encode(['messages' => $messages]);
}

// Add a new message to an existing conversation
function addMessageToConversation($conversationId, $senderId, $messageContent) {
    global $firestore;
    $messagesRef = $firestore->collection('messages');

    $messageData = [
        'conversation_id' => $conversationId,
        'sender_id' => $senderId,
        'message_content' => $messageContent,
        'timestamp' => new \DateTime(),
    ];

    $newMessage = $messagesRef->add($messageData);

    echo json_encode(['status' => 'success', 'message_id' => $newMessage->id()]);
}
?>
