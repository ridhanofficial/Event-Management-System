<?php
/**
 * Email Verification Page
 * 
 * This page handles email verification using the token sent to the user's email.
 */

// Include initialization file
require_once 'includes/init.php';

// Set page title
$pageTitle = 'Email Verification';

// Check if verification token is provided
$token = isset($_GET['token']) ? $_GET['token'] : null;
$verified = false;
$error = false;

if ($token) {
    // Attempt to verify the email
    $verified = $userModel->verifyEmail($token);
    
    if (!$verified) {
        $error = true;
    }
}

// Include header
include_once 'app/views/layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body text-center p-5">
                    <?php if ($token && $verified): ?>
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success fa-5x"></i>
                        </div>
                        <h2 class="mb-3">Email Verified!</h2>
                        <p class="mb-4">Your email has been successfully verified. You can now log in to your account.</p>
                        <a href="login.php" class="btn btn-primary">Log In</a>
                    <?php elseif ($token && $error): ?>
                        <div class="mb-4">
                            <i class="fas fa-times-circle text-danger fa-5x"></i>
                        </div>
                        <h2 class="mb-3">Verification Failed</h2>
                        <p class="mb-4">The verification link is invalid or has expired. Please try registering again or contact support for assistance.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="register.php" class="btn btn-primary">Register Again</a>
                            <a href="index.php" class="btn btn-outline-secondary">Go to Home</a>
                        </div>
                    <?php else: ?>
                        <div class="mb-4">
                            <i class="fas fa-envelope text-primary fa-5x"></i>
                        </div>
                        <h2 class="mb-3">Email Verification Required</h2>
                        <p class="mb-4">Please check your email for a verification link to activate your account. If you don't receive the email within a few minutes, check your spam folder.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="index.php" class="btn btn-primary">Back to Home</a>
                            <a href="login.php" class="btn btn-outline-secondary">Go to Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once 'app/views/layouts/footer.php';
?> 