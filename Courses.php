<?php
// Always set content type to JSON for the response
header('Content-Type: application/json');

$response = []; // Initialize response array

$con = mysqli_connect("localhost", "root", "", "examinationsystem");

// Check connection
if (mysqli_connect_errno()) {
    $response['status'] = 'error';
    $response['message'] = "Database connection failed: " . mysqli_connect_error();
    echo json_encode($response);
    exit;
}
mysqli_set_charset($con, "utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch all courses for client-side cache initialization
    $query = "SELECT CourseName as name, CourseNum as number, CourseSubject as level FROM courses";
    $result = mysqli_query($con, $query);

    if ($result) {
        $courses_data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Ensure 'number' is an integer for consistency with client-side logic
            $row['number'] = (int)$row['number'];
            $courses_data[] = $row;
        }
        $response['status'] = 'success';
        $response['data'] = $courses_data;
    } else {
        $response['status'] = 'error';
        $response['message'] = "Error fetching courses: " . mysqli_error($con);
    }
    mysqli_close($con);
    echo json_encode($response);
    exit;
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from POST request
    $courseName = $_POST['courseName'] ?? null;
    $courseNumber = $_POST['courseNumber'] ?? null;
    $subjectLevel = $_POST['subjectLevel'] ?? null;
    $level = $_POST['level'] ?? null;
    $hours = $_POST['hours'] ?? null;
    $departmentId = $_POST['department'] ?? null; // Ensure this matches the `name` attribute in the form

    // Debugging: Log the received data
    error_log("Received data: " . print_r($_POST, true));

    // Basic validation for required fields
    if (empty($courseName) || empty($courseNumber) || empty($subjectLevel) || empty($level) || !isset($hours) || empty($departmentId)) {
        $response['status'] = 'error';
        $response['message'] = "All fields, including department, are required.";
        mysqli_close($con);
        echo json_encode($response);
        exit;
    }

    // Check if the department exists in the database
    $stmt_check_department = $con->prepare("SELECT DepartmentID FROM department WHERE DepartmentID = ?");
    $stmt_check_department->bind_param("i", $departmentId);
    $stmt_check_department->execute();
    $stmt_check_department->store_result();
    if ($stmt_check_department->num_rows === 0) {
        $response['status'] = 'error';
        $response['message'] = "Invalid department selected.";
        $stmt_check_department->close();
        mysqli_close($con);
        echo json_encode($response);
        exit;
    }
    $stmt_check_department->close();

    // Check if the course number already exists
    $stmt_check_num = $con->prepare("SELECT CourseNum FROM courses WHERE CourseNum = ?");
    $stmt_check_num->bind_param("s", $courseNumber);
    $stmt_check_num->execute();
    $stmt_check_num->store_result();
    if ($stmt_check_num->num_rows > 0) {
        $response['status'] = 'error';
        $response['message'] = "Course number '$courseNumber' already exists in the database.";
        $stmt_check_num->close();
        mysqli_close($con);
        echo json_encode($response);
        exit;
    }
    $stmt_check_num->close();

    // Insert the course into the database
    $stmt_insert = $con->prepare("INSERT INTO courses (CourseName, CourseNum, CourseSubject, CreditHours, Level, DepartmentID) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("sssisi", $courseName, $courseNumber, $subjectLevel, $hours, $level, $departmentId);
    $stmt_insert->execute();
    $stmt_insert->close();

    $response['status'] = 'success';
    $response['message'] = "Course added successfully!";
    mysqli_close($con);
    echo json_encode($response);
    exit;
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method.';
    // http_response_code(405); // Optionally set Method Not Allowed HTTP status
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetchDepartments'])) {
    // Fetch all departments
    $query = "SELECT DepartmentID, DepartmentName FROM department";
    $result = mysqli_query($con, $query);

    if ($result) {
        $departments_data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $departments_data[] = $row;
        }
        $response['status'] = 'success';
        $response['data'] = $departments_data;
    } else {
        $response['status'] = 'error';
        $response['message'] = "Error fetching departments: " . mysqli_error($con);
    }
    mysqli_close($con);
    echo json_encode($response);
    exit;
}

// Check the structure of the departments table
$describe_departments = mysqli_query($con, "DESCRIBE departments");
$departments_structure = mysqli_fetch_all($describe_departments, MYSQLI_ASSOC);

// Check the structure of the courses table
$describe_courses = mysqli_query($con, "DESCRIBE courses");
$courses_structure = mysqli_fetch_all($describe_courses, MYSQLI_ASSOC);

// Verify the foreign key relationship
$foreign_key_check = mysqli_query($con, "
    SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_NAME = 'courses' AND COLUMN_NAME = 'DepartmentID'
");
$foreign_keys = mysqli_fetch_all($foreign_key_check, MYSQLI_ASSOC);

$response['departments_structure'] = $departments_structure;
$response['courses_structure'] = $courses_structure;
$response['foreign_keys'] = $foreign_keys;

echo json_encode($response);
?>
<?php
// Database connection
$con = mysqli_connect("localhost", "root", "", "examinationsystem");

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Departments to insert
$departments = [
    ['DepartmentID' => 1, 'DepartmentNumber' => 'SE001', 'DepartmentName' => 'Software Engineering'],
    ['DepartmentID' => 2, 'DepartmentNumber' => 'CS001', 'DepartmentName' => 'Computer Science'],
    ['DepartmentID' => 3, 'DepartmentNumber' => 'CIS001', 'DepartmentName' => 'Computer Information Systems']
];

foreach ($departments as $department) {
    // Fetch DepHeadID from departmenthead table
    $stmt = $con->prepare("SELECT DepHeadID FROM departmenthead WHERE DepartmentName = ?");
    $stmt->bind_param("s", $department['DepartmentName']);
    $stmt->execute();
    $stmt->bind_result($depHeadID);
    $stmt->fetch();
    $stmt->close();

    // Insert into department table
    $stmt_insert = $con->prepare("INSERT INTO department (DepartmentNumber, DepartmentName, DepHeadID) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("ssi", $department['DepartmentNumber'], $department['DepartmentName'], $depHeadID);
    $stmt_insert->execute();
    $stmt_insert->close();
}

mysqli_close($con);
?>
