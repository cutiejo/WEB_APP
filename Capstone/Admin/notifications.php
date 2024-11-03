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
    <title>Notifications</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">

    <style>
        /* Notification styles */
        .logs-table-container {
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            height: auto;
        }
        .notification-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        .notification-item:hover {
            background-color: #e9ecef;
        }
        .notification-item h6 {
            margin: 0;
            font-weight: bold;
            font-size: 16px;
        }
        .notification-item p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container-fluid main-content">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Notifications</h1>
    </div>
    <div class="logs-table-container" id="notifications-container">
        <!-- Notifications will be dynamically loaded here -->
    </div>
</div>

<!-- Firebase and custom JavaScript -->
<script type="module">
  // Import Firebase modules
  import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";
  import { getFirestore, collection, query, where, onSnapshot } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-firestore.js";

  // Firebase configuration
  const firebaseConfig = {
    apiKey: "AIzaSyARVlrOu1mzfjwkzZRUCw8eHGafT_sLTgs",
    authDomain: "svmrfid.firebaseapp.com",
    projectId: "svmrfid",
    storageBucket: "svmrfid.appspot.com",
    messagingSenderId: "1089576323565",
    appId: "1:1089576323565:web:b32d2b714695045065bfe4",
    measurementId: "G-3PN9Y0NVJD"
  };

  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  const db = getFirestore(app);

  // Fetch and display notifications for the admin
  const notificationsContainer = document.getElementById("notifications-container");

  function fetchNotifications() {
    const notificationsRef = collection(db, "notifications");
    const q = query(notificationsRef, where("user_id", "==", "admin_user_id")); // Replace "admin_user_id" with actual ID

    onSnapshot(q, (querySnapshot) => {
      notificationsContainer.innerHTML = ''; // Clear existing notifications
      querySnapshot.forEach((doc) => {
        const data = doc.data();
        const notificationHTML = `
          <div class="notification-item">
            <h6>${data.message}</h6>
            <p>${new Date(data.created_at.toDate()).toLocaleDateString()}</p>
          </div>
        `;
        notificationsContainer.insertAdjacentHTML("beforeend", notificationHTML);
      });
    });
  }

  // Fetch notifications on page load
  fetchNotifications();
</script>

<!-- jQuery, Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
