<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['dephead_id'], $_SESSION['dephead_name'], $_SESSION['department_id'], $_SESSION['department_name'])) {
    echo json_encode([
        'dephead_id' => $_SESSION['dephead_id'],
        'dephead_name' => $_SESSION['dephead_name'],
        'department_id' => $_SESSION['department_id'],
        'department_name' => $_SESSION['department_name']
    ]);
} else {
    echo json_encode(['error' => 'not_logged_in']);
}
?>