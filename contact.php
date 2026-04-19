<?php
// error_reporting(0);
// ini_set('display_errors', 0);

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

// Set timezone to IST
date_default_timezone_set('Asia/Kolkata');

// Create connection
$conn = @new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database Connection Failed: " . $conn->connect_error]);
    exit;
}

// Set MySQL session time zone to IST
$conn->query("SET time_zone = '+05:30'");

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

// Prepare Email variables (for reuse in retry block)
$to = "mahhadevlogistics23@gmail.com";
$subject = "New Quote Request from website - Mahadev Logistics";
$message = '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; border: 1px solid #eee; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header { background-color: #000; color: #f2c94c; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .footer { background-color: #f9f9f9; padding: 15px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #eee; }
        .row { margin-bottom: 15px; border-bottom: 1px solid #f1f1f1; padding-bottom: 10px; }
        .label { font-weight: bold; color: #555; width: 150px; display: inline-block; }
        .value { color: #000; }
        .highlight { color: #d90101; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Mahadev Logistics</h1>
            <p style="margin: 5px 0 0; font-size: 14px;">New Website Inquiry</p>
        </div>
        <div class="content">
            <p>Hello Admin,</p>
            <p>You have received a new quote request from the website. Here are the details:</p>
            
            <div class="row"><span class="label">Name:</span> <span class="value">' . $name . '</span></div>
            <div class="row"><span class="label">Email:</span> <span class="value"><a href="mailto:' . $email . '">' . $email . '</a></span></div>
            <div class="row"><span class="label">Phone:</span> <span class="value">' . $phone . '</span></div>
            <div class="row"><span class="label">Freight Type:</span> <span class="value highlight">' . $freight_type . '</span></div>
            <div class="row"><span class="label">Dimensions:</span> <span class="value">' . $dimensions . '</span></div>
            <div class="row"><span class="label">Origin:</span> <span class="value">' . $origin . '</span></div>
            <div class="row"><span class="label">Destination:</span> <span class="value">' . $destination . '</span></div>
            <div class="row"><span class="label">Options:</span> <span class="value">' . $options . '</span></div>
            
            <p style="margin-top: 25px;">Please contact the client as soon as possible.</p>
        </div>
        <div class="footer">
            This email was generated automatically from mahadevlogistic.com<br>
            Time: ' . date("Y-m-d H:i:s") . '
        </div>
    </div>
</body>
</html>';

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Mahadev Website <info@mahadevlogistic.com>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

if ($conn->query($sql) === TRUE) {
    // Send HTML Email Notification
    @mail($to, $subject, $message, $headers);
    echo json_encode(["status" => "success", "message" => "Thank you! Your response has been submitted, Our team will contact you soon."]);
}
else {
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
                // Also send mail in retry case
                @mail($to, $subject, $message, $headers);
                echo json_encode(["status" => "success", "message" => "Thank you! Your response has been submitted, Our team will contact you soon."]);
            }
            else {
                echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
            }
        }
        else {
            echo json_encode(["status" => "error", "message" => "Error creating table: " . $conn->error]);
        }
    }
    else {
        echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
    }
}

$conn->close();
?>