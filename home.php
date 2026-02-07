<?php
session_start(); // Start the session

// Database connection details (adjust as per your setup)
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "examinationsystem";

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    // Handle connection error gracefully
    $deptCount = "N/A";
    $roomCount = "N/A";
    $courseCount = "N/A";
    $recentActivity = [];
} else {
    mysqli_set_charset($conn, "utf8mb4");

    // Fetch Department Heads Count
    $resultDept = mysqli_query($conn, "SELECT COUNT(*) as count FROM departmenthead");
    $deptCount = ($resultDept && mysqli_num_rows($resultDept) > 0) ? mysqli_fetch_assoc($resultDept)['count'] : 0;

    // Fetch Rooms Count
    $resultRoom = mysqli_query($conn, "SELECT COUNT(*) as count FROM ClassRooms");
    $roomCount = ($resultRoom && mysqli_num_rows($resultRoom) > 0) ? mysqli_fetch_assoc($resultRoom)['count'] : 0;

    // Fetch Courses Count
    $resultCourse = mysqli_query($conn, "SELECT COUNT(*) as count FROM Courses");
    $courseCount = ($resultCourse && mysqli_num_rows($resultCourse) > 0) ? mysqli_fetch_assoc($resultCourse)['count'] : 0;

    // Fetch Recent Activity
    $recentActivity = [];
    $queryRecentActivity = "
        SELECT 
            'Department Head' AS activity_type, 
            CONCAT('Added ', Name, ' as Department Head') AS activity_detail, 
            add_date AS activity_date
        FROM departmenthead
        WHERE add_date IS NOT NULL
        UNION ALL
        SELECT 
            'Room' AS activity_type, 
            CONCAT('Created Room ', ClassRoomNumber) AS activity_detail, 
            NULL AS activity_date
        FROM ClassRooms
        UNION ALL
        SELECT 
            'Course' AS activity_type, 
            CONCAT('Added Course ', CourseName) AS activity_detail, 
            NULL AS activity_date
        FROM Courses
        ORDER BY activity_date DESC
        LIMIT 5;
    ";

    $resultRecentActivity = mysqli_query($conn, $queryRecentActivity);
    if ($resultRecentActivity && mysqli_num_rows($resultRecentActivity) > 0) {
        while ($row = mysqli_fetch_assoc($resultRecentActivity)) {
            $recentActivity[] = $row;
        }
    }

    mysqli_close($conn);
}

// Example: Get username from session if available
$loggedInUser = $_SESSION['username'] ?? 'Admin User'; // Default to 'Admin User' if not set

// Define custom type order
$typeOrder = [
    'Department Head' => 1,
    'Course' => 2,
    'Room' => 3
];

// Sort recentActivity based on the type order
usort($recentActivity, function ($a, $b) use ($typeOrder) {
    return ($typeOrder[$a['activity_type']] ?? 999) - ($typeOrder[$b['activity_type']] ?? 999);
});

// Sample activity data (in a real app, this would come from a database)
$activities = [
    [
        'type' => 'Department Head',
        'action' => 'Added Mona Haniyah as Department Head',
        'timestamp' => '2025-05-12 20:43:28'
    ],
    [
        'type' => 'Course',
        'action' => 'Added Course Component',
        'timestamp' => '2025-05-12 21:15:42'
    ],
    [
        'type' => 'Room',
        'action' => 'Created Room 100',
        'timestamp' => '2025-05-12 22:30:15'
    ]
];

// Function to get icon class based on activity type
function getActivityIcon($type) {
    switch ($type) {
        case 'Department Head': return 'fas fa-user-tie';
        case 'Course': return 'fas fa-book';
        case 'Room': return 'fas fa-door-open';
        default: return 'fas fa-circle';
    }
}

// Function to format timestamp
function formatTimestamp($timestamp) {
    return date('M j, Y g:i A', strtotime($timestamp));
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Home</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="home.css" rel="stylesheet" /> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="tabs">
        <a href="home.php" class="tab active"><i class="fas fa-home"></i> Home</a> <!-- Link to itself -->
        <a href="department-Heads.html" class="tab"><i class="fas fa-users"></i> Departments</a>
        <a href="Rooms.html" class="tab"><i class="fas fa-door-open"></i> Rooms</a>
        <a href="Courses.html" class="tab"><i class="fas fa-book"></i> Courses</a>
        <a href="login.php" class="tab"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="content">
        <div id="home" class="tab-content active">
            <div class="header">
                <h1>Welcome to the Admin Dashboard</h1>
            </div>
            <div class="user-info">
                <p>Logged in as: <strong><?php echo htmlspecialchars($loggedInUser); ?></strong></p>
            </div>
            <div class="stats">
                <div class="stat-box">
                    <h3><?php echo $deptCount; ?></h3>
                    <p>Department Heads</p>
                </div>
                <div class="stat-box">
                    <h3><?php echo $roomCount; ?></h3>
                    <p>Rooms</p>
                </div>
                <div class="stat-box">
                    <h3><?php echo $courseCount; ?></h3>
                    <p>Courses</p>
                </div>
            </div>
            <div class="box">
                <button><a href="department-Heads.html" class="button">Department Heads</a></button>
                <button><a href="Rooms.html" class="button">Rooms</a></button>
                <button><a href="Courses.html" class="button">Courses</a></button>
            </div>
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <ul id="activityList">
                    <?php if (!empty($recentActivity)): ?>
                        <?php foreach ($recentActivity as $activity): ?>
                            <li data-type="<?php echo htmlspecialchars($activity['activity_type']); ?>">
                                <i class="activity-icon <?= getActivityIcon($activity['activity_type']) ?>"></i>
                                <div class="activity-details">
                                    <span class="activity-type"><?= htmlspecialchars($activity['activity_type']) ?>:</span>
                                    <?= htmlspecialchars($activity['activity_detail']) ?>
                                </div>
                                <span class="activity-timestamp">
                                    <?= formatTimestamp($activity['activity_date']) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No recent activity.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>