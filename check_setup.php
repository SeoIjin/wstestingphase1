<?php
// check_setup.php - Diagnostic tool
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>eBCsH Setup Checker</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .check { margin: 15px 0; padding: 12px; border-radius: 4px; }
        .pass { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .fail { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warn { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        h1 { color: #333; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 eBCsH System Setup Checker</h1>
        
        <?php
        $checks = [];
        
        // 1. Database Connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "users";
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            $checks[] = ['name' => 'Database Connection', 'status' => 'fail', 'msg' => $conn->connect_error];
        } else {
            $checks[] = ['name' => 'Database Connection', 'status' => 'pass', 'msg' => 'Connected to MySQL'];
            
            // Check tables
            $tables = ['account', 'password_resets', 'notifications', 'requests'];
            foreach ($tables as $table) {
                $result = $conn->query("SHOW TABLES LIKE '$table'");
                if ($result && $result->num_rows > 0) {
                    $checks[] = ['name' => "Table: $table", 'status' => 'pass', 'msg' => 'Table exists'];
                } else {
                    $checks[] = ['name' => "Table: $table", 'status' => 'fail', 'msg' => 'Table not found'];
                }
            }
            
            // Check password_resets columns
            $result = $conn->query("DESCRIBE password_resets");
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            
            $required_cols = ['id', 'email', 'otp', 'created_at', 'expires_at', 'used'];
            foreach ($required_cols as $col) {
                if (in_array($col, $columns)) {
                    $checks[] = ['name' => "Column: password_resets.$col", 'status' => 'pass', 'msg' => 'Column exists'];
                } else {
                    $checks[] = ['name' => "Column: password_resets.$col", 'status' => 'fail', 'msg' => 'Column missing'];
                }
            }
            
            // Check reset_token column
            if (in_array('reset_token', $columns)) {
                $checks[] = ['name' => "Column: password_resets.reset_token", 'status' => 'pass', 'msg' => 'Column exists'];
            } else {
                $checks[] = ['name' => "Column: password_resets.reset_token", 'status' => 'warn', 'msg' => 'Optional - you should add this with SQL migration'];
            }
            
            $conn->close();
        }
        
        // 2. PHP Version
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            $checks[] = ['name' => 'PHP Version', 'status' => 'pass', 'msg' => 'PHP ' . PHP_VERSION];
        } else {
            $checks[] = ['name' => 'PHP Version', 'status' => 'fail', 'msg' => 'PHP ' . PHP_VERSION . ' (requires 7.0+)'];
        }
        
        // 3. PHPMailer
        $autoload_path = __DIR__ . '/PHPMailer/src/Exception.php';
        if (file_exists($autoload_path)) {
            $checks[] = ['name' => 'PHPMailer Library', 'status' => 'pass', 'msg' => 'PHPMailer found at ' . $autoload_path];
        } else {
            $checks[] = ['name' => 'PHPMailer Library', 'status' => 'fail', 'msg' => 'PHPMailer not found. Download from: https://github.com/PHPMailer/PHPMailer'];
        }
        
        // 4. File Permissions
        $files_to_check = [
            'send_otp.php',
            'verify_otp.php',
            'reset_password.php',
            'sign-in.php'
        ];
        
        foreach ($files_to_check as $file) {
            if (file_exists($file)) {
                $checks[] = ['name' => "File: $file", 'status' => 'pass', 'msg' => 'File exists'];
            } else {
                $checks[] = ['name' => "File: $file", 'status' => 'fail', 'msg' => 'File not found in root directory'];
            }
        }
        
        // 5. Uploads Directory
        if (is_dir('uploads')) {
            if (is_writable('uploads')) {
                $checks[] = ['name' => 'Uploads Directory', 'status' => 'pass', 'msg' => 'Directory exists and is writable'];
            } else {
                $checks[] = ['name' => 'Uploads Directory', 'status' => 'warn', 'msg' => 'Directory exists but not writable'];
            }
        } else {
            $checks[] = ['name' => 'Uploads Directory', 'status' => 'warn', 'msg' => 'Directory does not exist (will be created on first upload)'];
        }
        
        // Display all checks
        foreach ($checks as $check) {
            $class = $check['status'];
            echo "<div class='check $class'>";
            echo "<strong>" . $check['name'] . ":</strong> " . $check['msg'];
            echo "</div>";
        }
        ?>
        
        <hr style="margin: 30px 0;">
        
        <h2>📋 Quick Fixes</h2>
        
        <h3>If PHPMailer is missing:</h3>
        <ol>
            <li>Download: <code>https://github.com/PHPMailer/PHPMailer</code></li>
            <li>Extract and copy the <code>PHPMailer/src</code> folder to your project</li>
            <li>Your structure should be: <code>/PHPMailer/src/Exception.php</code>, <code>/PHPMailer/src/PHPMailer.php</code>, etc.</li>
        </ol>
        
        <h3>If password_resets.reset_token is missing:</h3>
        <p>Run this SQL in phpMyAdmin:</p>
        <pre style="background: #f4f4f4; padding: 10px; border-radius: 4px;">
ALTER TABLE password_resets ADD COLUMN reset_token VARCHAR(255) NULL AFTER used;
        </pre>
        
        <h3>Test Email Sending:</h3>
        <p>Use <code>test_gmail.php</code> to verify Gmail credentials are correct.</p>
        
    </div>
</body>
</html>