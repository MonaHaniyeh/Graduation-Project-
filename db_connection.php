<?php
function openDatabaseConnection()
{
    $servername = "127.0.0.1";
    $username = "root"; // Replace with your username
    $password = ""; // Replace with your password
    $dbname = "examinationsystem";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

function closeDatabaseConnection($conn)
{
    $conn->close();
}
?>