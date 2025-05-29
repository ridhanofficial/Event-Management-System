<?php
/**
 * Admin Dashboard
 * 
 * This page displays the admin dashboard with summary statistics and quick actions.
 */

// Include initialization file
require_once '../includes/init.php';

// Require admin privileges
requireAdmin();

// Set page title
$pageTitle = 'Admin Dashboard';

// Get statistics for the dashboard
$totalUsers = count($userModel->getAll());
$totalEvents = count($eventModel->getAll());

// Get upcoming events
$upcomingEvents = $eventModel->getUpcoming();

// Include header
include_once '../app/views/layouts/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-0">Admin Dashboard</h1>
            <p class="lead">Welcome to the PIETECH Events Platform admin panel</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="../index.php" class="btn btn-outline-primary">
                <i class="fas fa-home me-2"></i>Return to Site
            </a>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Users</h6>
                            <h2 class="mt-2 mb-0"><?php echo $totalUsers; ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="users.php" class="text-white text-decoration-none small">
                        Manage Users <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Events</h6>
                            <h2 class="mt-2 mb-0"><?php echo $totalEvents; ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="events.php" class="text-white text-decoration-none small">
                        Manage Events <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Upcoming Events</h6>
                            <h2 class="mt-2 mb-0"><?php echo count($upcomingEvents); ?></h2>
                        </div>
                        <div>
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="events.php" class="text-white text-decoration-none small">
                        View Schedule <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Pending Approvals</h6>
                            <h2 class="mt-2 mb-0">-</h2>
                        </div>
                        <div>
                            <i class="fas fa-clipboard-check fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="registrations.php" class="text-white text-decoration-none small">
                        Review Registrations <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="event_create.php" class="btn btn-outline-primary d-block py-3">
                                <i class="fas fa-plus-circle fa-2x d-block mb-2 mx-auto"></i>
                                Create New Event
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="registrations.php" class="btn btn-outline-primary d-block py-3">
                                <i class="fas fa-clipboard-list fa-2x d-block mb-2 mx-auto"></i>
                                Manage Registrations
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="attendance.php" class="btn btn-outline-primary d-block py-3">
                                <i class="fas fa-user-check fa-2x d-block mb-2 mx-auto"></i>
                                Attendance Dashboard
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="users.php" class="btn btn-outline-primary d-block py-3">
                                <i class="fas fa-user-shield fa-2x d-block mb-2 mx-auto"></i>
                                User Management
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="select_winners.php" class="btn btn-outline-success d-block py-3">
                                <i class="fas fa-trophy fa-2x d-block mb-2 mx-auto"></i>
                                Select Event Winners
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Events -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Upcoming Events</h5>
                    <a href="events.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($upcomingEvents)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No upcoming events. <a href="event_create.php" class="alert-link">Create one now!</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Date & Time</th>
                                        <th>Venue</th>
                                        <th>Registrations</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Display up to 5 upcoming events
                                    $count = 0;
                                    foreach ($upcomingEvents as $event):
                                        if ($count >= 5) break;
                                        
                                        // Get participant count
                                        $participantCount = $eventModel->getParticipantCount($event['id']);
                                    ?>
                                        <tr>
                                            <td>
                                                <a href="../event_details.php?id=<?php echo $event['id']; ?>">
                                                    <?php echo htmlspecialchars($event['title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo getCategoryBadge($event['category']); ?></td>
                                            <td>
                                                <?php echo formatDate($event['date']); ?><br>
                                                <small class="text-muted"><?php echo formatTime($event['time']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($event['venue']); ?><br>
                                                <small class="text-muted">Room: <?php echo htmlspecialchars($event['room_no']); ?></small>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <?php 
                                                    $percentFull = ($participantCount / $event['max_participants']) * 100;
                                                    $progressClass = $percentFull >= 90 ? 'bg-danger' : ($percentFull >= 70 ? 'bg-warning' : 'bg-success');
                                                    ?>
                                                    <div class="progress-bar <?php echo $progressClass; ?>" role="progressbar" 
                                                         style="width: <?php echo $percentFull; ?>%;" 
                                                         aria-valuenow="<?php echo $participantCount; ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="<?php echo $event['max_participants']; ?>">
                                                        <?php echo $participantCount; ?>/<?php echo $event['max_participants']; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="event_edit.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="registrations.php?event_id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-users"></i> Registrations
                                                </a>
                                            </td>
                                        </tr>
                                    <?php 
                                        $count++;
                                        endforeach; 
                                    ?>
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
include_once '../app/views/layouts/footer.php';
?> 