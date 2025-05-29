<?php
/**
 * User Model Class
 * 
 * This class handles user-related database operations.
 */

class User {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Create a new user
     * 
     * @param array $userData User data (name, email, password, department, reg_no)
     * @return array|bool Returns ['id' => $id, 'token' => $token] on success, false on failure
     */
    public function create($userData) {
        try {
            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));
            
            // Hash the password
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Insert the user into the database
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, password, department, reg_no, verification_token)
                VALUES (:name, :email, :password, :department, :reg_no, :token)
            ");
            
            $stmt->bindParam(':name', $userData['name']);
            $stmt->bindParam(':email', $userData['email']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':department', $userData['department']);
            $stmt->bindParam(':reg_no', $userData['reg_no']);
            $stmt->bindParam(':token', $verificationToken);
            
            $stmt->execute();
            
            // Return the user ID
            return [
                'id' => $this->db->lastInsertId(),
                'token' => $verificationToken
            ];
        } catch (PDOException $e) {
            error_log("User creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify a user's email using the verification token
     * 
     * @param string $token Verification token
     * @return bool Success status
     */
    public function verifyEmail($token) {
        try {
            // Find the user with the given token
            $stmt = $this->db->prepare("
                UPDATE users
                SET is_verified = 1, verification_token = NULL
                WHERE verification_token = :token
            ");
            
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            // If a row was affected, the verification was successful
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Email verification failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find a user by email
     * 
     * @param string $email User email
     * @return array|bool User data on success, false on failure
     */
    public function findByEmail($email) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Find user by email failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find a user by ID
     * 
     * @param int $id User ID
     * @return array|bool User data on success, false on failure
     */
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Find user by ID failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Authenticate a user
     * 
     * @param string $email User email
     * @param string $password User password
     * @return array|bool User data on success, false on failure
     */
    public function authenticate($email, $password) {
        // Find the user by email
        $user = $this->findByEmail($email);
        
        // If user not found or password doesn't match, return false
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        
        // Check if email is verified
        if (!$user['is_verified']) {
            // User is not verified
            return ['verified' => false, 'user' => $user];
        }
        
        // User authenticated successfully
        return ['verified' => true, 'user' => $user];
    }
    
    /**
     * Get all users
     * 
     * @return array|bool User list on success, false on failure
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("
                SELECT id, name, email, role, department, reg_no, is_verified, created_at,
                is_verified as is_active
                FROM users
                ORDER BY created_at DESC
            ");
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get all users failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set user status (active/inactive)
     * 
     * @param int $userId User ID
     * @param int $status Status (1 for active, 0 for inactive)
     * @return bool Success status
     */
    public function setUserStatus($userId, $status) {
        try {
            // Since we don't have an is_active field, we'll use is_verified
            // to represent the active status
            $stmt = $this->db->prepare("UPDATE users SET is_verified = :status WHERE id = :id");
            $stmt->bindParam(':id', $userId);
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Set user status failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify user by admin
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function verifyUserByAdmin($userId) {
        return $this->setUserStatus($userId, 1);
    }
    
    /**
     * Set user role
     * 
     * @param int $userId User ID
     * @param string $role Role (admin or user)
     * @return bool Success status
     */
    public function setUserRole($userId, $role) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET role = :role WHERE id = :id");
            $stmt->bindParam(':id', $userId);
            $stmt->bindParam(':role', $role);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Set user role failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a user
     * 
     * @param int $id User ID
     * @param array $userData User data to update
     * @return bool Success status
     */
    public function update($id, $userData) {
        try {
            // Build the update query dynamically based on the fields to update
            $updateFields = [];
            $params = [':id' => $id];
            
            foreach ($userData as $field => $value) {
                // Skip password for now, it will be handled separately
                if ($field !== 'password') {
                    $updateFields[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $value;
                }
            }
            
            // If there's a password to update, hash it
            if (isset($userData['password']) && !empty($userData['password'])) {
                $updateFields[] = "password = :password";
                $params[':password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            }
            
            // If no fields to update, return success
            if (empty($updateFields)) {
                return true;
            }
            
            // Build and execute the query
            $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return true;
        } catch (PDOException $e) {
            error_log("Update user failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a user
     * 
     * @param int $id User ID
     * @return bool Success status
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Delete user failed: " . $e->getMessage());
            return false;
        }
    }
}