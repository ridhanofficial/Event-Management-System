<?php
/**
 * User Dashboard
 * 
 * This page displays the user dashboard with user information and upcoming registrations.
 */

// Include initialization file
require_once 'includes/init.php';

// Require login
requireLogin();

// Set page title
$pageTitle = 'Dashboard';

// Get user data
$userId = $_SESSION['user_id'];
$user = $userModel->findById($userId);

// Get user's upcoming registrations
$registrations = $registrationModel->getByUserId($userId);

// Separate upcoming and past registrations
$upcomingRegistrations = [];
$pastRegistrations = [];

if ($registrations) {
    foreach ($registrations as $registration) {
        if (isEventPast($registration['event_date'], $registration['event_time'])) {
            $pastRegistrations[] = $registration;
        } else {
            $upcomingRegistrations[] = $registration;
        }
    }
}

// Include header
include_once 'app/views/layouts/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-user-circle dashboard-icon me-3"></i>
                        <h5 class="card-title mb-0">Profile Information</h5>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user me-2"></i> Name</span>
                            <span><?php echo htmlspecialchars($user['name']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-envelope me-2"></i> Email</span>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-building me-2"></i> Department</span>
                            <span><?php echo htmlspecialchars($user['department']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-id-card me-2"></i> Reg. Number</span>
                            <span><?php echo htmlspecialchars($user['reg_no']); ?></span>
                        </li>
                    </ul>
                    <div class="mt-3">
                        <a href="profile.php" class="btn btn-outline-primary btn-sm">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-chart-pie dashboard-icon me-3"></i>
                        <h5 class="card-title mb-0">Activity Summary</h5>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <h2 class="mb-1"><?php echo count($upcomingRegistrations); ?></h2>
                                <p class="mb-0 text-muted">Upcoming Events</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <h2 class="mb-1"><?php echo count($pastRegistrations); ?></h2>
                                <p class="mb-0 text-muted">Past Events</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <h2 class="mb-1"><?php echo count($registrations); ?></h2>
                                <p class="mb-0 text-muted">Total Registrations</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="events.php" class="btn btn-primary">Browse Events</a>
                        <a href="my_registrations.php" class="btn btn-outline-primary ms-2">View All Registrations</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-calendar-check dashboard-icon me-3"></i>
                        <h5 class="card-title mb-0">Upcoming Registered Events</h5>
                    </div>
                    
                    <?php if (empty($upcomingRegistrations)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>You don't have any upcoming registered events.
                            <a href="events.php" class="alert-link">Browse events</a> to register.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Date & Time</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcomingRegistrations as $registration): ?>
                                        <tr>
                                            <td>
                                                <a href="event_details.php?id=<?php echo $registration['event_id']; ?>">
                                                    <?php echo htmlspecialchars($registration['event_title']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php echo formatDate($registration['event_date']); ?><br>
                                                <small class="text-muted"><?php echo formatTime($registration['event_time']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($registration['event_venue']); ?><br>
                                                <small class="text-muted">Room: <?php echo htmlspecialchars($registration['event_room']); ?></small>
                                            </td>
                                            <td><?php echo getStatusBadge($registration['status']); ?></td>
                                            <td>
                                                <a href="registration_details.php?id=<?php echo $registration['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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