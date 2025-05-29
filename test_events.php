<?php
// Test script to check if events exist in the database
require_once 'includes/init.php';

echo "Database connection status: " . ($dbConnection ? "Connected" : "Not connected") . "\n";

// Get all events
echo "Attempting to get events...\n";
$events = $eventModel->getAll('date', 'desc');

// Output the number of events
echo "Number of events: " . count($events) . "\n";

// If there are events, show details of the first one
if (count($events) > 0) {
    echo "First event details:\n";
    echo "ID: " . $events[0]['id'] . "\n";
    echo "Title: " . $events[0]['title'] . "\n";
    echo "Date: " . $events[0]['date'] . "\n";
    echo "Created by: " . $events[0]['creator_name'] . "\n";
} else {
    echo "No events found in the database.\n";
    
    // Check if the events table exists and has the correct structure
    try {
        $stmt = $dbConnection->query("SHOW TABLES LIKE 'events'");
        $tableExists = $stmt->rowCount() > 0;
        
        if (!$tableExists) {
            echo "The 'events' table does not exist in the database.\n";
        } else {
            echo "The 'events' table exists but contains no records.\n";
            
            // Check if there are any records in the users table
            $stmt = $dbConnection->query("SELECT COUNT(*) as count FROM users");
            $userCount = $stmt->fetch()['count'];
            echo "Number of users in the database: " . $userCount . "\n";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage() . "\n";
    }
}