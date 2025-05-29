<?php
/**
 * Login Page
 * 
 * This page handles user authentication.
 */

// Include initialization file
require_once 'includes/init.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Set page title
$pageTitle = 'Login';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid request. Please try again.', 'danger');
        redirect('login.php');
    }
    
    // Get and validate input
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Simple validation
    if (empty($email) || empty($password)) {
        setFlashMessage('Please enter both email and password.', 'warning');
    } else {
        // Attempt to authenticate user
        $result = $userModel->authenticate($email, $password);
        
        if ($result === false) {
            // Authentication failed
            setFlashMessage('Invalid email or password.', 'danger');
        } elseif ($result['verified'] === false) {
            // User not verified
            setFlashMessage('Please verify your email before logging in.', 'warning');
        } else {
            // Authentication successful
            $user = $result['user'];
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                redirect('admin/index.php');
            } else {
                redirect('dashboard.php');
            }
        }
    }
}

// Include header
include_once 'app/views/layouts/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-form">
        <h1 class="h3 mb-3 fw-normal text-center">Login to Your Account</h1>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="login.php">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mb-3">
                        <button class="btn btn-primary" type="submit">Login</button>
                    </div>
                    
                    <div class="text-center">
                        <p>Don't have an account? <a href="register.php">Register</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once 'app/views/layouts/footer.php';
?> 