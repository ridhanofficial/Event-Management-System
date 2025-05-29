<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>PIETECH Events Platform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>/public/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo getBaseUrl(); ?>/index.php">
                <img src="<?php echo getBaseUrl(); ?>/images/logo.png" alt="PIETECH Logo" height="40" class="d-inline-block align-top">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getBaseUrl(); ?>/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getBaseUrl(); ?>/events.php">Events</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo getBaseUrl(); ?>/admin/dashboard.php">Admin Dashboard</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <!-- Admin Navigation -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                    Admin
                                </a>                                <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                    <li><a class="dropdown-item" href="<?php echo getBaseUrl(); ?>/admin/events.php">Manage Events</a></li>
                                    <li><a class="dropdown-item" href="<?php echo getBaseUrl(); ?>/admin/users.php">Manage Users</a></li>
                                    <li><a class="dropdown-item" href="<?php echo getBaseUrl(); ?>/admin/registrations.php">Manage Registrations</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo getBaseUrl(); ?>/admin/attendance.php">Attendance Dashboard</a></li>
                                    <li><a class="dropdown-item" href="<?php echo getBaseUrl(); ?>/admin/select_winners.php">
                                        <i class="fas fa-trophy me-2"></i>Select Winners</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <!-- Student Navigation -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo getBaseUrl(); ?>/dashboard.php">Dashboard</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo $_SESSION['user_name']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="<?php echo getBaseUrl(); ?>/profile.php">My Profile</a></li>
                                <?php if (!isAdmin()): ?>
                                    <li><a class="dropdown-item" href="<?php echo getBaseUrl(); ?>/my_registrations.php">My Registrations</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo getBaseUrl(); ?>/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo getBaseUrl(); ?>/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo getBaseUrl(); ?>/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="container mt-4">
        <?php echo displayFlashMessage(); ?>