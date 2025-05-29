<?php
/**
 * Registration Page
 * 
 * This page handles user registration.
 */

// Include initialization file
require_once 'includes/init.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Set page title
$pageTitle = 'Register';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid request. Please try again.', 'danger');
        redirect('register.php');
    }
    
    // Get and sanitize inputs
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $department = sanitizeInput($_POST['department']);
    $regNo = sanitizeInput($_POST['reg_no']);
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($department)) {
        $errors[] = 'Department is required';
    }
    
    if (empty($regNo)) {
        $errors[] = 'Registration number is required';
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $existingUser = $userModel->findByEmail($email);
        if ($existingUser) {
            $errors[] = 'Email already in use. Please use a different email or login';
        }
    }
    
    // If no errors, create the user
    if (empty($errors)) {
        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'department' => $department,
            'reg_no' => $regNo
        ];
        
        $result = $userModel->create($userData);
        
        if ($result) {
            // Send verification email
            $mailer->sendVerificationEmail($email, $name, $result['token']);
            
            // Set success message
            setFlashMessage('Registration successful! Please check your email to verify your account.', 'success');
            
            // Redirect to login page
            redirect('login.php');
        } else {
            setFlashMessage('Registration failed. Please try again.', 'danger');
        }
    } else {
        // Display errors
        setFlashMessage(implode('<br>', $errors), 'danger');
    }
}

// Include header
include_once 'app/views/layouts/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="h3 mb-3 fw-normal text-center">Create an Account</h1>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="register.php" class="needs-validation" novalidate>
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="invalid-feedback">
                                    Please enter your full name.
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                                </div>
                                <div class="form-text">
                                    Must be at least 8 characters long.
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="invalid-feedback">
                                    Passwords do not match.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="department" class="form-label">Department</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <input type="text" class="form-control" id="department" name="department" required>
                                </div>
                                <div class="invalid-feedback">
                                    Please enter your department.
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="reg_no" class="form-label">Registration Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" class="form-control" id="reg_no" name="reg_no" required>
                                </div>
                                <div class="invalid-feedback">
                                    Please enter your registration number.
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 mb-3">
                            <button class="btn btn-primary" type="submit">Register</button>
                        </div>
                        
                        <div class="text-center">
                            <p>Already have an account? <a href="login.php">Login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript for form validation
(function() {
    'use strict';
    
    // Fetch all forms we want to apply validation to
    var forms = document.querySelectorAll('.needs-validation');
    
    // Loop over them and prevent submission
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            // Check if passwords match
            var password = document.getElementById('password');
            var confirmPassword = document.getElementById('confirm_password');
            
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
                event.preventDefault();
                event.stopPropagation();
            } else {
                confirmPassword.setCustomValidity('');
            }
            
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php
// Include footer
include_once 'app/views/layouts/footer.php';
?> 