<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start(); // Start session for potential future use

$response = [];

// Database connection
$con = mysqli_connect("localhost", "root", "", "examinationsystem");

// Check connection
if (mysqli_connect_errno()) {
    $response['status'] = 'error';
    header('Content-Type: application/json'); // Set header before output
    $response['message'] = "Database connection failed: " . mysqli_connect_error();
    echo json_encode($response);
    exit;
}
mysqli_set_charset($con, "utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // This block handles fetching all rooms.
    $query = "SELECT ClassRoomNumber as number, RoomType as type, Capacity as capacity FROM ClassRooms ORDER BY ClassRoomNumber ASC";
    $result = mysqli_query($con, $query);
    $rooms_data = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row['capacity'] = (int)$row['capacity']; // Ensure capacity is an integer
            $rooms_data[] = $row;
        }
        $response['status'] = 'success';
        $response['data'] = $rooms_data;
    } else {
        $response['status'] = 'error';
        $response['message'] = "Error fetching rooms: " . mysqli_error($con);
    }

    // Send the response back to the frontend
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    if ($action === 'add') {
        $roomNumber = $_POST['roomNumber'] ?? null;
        $roomType = $_POST['roomType'] ?? null;
        $capacity = $_POST['capacity'] ?? null;

        if (empty($roomNumber) || empty($roomType) || empty($capacity)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }

        // Database connection
        $conn = new mysqli('localhost', 'root', '', 'examinationsystem');
        if ($conn->connect_error) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
            exit;
        }

        $query = "INSERT INTO ClassRooms (ClassRoomNumber, RoomType, Capacity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare query: ' . $conn->error]);
            exit;
        }

        $stmt->bind_param("ssi", $roomNumber, $roomType, $capacity);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Room added successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add room: ' . $stmt->error]);
        }

        $stmt->close();
        $conn->close();
        exit;
    } elseif ($action === 'delete') {
        $roomNumberToDelete = $_POST['roomNumber'] ?? null;

        if (empty($roomNumberToDelete)) {
            $response['status'] = 'error';
            $response['message'] = 'Room number is required for deletion.';
        } else {
            $stmt_delete = $con->prepare("DELETE FROM ClassRooms WHERE ClassRoomNumber = ?");
            if (!$stmt_delete) {
                $response['status'] = 'error';
                $response['message'] = 'Error preparing delete statement: ' . mysqli_error($con);
            } else {
                $stmt_delete->bind_param("s", $roomNumberToDelete);
                if ($stmt_delete->execute()) {
                    if ($stmt_delete->affected_rows > 0) {
                        $response['status'] = 'success';
                        $response['message'] = "Room '" . htmlspecialchars($roomNumberToDelete) . "' deleted successfully.";
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = "Room '" . htmlspecialchars($roomNumberToDelete) . "' not found or already deleted.";
                    }
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Error deleting room: ' . $stmt_delete->error;
                }
                $stmt_delete->close();
            }
        }
    } elseif ($action === 'update') {
        $roomNumber = $_POST['roomNumber'] ?? null;
        $roomType = $_POST['roomType'] ?? null;
        $capacity = $_POST['capacity'] ?? null;

        if (empty($roomNumber) || empty($roomType) || !isset($capacity) || $capacity === '') {
            $response['status'] = 'error';
            $response['message'] = "All fields (Room Number, Room Type, Capacity) are required for updating a room.";
        } else {
            $stmt_update = $con->prepare("UPDATE ClassRooms SET RoomType = ?, Capacity = ? WHERE ClassRoomNumber = ?");
            if (!$stmt_update) {
                $response['status'] = 'error';
                $response['message'] = "Error preparing update statement: " . mysqli_error($con);
            } else {
                $stmt_update->bind_param("sis", $roomType, $capacity, $roomNumber);
                if ($stmt_update->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = "Room '" . htmlspecialchars($roomNumber) . "' updated successfully.";
                } else {
                    $response['status'] = 'error';
                    $response['message'] = "Error updating room: " . $stmt_update->error;
                }
                $stmt_update->close();
            }
        }
    } else { // Unknown or missing action for POST
        $response['status'] = 'error';
        if ($raw_action !== null) {
            $response['message'] = "Invalid action specified: '" . htmlspecialchars($raw_action) . "'. Expected 'add', 'delete', or 'update'.";
        } else {
            $response['message'] = 'Missing action specified for POST request. Expected \'add\', \'delete\', or \'update\'.';
        }
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method.';
}

mysqli_close($con);
header('Content-Type: application/json'); // Ensure header is set before any output
echo json_encode($response);
?>