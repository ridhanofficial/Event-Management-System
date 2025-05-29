<?php
/**
 * Registration Model Class
 * 
 * This class handles registration-related database operations.
 */

class Registration {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Register for an event
     * 
     * @param array $registrationData Registration data
     * @return int|bool Registration ID on success, false on failure
     */
    public function register($registrationData) {
        try {
            // Check if user already registered for the event
            $stmt = $this->db->prepare("
                SELECT id FROM registrations
                WHERE user_id = :user_id AND event_id = :event_id
            ");
            
            $stmt->bindParam(':user_id', $registrationData['user_id']);
            $stmt->bindParam(':event_id', $registrationData['event_id']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // User already registered for this event
                return false;
            }
            
            // Continue with registration
            $stmt = $this->db->prepare("
                INSERT INTO registrations (
                    user_id, event_id, team_name, members, status
                ) VALUES (
                    :user_id, :event_id, :team_name, :members, :status
                )
            ");
            
            $stmt->bindParam(':user_id', $registrationData['user_id']);
            $stmt->bindParam(':event_id', $registrationData['event_id']);
            $stmt->bindParam(':team_name', $registrationData['team_name']);
            $stmt->bindParam(':members', $registrationData['members']);
            $stmt->bindParam(':status', $registrationData['status']);
            
            $stmt->execute();
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Registration failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get a registration by ID
     * 
     * @param int $id Registration ID
     * @return array|bool Registration data on success, false on failure
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.name as user_name, u.email as user_email, e.title as event_title,
                e.date as event_date, e.time as event_time, e.venue as event_venue, e.room_no as event_room
                FROM registrations r
                JOIN users u ON r.user_id = u.id
                JOIN events e ON r.event_id = e.id
                WHERE r.id = :id
            ");
            
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get registration by ID failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get registrations by user ID
     * 
     * @param int $userId User ID
     * @return array|bool Registration list on success, false on failure
     */
    public function getByUserId($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, e.title as event_title, e.date as event_date, e.time as event_time,
                e.venue as event_venue, e.room_no as event_room
                FROM registrations r
                JOIN events e ON r.event_id = e.id
                WHERE r.user_id = :user_id
                ORDER BY e.date ASC, e.time ASC
            ");
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get registrations by user ID failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get registrations by event ID
     * 
     * @param int $eventId Event ID
     * @return array|bool Registration list on success, false on failure
     */
    public function getByEventId($eventId) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.name as user_name, u.email as user_email,
                u.department as user_department, u.reg_no as user_reg_no
                FROM registrations r
                JOIN users u ON r.user_id = u.id
                WHERE r.event_id = :event_id
                ORDER BY r.created_at ASC
            ");
            
            $stmt->bindParam(':event_id', $eventId);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get registrations by event ID failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update registration status
     * 
     * @param int $id Registration ID
     * @param string $status New status (pending, approved, rejected)
     * @return bool Success status
     */
    public function updateStatus($id, $status) {
        try {
            $stmt = $this->db->prepare("
                UPDATE registrations
                SET status = :status
                WHERE id = :id
            ");
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Update registration status failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update attendance status (check in)
     * 
     * @param int $id Registration ID
     * @param bool $checkIn Check-in status
     * @return bool Success status
     */
    public function updateCheckIn($id, $checkIn) {
        try {
            $stmt = $this->db->prepare("
                UPDATE registrations
                SET check_in = :check_in
                WHERE id = :id
            ");
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':check_in', $checkIn, PDO::PARAM_BOOL);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Update attendance status failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark attendance for a registration
     * 
     * @param int $id Registration ID
     * @param bool $attended Attendance status
     * @return bool Success status
     */
    public function markAttendance($id, $attended) {
        // This is an alias for updateCheckIn to maintain compatibility with attendance.php
        return $this->updateCheckIn($id, $attended);
    }
      /**
     * Delete a registration
     * 
     * @param int $id Registration ID
     * @return bool Success status
     */
    public function delete($id) {
        try {
            // First get the registration details for logging purposes
            $getStmt = $this->db->prepare("SELECT user_id, event_id FROM registrations WHERE id = :id");
            $getStmt->bindParam(':id', $id);
            $getStmt->execute();
            $regInfo = $getStmt->fetch();
            
            if (!$regInfo) {
                error_log("Delete registration failed: Registration ID $id not found");
                return false;
            }
            
            // Log the deletion attempt
            error_log("Deleting registration ID: $id for user: {$regInfo['user_id']}, event: {$regInfo['event_id']}");
            
            // Prepare and execute the delete statement
            $stmt = $this->db->prepare("DELETE FROM registrations WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $result = $stmt->rowCount() > 0;
            if ($result) {
                error_log("Registration ID $id deleted successfully");
            } else {
                error_log("Delete registration DB operation completed but no rows affected for ID: $id");
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Delete registration failed with exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get summary of attended/registered participants for an event
     * 
     * @param int $eventId Event ID
     * @return array|bool Summary data on success, false on failure
     */
    public function getEventAttendanceSummary($eventId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_registered,
                    SUM(CASE WHEN check_in = 1 THEN 1 ELSE 0 END) as total_attended
                FROM registrations
                WHERE event_id = :event_id
            ");
            
            $stmt->bindParam(':event_id', $eventId);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get event attendance summary failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a user is registered for an event
     * 
     * @param int $userId User ID
     * @param int $eventId Event ID
     * @return bool True if registered, false otherwise
     */
    public function isUserRegistered($userId, $eventId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM registrations
                WHERE user_id = :user_id AND event_id = :event_id
            ");
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':event_id', $eventId);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Check if user is registered failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all registrations
     * 
     * @return array|bool All registrations on success, false on failure
     */
    public function getAll() {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.name as user_name, u.email as user_email,
                u.department as user_department, u.reg_no as user_reg_no,
                e.title as event_title, e.date as event_date
                FROM registrations r
                JOIN users u ON r.user_id = u.id
                JOIN events e ON r.event_id = e.id
                ORDER BY r.created_at DESC
            ");
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get all registrations failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get approved registrations by event ID with sorting options
     * 
     * @param int $eventId Event ID
     * @param string $sortBy Column to sort by (name, email, date, attendance)
     * @param string $sortOrder Sort order (asc, desc)
     * @return array|bool Registration list on success, false on failure
     */
    public function getApprovedByEventId($eventId, $sortBy = 'name', $sortOrder = 'asc') {
        try {
            // Validate sort parameters
            $validSortColumns = [
                'name' => 'u.name',
                'email' => 'u.email',
                'date' => 'r.created_at',
                'attendance' => 'r.check_in'
            ];
            
            // Default to name if invalid sort column
            $sortColumn = $validSortColumns[$sortBy] ?? 'u.name';
            
            // Validate sort order
            $sortDirection = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
            
            $stmt = $this->db->prepare("
                SELECT r.*, u.name as user_name, u.email as user_email,
                u.department as user_department, u.reg_no as user_reg_no,
                r.check_in as attended, e.team_based
                FROM registrations r
                JOIN users u ON r.user_id = u.id
                JOIN events e ON r.event_id = e.id
                WHERE r.event_id = :event_id AND r.status = 'approved'
                ORDER BY {$sortColumn} {$sortDirection}, u.name ASC
            ");
            
            $stmt->bindParam(':event_id', $eventId);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get approved registrations by event ID failed: " . $e->getMessage());
            return false;
        }
    }
}