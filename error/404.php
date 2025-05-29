<?php
/**
 * 404 Error Page
 * 
 * This page is displayed when a requested page cannot be found.
 */

$pageTitle = "Page Not Found";
require_once '../includes/init.php';
include_once '../app/views/layouts/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-template">
                <h1 class="display-1">404</h1>
                <h2>Page Not Found</h2>
                <div class="error-details mb-4">
                    Sorry, the page you requested could not be found.
                </div>
                <div class="error-actions">
                    <a href="/" class="btn btn-primary btn-lg">
                        <i class="fas fa-home me-2"></i>Go Home
                    </a>
                    <a href="/events.php" class="btn btn-secondary btn-lg ms-2">
                        <i class="fas fa-calendar-alt me-2"></i>View Events
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../app/views/layouts/footer.php'; ?> 