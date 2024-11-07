<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php"); // Redirect to login if not an admin
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
            gap: 20px;
            /* Adds space between the two containers */
            height: 90vh;
            /* Ensures the entire height is used */
        }

        .messages-list-container,
        .messages-content-container {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 20px;
            height: 100%;
            /* Stretch containers to full height */
            overflow: auto;
            /* Ensure scrollable content if height exceeds */
        }

        .messages-list-container {
            flex: 0 0 300px;
            /* Fixed width for the left side */
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
            background-color: #6c757d;
            /* Gray background */
            color: white;
            /* White text color */
            border: none;
            /* Remove border */
            border-radius: 8px;
            /* Rounded corners */
            padding: 8px 15px;
            /* Padding for the button */
            font-size: 16px;
            /* Font size */
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            /* Soft shadow */
            transition: background-color 0.3s ease;
            /* Smooth hover effect */
        }

        .btn-custom-back i {
            margin-right: 8px;
            /* Add some space between icon and text */
        }

        .btn-custom-back:hover {
            background-color: #5a6268;
            /* Darker gray on hover */
        }
    /* Container for the messages */
        #message-body {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 10px;
            max-height: 80vh;
            overflow-y: auto;
        }

        /* Style for messages sent by the sender (blue background) */
        .message-right {
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
            gap: 10px;
            margin-left: auto;
            background-color: #0084ff;
            /* Messenger blue */
            color: #fff;
            /* White text for contrast */
            padding: 10px;
            border-radius: 18px 18px 0 18px;
            /* Rounded corners */
            max-width: 70%;
            position: relative;
        }

        /* Style for messages received from others (gray background) */
        .message-left {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background-color: #e4e6eb;
            /* Light gray for incoming messages */
            padding: 10px;
            border-radius: 18px 18px 18px 0;
            max-width: 70%;
            color: #000;
            /* Black text for contrast */
            position: relative;
        }

        /* Profile image styling for both sender and receiver */
        .message-right img,
        .message-left img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
        }

        /* Style for the message content */
        .message-content {
            display: flex;
            flex-direction: column;
        }

        /* Message text styling */
        .message-content p {
            margin: 0;
            font-size: 15px;
            line-height: 1.4;
        }

        /* Styling for the sender's email */
        .message-content small {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            /* Slightly lighter for contrast on blue */
            margin-top: 4px;
        }

        .message-item .details h6 {
            margin: 0;
        }

        .message-item .details p {
            margin: 0;
            font-size: 0.9em;
            color: #666;
        }

        .message-item .details p.unseen {
            font-weight: bold;
            color: #000;
            /* darker color for emphasis */
        }

        .message-item .details p.seen {
            font-weight: normal;
            color: #888;
            /* lighter color for read messages */
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
                    <div id="friend-requests-list">

                    </div>

                    <div class="search-container">
                        <input type="text" id="searchInput" placeholder="Search users by email...">
                        <div id="search-results">

                        </div>
                    </div>
                    <div id="messages-list">

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
                        <button id="submit"><i class="fa fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>

<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";
    import { getFirestore, collection, doc, addDoc, updateDoc, getDoc, getDocs, where, setDoc, onSnapshot, query, orderBy, limit } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-firestore.js";

    const firebaseConfig = {
        apiKey: "AIzaSyBesa9xkX61DZ-xQxsX6UEy0ZP8c7CNvms",
        authDomain: "chat-app-b2c25.firebaseapp.com",
        projectId: "chat-app-b2c25",
        storageBucket: "chat-app-b2c25.firebasestorage.app",
        messagingSenderId: "17333985878",
        appId: "1:17333985878:web:6e83cbc7e786ba367791f7",
        measurementId: "G-0J951CGMHY"
    };

    const app = initializeApp(firebaseConfig);
    const db = getFirestore(app);

    let selectedFriendId = null;
    let selectedFriendName = '';
    let selectedFriendProfilePic = '';

    async function createUserProfile(userId, userInfo) {
        const userRef = doc(db, "users", userId);
        await setDoc(userRef, userInfo);
    }
    createUserProfile("<?php echo $_SESSION['user_id']; ?>", {
        userId: "<?php echo $_SESSION['user_id']; ?>",
        displayName: "<?php echo $_SESSION['email']; ?>",
        email: "<?php echo $_SESSION['email']; ?>",
        profilePicUrl: "https://www.mgp.net.au/wp-content/uploads/2023/05/150-1503945_transparent-user-png-default-user-image-png-png.png"
    });

    const submit = document.getElementById('submit');
    submit.addEventListener('click', async () => {
        const messageInput = document.getElementById('messageInput');
        const messageContent = messageInput.value.trim();
        const senderId = "<?php echo $_SESSION['user_id']; ?>";
        const senderEmail = "<?php echo $_SESSION['email']; ?>";
        const senderProfilePicUrl = "https://www.mgp.net.au/wp-content/uploads/2023/05/150-1503945_transparent-user-png-default-user-image-png-png.png";

        if (messageContent && selectedFriendId) {
            const conversationId = generateConversationId(senderId, selectedFriendId);
            const messagesRef = collection(db, "conversations", conversationId, "messages");

            await addDoc(messagesRef, {
                senderId,
                senderEmail,
                senderProfilePicUrl,
                text: messageContent,
                timestamp: new Date()
            });

            updateOrCreateConversation(conversationId, messageContent, senderId, selectedFriendId);

            messageInput.value = '';
        } else {
            alert("Please select a user to chat with and enter a message.");
        }
    });

    async function updateOrCreateConversation(conversationId, lastMessage, senderId, friendId) {
        const conversationRef = doc(db, "conversations", conversationId);

        const conversationData = {
            userIds: [senderId, friendId],
            ...(lastMessage && { lastMessage: lastMessage }),
            lastTimestamp: lastMessage ? Date.now() : 0,
            seen: false
        };
        await setDoc(conversationRef, conversationData, { merge: true });
    }



    const searchInput = document.getElementById("searchInput");
    const searchResults = document.getElementById("search-results");

    const messagesList = document.getElementById("messages-list");
    let originalMessagesListElements = [];

    function backupMessagesListElements() {
        if (originalMessagesListElements.length === 0) {
            originalMessagesListElements = Array.from(messagesList.children);
            console.log("Original messages list elements backed up.");
        }
    }

    function restoreMessagesListElements() {
        messagesList.innerHTML = '';
        originalMessagesListElements.forEach((element) => {
            messagesList.appendChild(element);
        });
    }

    // Event listener for search input
    searchInput.addEventListener("input", async () => {
        const queryText = searchInput.value.trim();

        if (queryText) {
            backupMessagesListElements();
            messagesList.innerHTML = '';
            await searchUsersByEmail(queryText);
        } else {
            searchResults.innerHTML = '';
            restoreMessagesListElements();
            console.log("Input is empty. Restoring original content.");
        }
    });



    async function searchUsersByEmail(email) {
        const usersRef = collection(db, "users");
        const q = query(usersRef, where("email", "==", email));
        const querySnapshot = await getDocs(q);

        searchResults.innerHTML = '';
        querySnapshot.forEach((userDoc) => {
            const userData = userDoc.data();
            const resultItem = document.createElement("div");
            resultItem.className = "search-result-item";
            const userId = userDoc.id;

            const userItem = document.createElement("div");
            userItem.className = "message-item";
            if (friendsSet.has(userId)) {

                userItem.innerHTML = `
                    <img src="${userData.profilePicUrl}" alt="${userData.displayName}">
                    <div class="details">
                        <h6>${userData.displayName}</h6>
                        <small>Friend</small>
                    </div>
                `;
            }
            else {
                userItem.innerHTML = `
                    <img src="${userData.profilePicUrl}" alt="${userData.displayName}">
                    <div class="details">
                        <h6>${userData.displayName}</h6>
                        <button class="add-friend-btn">Add Friend</button>
                    </div>
                `;
            }
            resultItem.appendChild(userItem);
            searchResults.appendChild(resultItem);

            const addFriendButton = resultItem.querySelector(".add-friend-btn");
            addFriendButton.addEventListener("click", () => sendFriendRequest(userDoc.id));
        });
    }

    const friendsSet = new Set();

    async function sendFriendRequest(friendId) {
        const userId = "<?php echo $_SESSION['user_id']; ?>";
        const friendRequestRef = doc(db, "friendRequests", `${userId}_${friendId}`);

        await setDoc(friendRequestRef, {
            senderId: userId,
            receiverId: friendId,
            status: "pending"
        });

        searchResults.innerHTML = '';
        restoreMessagesListElements();
        searchInput.value = '';
        alert("Friend request sent!");
    }

    async function loadUsers() {
        const userId = "<?php echo $_SESSION['user_id']; ?>";
        const friendRequestsRef = collection(db, "friendRequests");
        const messagesList = document.getElementById("messages-list");
        const friendsList = [];

        onSnapshot(friendRequestsRef, (snapshot) => {
            snapshot.docChanges().forEach(async (change) => {
                const request = change.doc.data();
                const friendId = request.senderId === userId ? request.receiverId : request.senderId;

                if ((change.type === "added" || change.type === "modified") && request.status === "accepted" &&
                    (request.senderId === userId || request.receiverId === userId)) {

                    const userRef = doc(db, "users", friendId);
                    const userSnap = await getDoc(userRef);

                    if (userSnap.exists()) {
                        const userData = userSnap.data();
                        const conversationId = generateConversationId(userId, friendId);
                        const conversationRef = doc(db, "conversations", conversationId);

                        onSnapshot(conversationRef, (conversationSnap) => {
                            let lastMessageText = "No message yet";
                            let lastMessageTimestamp = 0;
                            let isSeen = false;

                            if (conversationSnap.exists()) {
                                const conversationData = conversationSnap.data();
                                lastMessageText = conversationData.lastMessage || "No message yet";
                                lastMessageTimestamp = conversationData.lastTimestamp || 0;
                                isSeen = conversationData.seen && Array.isArray(conversationData.userIds) && conversationData.userIds.includes(userId);
                            }

                            const friendIndex = friendsList.findIndex(friend => friend.friendId === friendId);

                            if (friendIndex !== -1) {
                                friendsList[friendIndex] = {
                                    ...friendsList[friendIndex],
                                    lastMessageText,
                                    lastMessageTimestamp,
                                    isSeen
                                };
                            } else {
                                friendsList.push({
                                    friendId,
                                    displayName: userData.displayName,
                                    profilePicUrl: userData.profilePicUrl,
                                    lastMessageText,
                                    lastMessageTimestamp,
                                    isSeen
                                });
                            }

                            friendsList.sort((a, b) => b.lastMessageTimestamp - a.lastMessageTimestamp);

                            messagesList.innerHTML = '';
                            friendsList.forEach(friend => {
                                const userItem = document.createElement("div");
                                userItem.className = "message-item";
                                userItem.innerHTML = `
                                <img src="${friend.profilePicUrl}" alt="${friend.displayName}">
                                <div class="details">
                                    <h6>${friend.displayName}</h6>
                                    <p class="${friend.isSeen ? 'seen' : 'unseen'}">${friend.lastMessageText}</p>
                                </div>
                            `;

                                userItem.addEventListener("click", () => {
                                    selectedFriendId = friend.friendId;
                                    selectedFriendName = friend.displayName;
                                    selectedFriendProfilePic = friend.profilePicUrl;

                                    loadMessages(selectedFriendId, selectedFriendName, selectedFriendProfilePic);
                                });

                                messagesList.appendChild(userItem);
                            });
                        });
                    }
                }
            });
        });
    }


    async function loadMessages(friendId, friendName, friendProfilePic) {
        const senderId = "<?php echo $_SESSION['user_id']; ?>";
        selectedFriendId = friendId;
        selectedFriendName = friendName;
        selectedFriendProfilePic = friendProfilePic;

        const conversationId = generateConversationId(senderId, friendId);

        await updateOrCreateConversation(conversationId, '', senderId, selectedFriendId);

        const messagesRef = collection(db, "conversations", conversationId, "messages");
        const messagesQuery = query(messagesRef, orderBy("timestamp", "asc"));

        onSnapshot(messagesQuery, (snapshot) => {
            const chatContainer = document.getElementById("message-body");
            chatContainer.innerHTML = '';
            const messageHeaderName = document.getElementById("message-header-name");
            messageHeaderName.textContent = friendName;
            if (snapshot.empty) {

            } else {
                snapshot.forEach((messageDoc) => {
                    const messageData = messageDoc.data();
                    const messageElement = document.createElement("div");
                    messageElement.className = messageData.senderId === senderId ? "message-right" : "message-left";

                    messageElement.innerHTML = `
                    <img src="${messageData.senderProfilePicUrl}" alt="User">
                    <div class="">
                        <p>${messageData.text}</p>
                        <small>${new Date(messageData.timestamp.toDate()).toLocaleString()}</small>
                    </div>
                `;
                    chatContainer.appendChild(messageElement);
                });
            }

            const conversationRef = doc(db, "conversations", conversationId);
            updateDoc(conversationRef, { seen: true });
        });
    }

    async function acceptFriendRequest(requestId) {
        const friendRequestRef = doc(db, "friendRequests", requestId);
        await updateDoc(friendRequestRef, { status: "accepted" });

        const requestSnapshot = await getDoc(friendRequestRef);
        const requestData = requestSnapshot.data();
        const friendsRef = collection(db, "friends");
        await setDoc(doc(friendsRef, `${requestData.senderId}_${requestData.receiverId}`), {
            user1: requestData.senderId,
            user2: requestData.receiverId,
            friendshipSince: new Date()
        });

        alert("Friend request accepted!");
        loadFriendRequests();
        loadUsers();
    }

    async function rejectFriendRequest(requestId) {
        const friendRequestRef = doc(db, "friendRequests", requestId);
        await updateDoc(friendRequestRef, { status: "rejected" });
        alert("Friend request rejected!");
        loadFriendRequests();
        loadUsers();
    }

    function loadFriendRequests() {
        const userId = "<?php echo $_SESSION['user_id']; ?>";
        const friendRequestsRef = collection(db, "friendRequests");
        const q = query(friendRequestsRef, where("receiverId", "==", userId), where("status", "==", "pending"));

        onSnapshot(q, (snapshot) => {
            const requestsContainer = document.getElementById("friend-requests-list");
            requestsContainer.innerHTML = '';

            snapshot.forEach((requestDoc) => {
                const requestData = requestDoc.data();

                const requestItem = document.createElement("div");
                requestItem.className = "request-item";
                requestItem.innerHTML = `
                <p>${requestData.senderId} has sent you a friend request.</p>
                <button class="accept-btn">Accept</button>
                <button class="reject-btn">Reject</button>
            `;

                requestItem.querySelector(".accept-btn").addEventListener("click", () => acceptFriendRequest(requestDoc.id));
                requestItem.querySelector(".reject-btn").addEventListener("click", () => rejectFriendRequest(requestDoc.id));

                requestsContainer.appendChild(requestItem);
            });
        });
    }



    function generateConversationId(senderId, friendId) {
        return Number(senderId) < Number(friendId) ? `${senderId}_${friendId}` : `${friendId}_${senderId}`;
    }

    loadFriendRequests();
    loadUsers();
</script>