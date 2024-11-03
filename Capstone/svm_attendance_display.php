<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Interface</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Reset and Global Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body, html {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        /* Main Container */
        .container {
            position: relative;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
        }

        /* Header */
        .header {
            width: 95%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header-logo-left, .header-logo-right {
            height: 60px;
        }

        .header-content {
            text-align: center;
            flex: 1;
        }

        .header-content h2 {
            font-size: 2vw;
            margin: 0;
        }

        .header-content p {
            font-size: 1.5vw;
            margin: 0;
        }

        /* Student Info Section */
        .student-info {
            display: flex;
            align-items: flex-start;
            gap: 30px;
            width: 90%;
        }

        .student-details {
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 70%;
        }

        .student-id, .student-grade {
            font-size: 1.5vw;
            font-weight: bold;
            color: black;
            text-align: center;
        }

        .student-name {
            font-size: 1.5vw;
            font-weight: bold;
            color: white;
            background-color: green;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            width: 100%;
        }

        /* Profile Picture and IN Button */
        .profile-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .profile-picture {
            width: 20vw;
            height: 20vw;
            border-radius: 50%;
            border: 5px solid #007bff;
            overflow: hidden;
        }

        .profile-picture img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .status-indicator {
            font-size: 2vw;
            color: white;
            font-weight: bold;
            background-color: blue;
            padding: 5px 40px;
            border-radius: 5px;
            text-align: center;
        }

        /* Table Container */
        .table-container {
            width: 100%;
            max-width: 1200px;
            overflow-x: auto;
            margin-top: 10px;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 1.5vw;
            text-align: center;
        }

        .attendance-table th, .attendance-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        .attendance-table th {
            background-color: #007bff;
            color: white;
        }

        /* Footer */
        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 10px;
            background-color: #006400;
            overflow: hidden;
        }

        /* Scrolling Text */
        .scrolling-text {
            display: inline-block;
            white-space: nowrap;
            font-size: 1.5vw;
            color: white;
            animation: scroll-text 15s linear infinite;
        }

        @keyframes scroll-text {
            0% {
                transform: translateX(100vw);
            }
            100% {
                transform: translateX(-150%);
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <img src="assets/imgs/logo.png" alt="SVM Logo" class="header-logo-left">
        <div class="header-content">
            <h2>SV Montessori Imus Cavite</h2>
            <p>Ph. 8 Brgy. Magdalo, Bahayang Pagasa Subdivision, Imus, Cavite</p>
        </div>
        <img src="assets/imgs/deped_logo.jpg" alt="DepED Logo" class="header-logo-right">
    </div>

    <div class="student-info">
        <div class="profile-container">
            <div class="profile-picture">
                <img src="uploads/default.png" alt="Student Photo" id="studentPhoto">
            </div>
            <div class="status-indicator" id="statusIndicator">IN</div>
        </div>
        
        <div class="student-details">
            <div class="student-id" id="studentId">LRN/Employee ID</div>
            <div class="student-name" id="studentName">Name Placeholder</div>
            <div class="student-grade" id="studentGrade">Grade and Section Placeholder</div>

            <div class="table-container">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Grade</th>
                            <th>Section</th>
                            <th>Scan Time</th>
                            <th>Event Type</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody">
                        <!-- Rows will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="scrolling-text">
            Welcome back, Students! &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span id="dateDisplay"></span> | <span id="timeDisplay"></span>
        </div>
    </div>
</div>

<script>
    let inputBuffer = ''; // Initialize input buffer for RFID data
    let lastScanTime = 0; // Last scan timestamp
    const SCAN_COOLDOWN = 3000; // Cooldown of 3 seconds to prevent double taps

    function updateTime() {
        const now = new Date();
        document.getElementById('timeDisplay').textContent = now.toLocaleTimeString();
        document.getElementById('dateDisplay').textContent = now.toLocaleDateString();
    }
    setInterval(updateTime, 1000);
    updateTime();

    // Fetch data from the API and update UI
    function fetchAttendanceData(rfid_uid) {
        console.log("Fetching data for RFID:", rfid_uid);
        fetch(`attendance_fetch.php?rfid_uid=${rfid_uid}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'error') {
                    console.error(data.message);
                    return;
                }

                const studentData = data.student;

                // Update student information
                document.getElementById('studentName').textContent = studentData.full_name || 'N/A';
                document.getElementById('studentId').textContent = studentData.lrn || 'N/A';
                document.getElementById('studentGrade').textContent = `Grade ${studentData.grade_level || 'N/A'} ${studentData.section || ''}`;

                // Update profile picture
                const studentPhoto = document.getElementById('studentPhoto');
                studentPhoto.src = `uploads/profile_${rfid_uid}.jpg`;
                studentPhoto.onerror = () => studentPhoto.src = 'uploads/default.png';

                // Update attendance table
                const tableBody = document.getElementById('attendanceTableBody');
                const newRow = `
                    <tr>
                        <td>${studentData.full_name || 'N/A'}</td>
                        <td>${studentData.grade_level || 'N/A'}</td>
                        <td>${studentData.section || 'N/A'}</td>
                        <td>${new Date().toLocaleTimeString()}</td>
                        <td>${data.student.status_indicator || 'IN'}</td>
                    </tr>`;
                tableBody.insertAdjacentHTML('afterbegin', newRow);

                // Limit table to last 3 rows
                while (tableBody.rows.length > 3) {
                    tableBody.deleteRow(-1);
                }
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    // Listen for RFID scans with cooldown
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            const currentTime = new Date().getTime();

            // Check cooldown
            if (currentTime - lastScanTime < SCAN_COOLDOWN) {
                console.log("Double tap prevented.");
                return; // Ignore this scan if it's too soon
            }

            console.log("Detected Enter key, RFID UID:", inputBuffer);
            const rfid_uid = inputBuffer.trim();
            inputBuffer = ''; // Clear buffer
            fetchAttendanceData(rfid_uid);
            lastScanTime = currentTime; // Update last scan time
        } else {
            inputBuffer += event.key;
        }
    });
</script>

</body>
</html>
