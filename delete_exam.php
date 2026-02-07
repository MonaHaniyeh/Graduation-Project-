<?php
header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$config = [
    'host' => '127.0.0.1',
    'user' => 'root',
    'pass' => '',
    'name' => 'examinationsystem'
];

$response = ['success' => false, 'message' => ''];

try {
    // Get and validate input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    if (!isset($input['exam_id']) || !is_numeric($input['exam_id'])) {
        throw new Exception('Invalid Exam ID format');
    }

    $examId = (int) $input['exam_id'];

    // Create database connection
    $conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['name']);
    if ($conn->connect_error) {
        throw new Exception('DB Connection failed: ' . $conn->connect_error);
    }

    // Check if exam exists
    $check = $conn->prepare("SELECT ExamID FROM exam WHERE ExamID = ?");
    if (!$check) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $check->bind_param("i", $examId);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        throw new Exception('No exam found with ID: ' . $examId);
    }
    $check->close();

    // Delete exam
    $delete = $conn->prepare("DELETE FROM exam WHERE ExamID = ?");
    if (!$delete) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $delete->bind_param("i", $examId);
    if (!$delete->execute()) {
        throw new Exception('Delete failed: ' . $delete->error);
    }

    $response = [
        'success' => true,
        'message' => 'Exam deleted successfully',
        'deleted_id' => $examId
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Exam Delete Error: ' . $e->getMessage());
} finally {
    if (isset($conn))
        $conn->close();
    echo json_encode($response);
    exit;
}
?>