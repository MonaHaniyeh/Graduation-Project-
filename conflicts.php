<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "examinationsystem");

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}
$conn->set_charset("utf8mb4"); // Add character set for consistency


$course1_id = isset($_POST['course1']) ? intval($_POST['course1']) : 0;
$course2_id = isset($_POST['course2']) ? intval($_POST['course2']) : 0;

// Debug output - you can remove this later if everything works
error_log("Received course1_id: $course1_id, course2_id: $course2_id");

// Validate input
if (!$course1_id || !$course2_id) {
    echo json_encode(['error' => 'Invalid or missing course IDs provided. Both course1 and course2 parameters are required.']);
    $conn->close();
    exit;
}

if ($course1_id == $course2_id) {
    echo json_encode(['error' => 'Please select two different courses to check for conflicts.']);
    $conn->close();
    exit;
}

// Get course names
$courseNames = ['course1_name' => 'Not Available', 'course2_name' => 'Not Available'];
$sql_course_names = "SELECT CourseID, CourseName FROM courses WHERE CourseID = ? OR CourseID = ?";
$stmt_course_names = $conn->prepare($sql_course_names);

if ($stmt_course_names) {
    $stmt_course_names->bind_param("ii", $course1_id, $course2_id);
    $stmt_course_names->execute();
    $result_course_names = $stmt_course_names->get_result();

    while ($row = $result_course_names->fetch_assoc()) {
        if ($row['CourseID'] == $course1_id) {
            $courseNames['course1_name'] = $row['CourseName'] ?: 'Name Not Found';
        }
        if ($row['CourseID'] == $course2_id) {
            $courseNames['course2_name'] = $row['CourseName'] ?: 'Name Not Found';
        }
    }
    $stmt_course_names->close();
} else {
    error_log("Prepare statement for course names failed: " . $conn->error);
    echo json_encode(['error' => 'Server error: Could not prepare query for course names.']);
    $conn->close();
    exit;
}

// Get students registered in either course and their registration status for each selected course
$students = [];
$sql_students = "SELECT 
            s.id, 
            s.student_id, 
            s.name AS StudentName, -- Assuming 'name' is the full name column based on previous schema
            MAX(CASE WHEN sc.CourseID = ? THEN 1 ELSE 0 END) AS course1_registered,
            MAX(CASE WHEN sc.CourseID = ? THEN 1 ELSE 0 END) AS course2_registered
        FROM students s
        JOIN student_courses sc ON s.id = sc.StudentID
        WHERE sc.CourseID = ? OR sc.CourseID = ?
        GROUP BY s.id, s.student_id, s.name
        ORDER BY s.name";

$stmt_students = $conn->prepare($sql_students);

if ($stmt_students) {
    // Parameters for student query: course1_id, course2_id, course1_id, course2_id
    $stmt_students->bind_param("iiii", $course1_id, $course2_id, $course1_id, $course2_id);
    $stmt_students->execute();
    $result_students = $stmt_students->get_result();

    while ($row = $result_students->fetch_assoc()) {
        $students[] = [
            'StudentID' => $row['student_id'],
            'StudentName' => $row['StudentName'], // Use the 'name' column for full name
            'course1_registered' => (bool)$row['course1_registered'],
            'course2_registered' => (bool)$row['course2_registered']
        ];
    }
    $stmt_students->close();
} else {
    error_log("Prepare statement for student conflicts failed: " . $conn->error);
    echo json_encode(['error' => 'Server error: Could not prepare query for student data.']);
    $conn->close();
    exit;
}

echo json_encode(array_merge($courseNames, ['students' => $students]));
$conn->close();
?>