<?php
// process_exam.php
header('Content-Type: application/json');
error_reporting(0); // Turn off error reporting to prevent HTML output
ini_set('display_errors', 0);
// Database connection
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "examinationsystem";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get form data
    $examDate = $_POST['examDate'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $examType = $_POST['examType'];

    // Exam 1 data
    $course1 = $_POST['course1'];
    $instructor1 = $_POST['instructor1'];
    $classroom1 = $_POST['classroom1'];


    $hasExam2 = isset($_POST['course2']) && !empty($_POST['course2']) &&
        isset($_POST['instructor2']) && !empty($_POST['instructor2']) &&
        isset($_POST['classroom2']) && !empty($_POST['classroom2']);

    // Exam 2 data
    $course2 = $_POST['course2'];
    $instructor2 = $_POST['instructor2'];
    $classroom2 = $_POST['classroom2'];




    // Check if classroom is already booked
    $stmt = $conn->prepare("SELECT * FROM exam 
                          WHERE ClassRoomID = :classroom    
                          AND ExamDate = :examDate 
                          AND (
                              (:startTime BETWEEN ExamStartTime AND ExamEndTime)
                              OR (:endTime BETWEEN ExamStartTime AND ExamEndTime)
                              OR (ExamStartTime BETWEEN :startTime AND :endTime)
                          )");

    // Check for classroom 1
    $stmt->execute([
        ':classroom' => $classroom1,
        ':examDate' => $examDate,
        ':startTime' => $startTime,
        ':endTime' => $endTime
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Classroom 1 is already booked at this time']);
        exit;
    }

    if ($hasExam2) {
        $stmt->execute([
            ':classroom' => $_POST['classroom2'],
            ':examDate' => $examDate,
            ':startTime' => $startTime,
            ':endTime' => $endTime
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Classroom 2 is already booked at this time']);
            exit;
        }

        if ($classroom1 == $_POST['classroom2']) {
            echo json_encode(['success' => false, 'message' => 'Both exams cannot be in the same classroom']);
            exit;
        }
    }





    // Insert exam 1
    $stmt1 = $conn->prepare("INSERT INTO exam (ExamDate, ExamStartTime, ExamEndTime, ExamType, InstructorID, CourseID, ClassRoomID) 
                            VALUES (:examDate, :startTime, :endTime, :examType, :instructor, :course, :classroom)");
    $stmt1->execute([
        ':examDate' => $examDate,
        ':startTime' => $startTime,
        ':endTime' => $endTime,
        ':examType' => $examType,
        ':instructor' => $instructor1,
        ':course' => $course1,
        ':classroom' => $classroom1
    ]);

    // Insert exam 2
    if ($hasExam2) {
        $stmt2 = $conn->prepare("INSERT INTO exam (ExamDate, ExamStartTime, ExamEndTime, ExamType, InstructorID, CourseID, ClassRoomID) 
                                VALUES (:examDate, :startTime, :endTime, :examType, :instructor, :course, :classroom)");
        $stmt2->execute([
            ':examDate' => $examDate,
            ':startTime' => $startTime,
            ':endTime' => $endTime,
            ':examType' => $examType,
            ':instructor' => $_POST['instructor2'],
            ':course' => $_POST['course2'],
            ':classroom' => $_POST['classroom2']
        ]);
    }

    $message = $hasExam2 ? 'Exams scheduled successfully!' : 'Exam scheduled successfully!';
    echo json_encode(['success' => true, 'message' => $message]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn = null;
?>