<?php
session_start();

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../Login/login.php"); // Redirect to login if not a teacher
    exit();
}

include '../db.php';


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .container-wrapper {
            display: flex;
            justify-content: space-between;
            max-width: 1200px;
            width: 100%;
            gap: 20px; /* Adds space between the two containers */
            height: 90vh; /* Ensures the entire height is used */
        }

        .messages-list-container, .messages-content-container {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 20px;
            height: 100%; /* Stretch containers to full height */
            overflow: auto; /* Ensure scrollable content if height exceeds */
        }

        .messages-list-container {
            flex: 0 0 300px; /* Fixed width for the left side */
            background-color: #f9f9f9;
            overflow-y: auto;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .messages-list h5 {
            margin-bottom: 20px;
        }

        .message-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .message-item:hover {
            background-color: #e9ecef;
        }

        .message-item img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .message-item .details {
            font-size: 14px;
        }

        .message-item .details h6 {
            margin: 0;
            font-weight: bold;
            font-size: 15px;
        }

        .messages-content-container {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .message-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .message-header img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .message-body {
            flex-grow: 1;
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }

        .message img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .message-content {
            background-color: #f1f1f1;
            padding: 10px 15px;
            border-radius: 20px;
            max-width: 70%;
        }

        .message-content p {
            margin: 0;
            font-size: 14px;
        }

        .message-content small {
            display: block;
            margin-top: 5px;
            color: #aaa;
        }

        /* Input Area Styling */
        .input-area {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background-color: white;
            border-top: 1px solid #ddd;
            margin-top: 10px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 0 0 10px 10px;
        }

        .input-area input {
            flex-grow: 1;
            padding: 10px;
            border-radius: 25px;
            border: 1px solid #ddd;
            outline: none;
        }

        .input-area button {
            background-color: white;
            border: none;
            padding-left: 15px;
            font-size: 20px;
            color: #666;
            cursor: pointer;
        }

        .input-area button:hover .fa-paper-plane {
            color: blue;
        }

     /* Back Button Styling */
.back-button {
    margin: 10px;
}

.btn-custom-back {
    background-color: #6c757d; /* Gray background */
    color: white; /* White text color */
    border: none; /* Remove border */
    border-radius: 8px; /* Rounded corners */
    padding: 8px 15px; /* Padding for the button */
    font-size: 16px; /* Font size */
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Soft shadow */
    transition: background-color 0.3s ease; /* Smooth hover effect */
}

.btn-custom-back i {
    margin-right: 8px; /* Add some space between icon and text */
}

.btn-custom-back:hover {
    background-color: #5a6268; /* Darker gray on hover */
}


    </style>
</head>
<body>
    <!-- Back Button -->
<div class="back-button">
    <button class="btn btn-custom-back" onclick="window.location.href='index.php'">
        <i class="fas fa-arrow-left"></i> Back
    </button>
</div>

<!-- Main Content -->
<div class="main-content" id="main-content">
    <div class="container-fluid">
        <div class="container-wrapper">
            <!-- Left Sidebar (Messages List) -->
            <div class="messages-list-container">
                <h5>Messages</h5>
                <div id="messages-list">
                    <!-- Messages list will be dynamically loaded here -->
                </div>
            </div>

            <!-- Main Content (Messages) -->
            <div class="messages-content-container">
                <!-- Message Header -->
                <div class="message-header">
                    <img src="https://via.placeholder.com/45" alt="User" id="message-header-img">
                    <h5 id="message-header-name">Select a conversation</h5>
                </div>

                <!-- Message Body -->
                <div class="message-body" id="message-body">
                    <!-- Messages will be loaded here -->
                </div>

                <!-- Input Area -->
                <div class="input-area">
                    <input type="text" id="messageInput" placeholder="Send a message...">
                    <button onclick="sendMessage()"><i class="fa fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
let receiverId = null;

// Load messages list
async function loadMessagesList() {
    try {
        const response = await fetch('fetch_messages_list.php');
        const data = await response.json();
        if (data.status) {
            const messagesList = document.getElementById('messages-list');
            messagesList.innerHTML = '';  // Clear previous list
            data.users.forEach(user => {
                const userElement = document.createElement('div');
                userElement.classList.add('message-item');
                userElement.onclick = () => loadConversation(user.id);
                const lastMessageTime = user.last_message_time ? new Date(user.last_message_time).toLocaleString() : "No messages";
                userElement.innerHTML = `
                    <img src="https://via.placeholder.com/45" alt="User">
                    <div class="details">
                        <h6>${user.role} ${user.id}</h6>
                        <small>Last message at: ${lastMessageTime}</small>
                    </div>
                `;
                messagesList.appendChild(userElement);
            });
        } else {
            alert(data.message || "Failed to load message list");
        }
    } catch (error) {
        console.error("Error fetching message list:", error);
        alert("Error fetching message list");
    }
}

// Load conversation
async function loadConversation(id) {
    receiverId = id;
    try {
        const response = await fetch(`fetch_messages.php?receiver_id=${id}`);
        const data = await response.json();
        const messageBody = document.getElementById('message-body');
        messageBody.innerHTML = '';  // Clear previous messages
        if (data.status) {
            data.messages.forEach(msg => {
                const messageElement = document.createElement('div');
                messageElement.classList.add('message');
                const alignment = msg.sender_id === receiverId ? "start" : "end";
                messageElement.innerHTML = `
                    <img src="https://via.placeholder.com/35" alt="User">
                    <div class="message-content">
                        <p>${msg.message_content}</p>
                        <small>${new Date(msg.created_at).toLocaleString()}</small>
                    </div>
                `;
                messageBody.appendChild(messageElement);
            });
        } else {
            alert(data.message || "Failed to load messages");
        }
    } catch (error) {
        console.error("Error fetching messages:", error);
        alert("Error fetching messages");
    }
}

// Send a message
async function sendMessage() {
    const messageContent = document.getElementById('messageInput').value;
    if (messageContent && receiverId) {
        try {
            const response = await fetch('send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ receiver_id: receiverId, message_content: messageContent })
            });
            const data = await response.json();
            if (data.status) {
                const messageBody = document.getElementById('message-body');
                const newMessage = document.createElement('div');
                newMessage.classList.add('message');
                newMessage.innerHTML = `
                    <img src="https://via.placeholder.com/35" alt="User">
                    <div class="message-content">
                        <p>${messageContent}</p>
                        <small>${new Date().toLocaleString()}</small>
                    </div>
                `;
                messageBody.appendChild(newMessage);
                document.getElementById('messageInput').value = '';  // Clear input field
            } else {
                alert(data.message || "Failed to send message");
            }
        } catch (error) {
            console.error("Error sending message:", error);
            alert("Error sending message");
        }
    } else {
        alert("Please enter a message and select a user.");
    }
}

// Initial load
loadMessagesList();

</script>

</body>
</html>
