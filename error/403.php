<?php
/**
 * 403 Error Page
 * 
 * This page is displayed when access to a resource is forbidden.
 */

$pageTitle = "Access Forbidden";
require_once '../includes/init.php';
include_once '../app/views/layouts/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-template">
                <h1 class="display-1">403</h1>
                <h2>Access Forbidden</h2>
                <div class="error-details mb-4">
                    Sorry, you don't have permission to access this resource.
                </div>
                <div class="error-actions">
                    <a href="/" class="btn btn-primary btn-lg">
                        <i class="fas fa-home me-2"></i>Go Home
                    </a>
                    <a href="javascript:history.back()" class="btn btn-secondary btn-lg ms-2">
                        <i class="fas fa-arrow-left me-2"></i>Go Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../app/views/layouts/footer.php'; ?> 