<?php
/**
 * Event Registration Model Class
 * 
 * This class extends the Registration model with event-specific functionality.
 * It serves as a specialized interface for event registration operations.
 */

class EventRegistration extends Registration {
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        parent::__construct($db);
    }
    
    /**
     * Register a user for an event with additional event-specific validation
     * 
     * @param array $registrationData Registration data
     * @return int|bool Registration ID on success, false on failure
     */
    public function registerForEvent($registrationData) {
        // Additional event-specific validation could be added here
        return $this->register($registrationData);
    }
    
    /**
     * Get all registrations for a specific event
     * 
     * @param int $eventId Event ID
     * @return array|bool Registration list on success, false on failure
     */
    public function getRegistrationsByEventId($eventId) {
        return $this->getByEventId($eventId);
    }
    
    /**
     * Get registration statistics for an event
     * 
     * @param int $eventId Event ID
     * @return array Statistics including total registrations, pending, approved, etc.
     */
    public function getEventRegistrationStats($eventId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM registrations
                WHERE event_id = :event_id
            ");
            
            $stmt->bindParam(':event_id', $eventId);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get event registration stats failed: " . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'cancelled' => 0,
                'rejected' => 0
            ];
        }
    }
}