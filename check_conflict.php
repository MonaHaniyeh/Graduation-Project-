<?php
// filepath: c:\Users\user\OneDrive - Balqa Applied University\Desktop\TestGP\check_conflict.php

header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "examinationsystem");
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get DeptHeadID and DepartmentID from query if provided
$deptHeadID = isset($_GET['DeptHeadID']) ? intval($_GET['DeptHeadID']) : 0;
$departmentID = isset($_GET['DepartmentID']) ? intval($_GET['DepartmentID']) : 0;

$sql = "SELECT CourseID, CourseName FROM courses";
$where = [];

// Filter by DepartmentID and DeptHeadID if both are provided
if ($deptHeadID && $departmentID) {
    $where[] = "DepartmentID = $departmentID";
    $where[] = "DeptHeadID = $deptHeadID";
}

if (count($where) > 0) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " ORDER BY CourseName";
$result = $conn->query($sql);

$courses = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = [
            'id' => $row['CourseID'],
            'name' => $row['CourseName']
        ];
    }
}
echo json_encode(['courses' => $courses]);
$conn->close();
?>