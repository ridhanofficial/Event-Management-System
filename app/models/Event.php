<?php
/**
 * Event Model Class
 * 
 * This class handles event-related database operations.
 */

class Event {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Create a new event
     * 
     * @param array $eventData Event data
     * @return int|bool Event ID on success, false on failure
     */
    public function create($eventData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO events (
                    title, description, category, date, time, venue, room_no, 
                    max_participants, team_based, created_by
                ) VALUES (
                    :title, :description, :category, :date, :time, :venue, :room_no, 
                    :max_participants, :team_based, :created_by
                )
            ");
            
            $stmt->bindParam(':title', $eventData['title']);
            $stmt->bindParam(':description', $eventData['description']);
            $stmt->bindParam(':category', $eventData['category']);
            $stmt->bindParam(':date', $eventData['date']);
            $stmt->bindParam(':time', $eventData['time']);
            $stmt->bindParam(':venue', $eventData['venue']);
            $stmt->bindParam(':room_no', $eventData['room_no']);
            $stmt->bindParam(':max_participants', $eventData['max_participants']);
            $stmt->bindParam(':team_based', $eventData['team_based']);
            $stmt->bindParam(':created_by', $eventData['created_by']);
            
            $stmt->execute();
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Event creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get an event by ID
     * 
     * @param int $id Event ID
     * @return array|bool Event data on success, false on failure
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT e.*, u.name as creator_name
                FROM events e
                JOIN users u ON e.created_by = u.id
                WHERE e.id = :id
            ");
            
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get event by ID failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get upcoming events
     * 
     * @param string $category Optional category filter
     * @return array|bool Event list on success, false on failure
     */
    public function getUpcoming($category = null) {
        try {
            $query = "
                SELECT e.*, u.name as creator_name
                FROM events e
                JOIN users u ON e.created_by = u.id
                WHERE e.date >= CURRENT_DATE
                OR (e.date = CURRENT_DATE AND e.time >= CURRENT_TIME)
            ";
            
            // Add category filter if provided
            if ($category) {
                $query .= " AND e.category = :category";
            }
            
            $query .= " ORDER BY e.date ASC, e.time ASC";
            
            $stmt = $this->db->prepare($query);
            
            // Bind category parameter if provided
            if ($category) {
                $stmt->bindParam(':category', $category);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get upcoming events failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all events
     * 
     * @param string $sort Field to sort by (title, date, venue, category)
     * @param string $order Sort order (asc, desc)
     * @param string $category Optional category filter
     * @return array|bool Event list on success, false on failure
     */
    public function getAll($sort = 'date', $order = 'desc', $category = null) {
        try {
            $query = "
                SELECT e.*, u.name as creator_name
                FROM events e
                JOIN users u ON e.created_by = u.id
            ";
            
            // Add category filter if provided
            if ($category) {
                $query .= " WHERE e.category = :category";
            }
            
            // Validate sort field
            $validSortFields = ['title', 'date', 'venue', 'category'];
            if (!in_array($sort, $validSortFields)) {
                $sort = 'date';
            }
            
            // Validate order
            $validOrderValues = ['asc', 'desc'];
            if (!in_array($order, $validOrderValues)) {
                $order = 'desc';
            }
            
            $query .= " ORDER BY e.{$sort} {$order}";
            
            $stmt = $this->db->prepare($query);
            
            // Bind category parameter if provided
            if ($category) {
                $stmt->bindParam(':category', $category);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get all events failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update an event
     * 
     * @param int $id Event ID
     * @param array $eventData Event data to update
     * @return bool Success status
     */
    public function update($id, $eventData) {
        try {
            // Build the update query dynamically based on the fields to update
            $updateFields = [];
            $params = [':id' => $id];
            
            foreach ($eventData as $field => $value) {
                $updateFields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $value;
            }
            
            // If no fields to update, return success
            if (empty($updateFields)) {
                return true;
            }
            
            // Build and execute the query
            $query = "UPDATE events SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Update event failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete an event
     * 
     * @param int $id Event ID
     * @return bool Success status
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM events WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Delete event failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get count of participants for an event
     * 
     * @param int $id Event ID
     * @return int|bool Count on success, false on failure
     */
    public function getParticipantCount($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM registrations
                WHERE event_id = :id
            ");
            
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Get participant count failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if an event is full
     * 
     * @param int $id Event ID
     * @return bool True if event is full, false otherwise or on failure
     */
    public function isFull($id) {
        try {
            // Get the event details
            $event = $this->getById($id);
            if (!$event) {
                return false;
            }
            
            // Get the current participant count
            $count = $this->getParticipantCount($id);
            if ($count === false) {
                return false;
            }
            
            // Check if the event is full
            return $count >= $event['max_participants'];
        } catch (PDOException $e) {
            error_log("Check if event is full failed: " . $e->getMessage());
            return false;
        }
    }
}