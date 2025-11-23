<?php
// send_otp.php - Final Fixed Version
// NO HTML OUTPUT ALLOWED - ONLY JSON

// Set error reporting FIRST
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Headers MUST be first
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Catch any output errors
ob_start();

try {
    // Database configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "users";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || !isset($data['email'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Email is required'
        ]);
        exit();
    }

    $email = trim($data['email']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid email format'
        ]);
        exit();
    }

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT id FROM account WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Email not found in our system'
        ]);
        exit();
    }
    $stmt->close();

    // Generate 6-digit OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    // Delete any existing OTPs for this email
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();
    }

    // Insert new OTP with 60-minute expiration
    $stmt = $conn->prepare("INSERT INTO password_resets (email, otp, created_at, expires_at, used) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 60 MINUTE), 0)");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("ss", $email, $otp);

    if (!$stmt->execute()) {
        throw new Exception('Failed to generate OTP: ' . $stmt->error);
    }
    $stmt->close();

    // Clean output buffer
    ob_end_clean();

    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'OTP generated and saved successfully',
        'email' => $email,
        'otp_length' => strlen($otp)
    ]);

    $conn->close();

} catch (Exception $e) {
    // Clean output buffer to remove any HTML
    ob_end_clean();
    
    // Log the error
    error_log('send_otp.php Error: ' . $e->getMessage());
    
    // Return JSON error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>