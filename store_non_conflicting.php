[file name]: store_non_conflicting.php
[file content begin]
<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "examinationsystem");

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['course1_id']) || !isset($data['course2_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data. Both course1_id and course2_id are required.']);
    $conn->close();
    exit;
}

$course1 = $conn->real_escape_string($data['course1_id']);
$course2 = $conn->real_escape_string($data['course2_id']);

// Check if this pair already exists in the table (in either order)
$checkSql = "SELECT id FROM `non-conflicting` WHERE 
            (course1 = '$course1' AND course2 = '$course2') OR 
            (course1 = '$course2' AND course2 = '$course1')";
$result = $conn->query($checkSql);

if ($result->num_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'This course pair is already marked as non-conflicting.']);
    $conn->close();
    exit;
}

// Get course names for better readability in the database
$courseNames = [];
$nameQuery = $conn->query("SELECT CourseID, CourseName FROM courses WHERE CourseID IN ('$course1', '$course2')");
while ($row = $nameQuery->fetch_assoc()) {
    $courseNames[$row['CourseID']] = $row['CourseName'];
}

$course1Name = isset($courseNames[$course1]) ? $courseNames[$course1] : 'Unknown Course';
$course2Name = isset($courseNames[$course2]) ? $courseNames[$course2] : 'Unknown Course';

// Insert the new non-conflicting pair
$insertSql = "INSERT INTO `non-conflicting` (course1, course2) VALUES ('$course1Name', '$course2Name')";

if ($conn->query($insertSql)) {
    echo json_encode(['success' => true, 'message' => 'Course pair successfully marked as non-conflicting.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error storing non-conflicting pair: ' . $conn->error]);
}

$conn->close();
?>
[file content end]