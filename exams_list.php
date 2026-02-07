<?php
include 'db_connection.php';
$conn = openDatabaseConnection();

// Fetch exams with related data
$query = "SELECT e.*, e.ExamID, c.CourseName, i.InstructorFullName, r.ClassRoomNumber 
          FROM exam e
          JOIN courses c ON e.CourseID = c.CourseID
          JOIN instructor i ON e.InstructorID = i.InstructorID
          JOIN classrooms r ON e.ClassRoomID = r.ClassRoomID
          ORDER BY e.ExamDate, e.ExamStartTime";
$result = $conn->query($query);

closeDatabaseConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exams List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }

        table {
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            word-wrap: break-word;
        }

        th {
            background-color: #2c3e50;
            color: white;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .back-btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #2980b9;
        }

        .no-exams {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-style: italic;
        }

        .date-col {
            width: 10%;
        }

        .time-col {
            width: 15%;
        }

        .type-col {
            width: 10%;
        }

        .course-col {
            width: 25%;
        }

        .instructor-col {
            width: 25%;
        }

        .classroom-col {
            width: 15%;
        }

        .delete-exam {
            width: 10%;
        }

        .delete-btn {
            padding: 8px 12px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .delete-btn:hover {
            background-color: #d32f2f;
        }

        .delete-btn i {
            font-size: 14px;
            margin-right: 5px;
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 4px;
            color: white;
            z-index: 1000;
            animation: fadeIn 0.3s;
        }

        .toast.success {
            background-color: #4CAF50;
        }

        .toast.error {
            background-color: #f44336;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Button loading state */
        .fa-spinner {
            margin-right: 8px;
        }

        /* Row fade animation */
        tr {
            transition: opacity 0.3s;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Exams List</h1>

        <?php if ($result->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th class="date-col">Date</th>
                            <th class="time-col">Time</th>
                            <th class="type-col">Type</th>
                            <th class="course-col">Course</th>
                            <th class="instructor-col">Instructor</th>
                            <th class="classroom-col">Classroom</th>
                            <th class="delete-exam">Delete Exam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo !empty($row['ExamDate']) ? htmlspecialchars($row['ExamDate']) : 'N/A'; ?></td>
                                <td>
                                    <?php
                                    $startTime = !empty($row['ExamStartTime']) ? htmlspecialchars($row['ExamStartTime']) : 'N/A';
                                    $endTime = !empty($row['ExamEndTime']) ? htmlspecialchars($row['ExamEndTime']) : 'N/A';
                                    echo $startTime . ' - ' . $endTime;
                                    ?>
                                </td>
                                <td><?php echo !empty($row['ExamType']) ? htmlspecialchars($row['ExamType']) : 'N/A'; ?></td>
                                <td><?php echo !empty($row['CourseName']) ? htmlspecialchars($row['CourseName']) : 'N/A'; ?>
                                </td>
                                <td><?php echo !empty($row['InstructorFullName']) ? htmlspecialchars($row['InstructorFullName']) : 'N/A'; ?>
                                </td>
                                <td><?php echo !empty($row['ClassRoomNumber']) ? htmlspecialchars($row['ClassRoomNumber']) : 'N/A'; ?>
                                </td>
                                <td>
                                    <button class="delete-btn" data-exam-id="<?php echo $row['ExamID'] ?>">

                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-exams">No exams found in the database.</div>
        <?php endif; ?>

        <div style="text-align: center;">
            <a href="AddExam.html" class="back-btn">Back</a>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteButtons = document.querySelectorAll('.delete-btn');

            deleteButtons.forEach(button => {
                button.addEventListener('click', async function () {
                    const examId = this.getAttribute('data-exam-id');
                    const row = this.closest('tr');

                    if (!row) {
                        showAlert('Error: Could not find exam row', 'error');
                        return;
                    }

                    if (!confirm('Are you sure you want to delete this exam?')) {
                        return;
                    }

                    // Set loading state
                    const originalHTML = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                    this.disabled = true;

                    try {
                        const response = await fetch('delete_exam.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ exam_id: examId })
                        });

                        // Handle non-200 responses
                        if (!response.ok) {
                            const errorText = await response.text();
                            throw new Error(errorText || `Server error: ${response.status}`);
                        }

                        const result = await response.json();

                        if (result.success) {
                            // Visual removal with animation
                            row.style.transition = 'all 0.3s';
                            row.style.opacity = '0';
                            row.style.height = '0';
                            row.style.padding = '0';
                            row.style.margin = '0';
                            row.style.border = 'none';

                            setTimeout(() => {
                                row.remove();
                                showAlert(result.message, 'success');
                            }, 300);
                        } else {
                            throw new Error(result.message || 'Deletion failed');
                        }
                    } catch (error) {
                        console.error('Delete error:', error);
                        let errorMsg = 'An error occurred';

                        try {
                            const errorData = JSON.parse(error.message);
                            errorMsg = errorData.message || error.message;
                        } catch (e) {
                            errorMsg = error.message;
                        }

                        showAlert(errorMsg, 'error');
                    } finally {
                        // Reset button state
                        this.innerHTML = originalHTML;
                        this.disabled = false;
                    }
                });
            });
            // Optional: Toast notification function
            function showAlert(message, type = 'success') {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.textContent = message;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.remove();
                }, 3000);
            }
        });
    </script>
</body>

</html>