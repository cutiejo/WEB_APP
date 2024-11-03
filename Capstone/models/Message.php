<?php
require_once '../config/firebase_config.php';

class Message
{
    private $firestore;

    public function __construct()
    {
        global $firestore;
        $this->firestore = $firestore->database()->collection('messages');
    }

    public function send($senderId, $receiverId, $text)
    {
        return $this->firestore->add([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'text' => $text,
            'timestamp' => new \DateTime()
        ]);
    }

    public function fetchAll($userId)
    {
        return $this->firestore->where('receiver_id', '=', $userId)->documents();
    }
}
?>