<?php
header("Access-Control-Allow-Origin: *");
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'examinationsystem';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Main request handler
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'get':
        handleGet($conn);
        break;
    case 'add':
        handleAdd($conn);
        break;
    case 'update':
        handleUpdate($conn);
        break;
    case 'delete':
        handleDelete($conn);
        break;
    case 'toggle_status':
        handleToggleStatus($conn);
        break;
    default:
        echo "Invalid operation";
        break;
}

$conn->close();

// Function to get all department heads
function handleGet($conn)
{
    $sql = "SELECT DepHeadID as id, Name as name, UserId as user_id, 
            DepartmentName as department, status, 
            DATE_FORMAT(add_date, '%Y-%m-%d %H:%i:%s') as add_date 
            FROM departmenthead 
            ORDER BY DepHeadID DESC";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                <td>' . $row['id'] . '</td>
                <td style="display: flex; align-items: center; gap: 0.75rem;">
                    <div class="avatar">' . getInitials($row['name']) . '</div>
                    <span>' . $row['name'] . '</span>
                </td>
                <td>' . $row['user_id'] . '</td>
                <td>' . $row['department'] . '</td>
                <td>
                    <span class="status-badge ' . ($row['status'] === 'active' ? 'status-active' : 'status-inactive') . '">
                        <i class="fas fa-circle"></i> 
                        ' . ($row['status'] === 'active' ? 'active' : 'inactive') . '
                    </span>
                </td>
                <td>' . $row['add_date'] . '</td>
                <td>
                    <div class="actions-container">
                        <button class="action-btn status-btn" id="sbtn";
                                onclick="toggleStatus(' . $row['id'] . ')"
                                title="' . ($row['status'] === 'active' ? 'deactivate' : 'Activation') . '">
                            <i class="fas ' . ($row['status'] === 'active' ? 'fa-ban' : 'fa-check') . '"></i>
                        </button>
                        <button class="action-btn edit-btn"  id="ebtn";
                                onclick="editHead(' . $row['id'] . ', \'' . htmlspecialchars($row['name'], ENT_QUOTES) . '\', \'' . $row['user_id'] . '\', \'' . $row['department'] . '\')"
                                title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete-btn" id="dbtn";
                                onclick="confirmDelete(' . $row['id'] . ')"
                                title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                       
   
                    </div>
                </td>
            </tr>';
        }
    } else {
        echo '<tr>
                <td colspan="7">
                    <div class="empty-state">
                        <i class="fas fa-user-tie"></i>
                        <h3>There are no department heads.</h3>
                        <p>Please add a new department head to get started</p>
                    </div>
                </td>
            </tr>';
    }
}

// Function to add a new department head
function handleAdd($conn)
{
    $name = $_POST['Fullname'] ?? '';
    $user_id = $_POST['userId'] ?? '';
    $password = $_POST['Password'] ?? '';
    $department = $_POST['department'] ?? '';

    if (empty($name) || empty($user_id) || empty($password) || empty($department)) {
        echo "All fields are required";
        return;
    }

    if (strlen($password) < 10) {
        echo "Password must be at least 10 characters";
        return;
    }


    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO departmenthead (Name, UserId, Password, DepartmentName, status, add_date) 
                           VALUES (?, ?, ?, ?, 'active', NOW())");
    $stmt->bind_param("ssss", $name, $user_id, $hashed_password, $department);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error adding record: " . $stmt->error;
    }

    $stmt->close();
}

// Function to update a department head
function handleUpdate($conn)
{
    $id = $_POST['id'] ?? '';
    $name = $_POST['Fullname'] ?? '';
    $user_id = $_POST['userId'] ?? '';
    $password = $_POST['Password'] ?? '';
    $department = $_POST['department'] ?? '';

    if (empty($id) || empty($name) || empty($user_id) || empty($department)) {
        echo "All fields are required";
        return;
    }

    if (!empty($password)) {
        if (strlen($password) < 10) {
            echo "Password must be at least 10 characters";
            return;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE departmenthead 
                               SET Name = ?, UserId = ?, Password = ?, DepartmentName = ? 
                               WHERE DepHeadID = ?");
        $stmt->bind_param("ssssi", $name, $user_id, $hashed_password, $department, $id);
    } else {
        $stmt = $conn->prepare("UPDATE departmenthead 
                               SET Name = ?, UserId = ?, DepartmentName = ? 
                               WHERE DepHeadID = ?");
        $stmt->bind_param("sssi", $name, $user_id, $department, $id);
    }

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}

// Function to delete a department head
function handleDelete($conn)
{
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        die("The id is required. ");
    }

    $id = (int) $_GET['id'];
    if ($id <= 0) {
        die("Invalid identifier");
    }

    // التحقق من وجود السجل
    $check = $conn->prepare("SELECT DepHeadID FROM departmenthead WHERE DepHeadID = ?");
    $check->bind_param("i", $id);
    $check->execute();

    if (!$check->get_result()->num_rows > 0) {
        die("The record does not exist");
    }

    // تنفيذ الحذف
    $stmt = $conn->prepare("DELETE FROM departmenthead WHERE DepHeadID = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Deletion error:" . $stmt->error;
    }

    $stmt->close();
}

// Function to toggle department head status
function handleToggleStatus($conn)
{
    $id = $_GET['id'] ?? '';

    if (empty($id)) {
        echo "ID is required";
        return;
    }

    // Get current status
    $stmt = $conn->prepare("SELECT status FROM departmenthead WHERE DepHeadID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $new_status = ($row['status'] == 'active') ? 'inactive' : 'active';

    $stmt = $conn->prepare("UPDATE departmenthead SET status = ? WHERE DepHeadID = ?");
    $stmt->bind_param("si", $new_status, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error updating status: " . $stmt->error;
    }

    $stmt->close();
}

// Helper function to get initials
function getInitials($name)
{
    $initials = '';
    $names = explode(' ', $name);
    foreach ($names as $n) {
        if (!empty($n)) {
            $initials .= mb_substr($n, 0, 1);
            if (mb_strlen($initials) >= 2)
                break;
        }
    }
    return $initials;
}
?>