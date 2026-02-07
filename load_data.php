<?php
// load_data.php
header('Content-Type: application/json');

// Database connection
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "examinationsystem";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $type = $_GET['type'] ?? '';

    switch ($type) {
        case 'courses':
            $stmt = $conn->query("SELECT CourseID, CourseName FROM courses");
            break;

        case 'instructors':
            $stmt = $conn->query("SELECT InstructorID, InstructorFullName FROM instructor");
            break;

        case 'classrooms':
            $stmt = $conn->query("SELECT ClassRoomID, ClassRoomNumber, RoomType FROM classrooms");
            break;

        case 'non_conflicting':
            $stmt = $conn->query("SELECT * FROM `non-conflicting`");
            break;

        default:
            echo json_encode([]);
            exit;
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn = null;
?>