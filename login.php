<?php
// ملف login.php


// التحقق من أن الطلب POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // استقبال البيانات من النموذج
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // التحقق من عدم وجود بيانات فارغة
    if (empty($username) || empty($password)) {
        header("Location: loginpage.html?error=" . urlencode("Please enter both username and password"));
        exit;
    }

    // الاتصال بقاعدة البيانات باستخدام MySQLi
    $host = 'localhost';
    $dbuser = 'root';
    $dbpass = '';
    $dbname = 'examinationSystem';

    // إنشاء اتصال MySQLi
    $conn = new mysqli($host, $dbuser, $dbpass, $dbname);

    // التحقق من وجود أخطاء في الاتصال
    if ($conn->connect_error) {
        header("Location: loginpage.html?error=" . urlencode("Database connection failed"));
        exit;
    }

    // تعيين encoding لضمان التعامل مع الأحرف العربية
    $conn->set_charset("utf8mb4");

    // إعداد الاستعلام مع استخدام prepared statements
    $stmt = $conn->prepare("SELECT AdminID, username, password FROM admin WHERE username = ? LIMIT 1");

    if ($stmt === false) {
        header("Location: loginpage.html?error=" . urlencode("Database error"));
        $conn->close();
        exit;
    }

    // ربط المعاملات وتنفيذ الاستعلام
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // التحقق من كلمة المرور
        if ($password === $user['password']) {
            // بدء جلسة المستخدم
            session_start();
            $_SESSION['admin_id'] = $user['AdminID'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['logged_in'] = true;

            // إغلاق الاتصال
            $stmt->close();
            $conn->close();

            // Redirect to home page
            header("Location: home.php");
            exit;
        }
    }

    // في حالة فشل المصادقة
    $stmt->close();
    $conn->close();
    header("Location: loginpage.html?error=" . urlencode("Invalid username or password"));

    exit;

}

// If not a POST request, redirect to login page
header("Location: loginpage.html");
exit;
?>