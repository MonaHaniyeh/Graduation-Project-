<?php
session_start();

// Database connection
$conn = new mysqli('127.0.0.1', 'root', '', 'examinationsystem');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = $_POST['name'] ?? '';
$userId = $_POST['userId'] ?? '';
$password = $_POST['password'] ?? '';

// Simple validation
if (empty($name) || empty($userId) || empty($password)) {
    header("Location: depheadlogin.html?error=empty_fields");
    exit();
}

// SQL query using prepared statement
$sql = "SELECT * FROM departmenthead WHERE Name = ? AND UserId = ? AND Password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $name, $userId, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Login successful
    $user = $result->fetch_assoc();

    $_SESSION['dephead_id'] = $user['DepHeadID'];
    $_SESSION['dephead_name'] = $user['Name'];
    $_SESSION['department_id'] = $user['DepartmentID'];
    $_SESSION['department_name'] = $user['DepartmentName'];

    header("Location: check_conflict.html");
} else {
    // Login failed
    header("Location: depheadlogin.html?error=invalid_login");
}

$stmt->close();
$conn->close();
?>