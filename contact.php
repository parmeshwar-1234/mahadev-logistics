<?php
// Enable detailed error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Database credentials
$servername = "127.0.0.1";
$username = "u433269662_ML_website";
$password = "Z5L^d2m~"; // Updated password
$dbname = "u433269662_Mahadev";

// Check for mysqli extension
if (!extension_loaded('mysqli')) {
    echo json_encode(["status" => "error", "message" => "PHP mysqli extension is not enabled on this server."]);
    exit;
}

// Prevent mysqli from throwing exceptions (critical for PHP 8.1+)
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

// Create connection
$conn = @new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database Connection Failed: " . $conn->connect_error]);
    exit;
}

// Get form data
$name = isset($_POST['your-name']) ? $conn->real_escape_string($_POST['your-name']) : '';
$email = isset($_POST['your-email']) ? $conn->real_escape_string($_POST['your-email']) : '';
$phone = isset($_POST['your-phone']) ? $conn->real_escape_string($_POST['your-phone']) : '';
$dimensions = isset($_POST['your-dimensions']) ? $conn->real_escape_string($_POST['your-dimensions']) : '';
$freight_type = isset($_POST['your-frieght']) ? $conn->real_escape_string($_POST['your-frieght']) : '';
$origin = isset($_POST['your-origin-location']) ? $conn->real_escape_string($_POST['your-origin-location']) : '';
$destination = isset($_POST['your-destination-location']) ? $conn->real_escape_string($_POST['your-destination-location']) : '';
$options = isset($_POST['radio-605']) ? $conn->real_escape_string($_POST['radio-605']) : '';

// Insert into database
$sql = "INSERT INTO contact_submissions (name, email, phone, dimensions, freight_type, origin, destination, logistics_options)
VALUES ('$name', '$email', '$phone', '$dimensions', '$freight_type', '$origin', '$destination', '$options')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Thank you! Your message has been sent."]);
} else {
    // If the table doesn't exist, try to create it and retry
    if ($conn->errno == 1146) {
        $createTable = "CREATE TABLE IF NOT EXISTS contact_submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255),
            email VARCHAR(255),
            phone VARCHAR(50),
            dimensions VARCHAR(255),
            freight_type VARCHAR(100),
            origin VARCHAR(255),
            destination VARCHAR(255),
            logistics_options VARCHAR(255),
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        if ($conn->query($createTable) === TRUE) {
            // Retry insertion
            if ($conn->query($sql) === TRUE) {
                echo json_encode(["status" => "success", "message" => "Thank you! Your message has been sent."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Error creating table: " . $conn->error]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
    }
}

$conn->close();
?>
