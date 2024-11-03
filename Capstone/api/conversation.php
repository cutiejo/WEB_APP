<?php
require_once '../config/firebase_config.php'; // Include Firebase configuration
header('Content-Type: application/json');

// Check the request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['user_id'])) {
            // Fetch all conversations for a specific user
            fetchUserConversations($_GET['user_id']);
        }
        break;

    case 'POST':
        // Handle adding a new conversation
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['user_ids'])) {
            addConversation($data['user_ids']);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid request method']);
        break;
}

// Fetch all conversations for a specific user
function fetchUserConversations($userId) {
    global $firestore;
    $conversationsRef = $firestore->collection('conversations');
    $query = $conversationsRef->where('participants', 'array-contains', $userId);

    $conversations = [];
    $documents = $query->documents();
    foreach ($documents as $document) {
        $conversation = $document->data();
        $conversation['id'] = $document->id();
        $conversations[] = $conversation;
    }

    echo json_encode(['conversations' => $conversations]);
}

// Add a new conversation
function addConversation($userIds) {
    global $firestore;
    $conversationsRef = $firestore->collection('conversations');

    $conversationData = [
        'participants' => $userIds,
        'created_at' => new \DateTime(),
    ];

    $newConversation = $conversationsRef->add($conversationData);

    echo json_encode(['status' => 'success', 'conversation_id' => $newConversation->id()]);
}
?>
