<?php
// Include database connection
require_once '../includes/init.php';

// SQL to add winner_position column if it doesn't exist
$sql = "ALTER TABLE registrations ADD COLUMN IF NOT EXISTS winner_position VARCHAR(10) DEFAULT NULL AFTER feedback_submitted";

try {
    // Execute the SQL
    $dbConnection->exec($sql);
    echo "SUCCESS: winner_position column added to registrations table.";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
