<?php
// Direct password update script
echo "<h1>Admin Password Fix</h1>";

// Database connection details
$host = 'localhost';
$dbname = 'pietech_events';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    echo "<p>Connecting to database...</p>";
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "<p>Connected successfully!</p>";
    
    // Generate a new password hash for 'admin123'
    $newHash = '$2y$12$mXX.rSp8vlLNqm8vr8bRtOs35XQtmQCeM8a/ylS2MEO2O4e2yzX7G'; // Precomputed for admin123
    
    // Update the admin user password
    echo "<p>Updating admin password...</p>";
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@pietechevents.com' AND role = 'admin'");
    $stmt->execute([$newHash]);
    
    // Check if the update was successful
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green;font-weight:bold;'>Success! Admin password updated.</p>";
        echo "<p>You can now login with:</p>";
        echo "<ul>";
        echo "<li>Email: admin@pietechevents.com</li>";
        echo "<li>Password: admin123</li>";
        echo "</ul>";
        echo "<p><a href='login.php'>Go to Login Page</a></p>";
    } else {
        // Try to get the admin account email
        $stmt = $pdo->query("SELECT email FROM users WHERE role = 'admin' LIMIT 1");
        $admin = $stmt->fetch();
        
        if ($admin) {
            // Update the found admin account
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ? AND role = 'admin'");
            $stmt->execute([$newHash, $admin['email']]);
            
            if ($stmt->rowCount() > 0) {
                echo "<p style='color:green;font-weight:bold;'>Success! Admin password updated for account: " . $admin['email'] . "</p>";
                echo "<p>You can now login with:</p>";
                echo "<ul>";
                echo "<li>Email: " . $admin['email'] . "</li>";
                echo "<li>Password: admin123</li>";
                echo "</ul>";
                echo "<p><a href='login.php'>Go to Login Page</a></p>";
            } else {
                echo "<p style='color:red;'>Failed to update admin password.</p>";
            }
        } else {
            // No admin account found, create one
            echo "<p>No admin account found. Creating one...</p>";
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, department, reg_no, is_verified) 
                VALUES (?, ?, ?, 'admin', 'Administration', 'ADMIN001', 1)");
            $stmt->execute(["System Admin", "admin@system.com", $newHash]);
            
            echo "<p style='color:green;font-weight:bold;'>Created new admin account!</p>";
            echo "<p>You can now login with:</p>";
            echo "<ul>";
            echo "<li>Email: admin@system.com</li>";
            echo "<li>Password: admin123</li>";
            echo "</ul>";
            echo "<p><a href='login.php'>Go to Login Page</a></p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>Database Error: " . $e->getMessage() . "</p>";
    
    // Show more information to help debug
    echo "<h2>Debugging Information</h2>";
    echo "<pre>";
    echo "Host: $host\n";
    echo "Database: $dbname\n";
    echo "Username: $username\n";
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red;'>General Error: " . $e->getMessage() . "</p>";
}
?>