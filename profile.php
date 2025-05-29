<?php
/**
 * User Profile Page
 * 
 * This page displays and allows editing of user profile information.
 */

// Include initialization file
require_once 'includes/init.php';

// Require login
requireLogin();

// Set page title
$pageTitle = 'My Profile';

// Get user data
$userId = $_SESSION['user_id'];
$user = $userModel->findById($userId);

// Process form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Validate input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);
    $reg_no = trim($_POST['reg_no']);
    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    
    $errors = [];
    
    // Basic validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($department)) {
        $errors[] = "Department is required";
    }
    
    if (empty($reg_no)) {
        $errors[] = "Registration number is required";
    }
    
    // Check if email is already in use by another user
    if ($email !== $user['email']) {
        $existingUser = $userModel->findByEmail($email);
        if ($existingUser && $existingUser['id'] != $userId) {
            $errors[] = "Email is already in use by another account";
        }
    }
    
    // Password change validation (only if user is trying to change password)
    if (!empty($newPassword)) {
        // Verify current password
        if (empty($currentPassword) || !password_verify($currentPassword, $user['password'])) {
            $errors[] = "Current password is incorrect";
        }
        
        // Validate new password
        if (strlen($newPassword) < 8) {
            $errors[] = "New password must be at least 8 characters long";
        }
        
        // Confirm passwords match
        if ($newPassword !== $confirmPassword) {
            $errors[] = "New passwords do not match";
        }
    }
    
    // If no errors, update the profile
    if (empty($errors)) {
        $userData = [
            'name' => $name,
            'email' => $email,
            'department' => $department,
            'reg_no' => $reg_no
        ];
        
        // Add password to update data if provided
        if (!empty($newPassword)) {
            $userData['password'] = $newPassword;
        }
        
        // Update user profile
        if ($userModel->update($userId, $userData)) {
            // Update session name if it changed
            if ($name !== $user['name']) {
                $_SESSION['user_name'] = $name;
            }
            
            setFlashMessage("Profile updated successfully", "success");
            redirect('profile.php');
        } else {
            $errors[] = "Failed to update profile. Please try again.";
        }
    }
}

// Include header
include_once 'app/views/layouts/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="h4 mb-0"><i class="fas fa-user-circle me-2"></i>My Profile</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="profile.php">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="department" class="form-label">Department</label>
                                <input type="text" class="form-control" id="department" name="department" value="<?php echo htmlspecialchars($user['department']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="reg_no" class="form-label">Registration Number</label>
                                <input type="text" class="form-control" id="reg_no" name="reg_no" value="<?php echo htmlspecialchars($user['reg_no']); ?>" required>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        <h4 class="h5 mb-3">Change Password (Optional)</h4>
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                            <div class="form-text">Enter your current password to confirm changes or to set a new password.</div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="dashboard.php" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light">
                    <div class="small text-muted">
                        <strong>Account Created:</strong> <?php echo formatDate($user['created_at']); ?>
                        <br>
                        <strong>Email Status:</strong> 
                        <?php if ($user['is_verified']): ?>
                            <span class="text-success">Verified</span>
                        <?php else: ?>
                            <span class="text-danger">Not Verified</span>
                            <a href="verify.php?resend=1" class="ms-2 small">Resend Verification Email</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'app/views/layouts/footer.php'; ?>