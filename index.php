<?php
/**
 * Homepage
 * 
 * This is the main landing page of the PIETECH Events Platform.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include initialization file
require_once 'includes/init.php';

// Set page title
$pageTitle = 'Home';

// Get upcoming events
$upcomingEvents = $eventModel->getUpcoming();

// Include header
include_once 'app/views/layouts/header.php';
?>

<!-- Hero Section -->
<section class="hero-section" style="background: url('images/slide-1.png') no-repeat center center; background-size: cover; position: relative; padding: 6rem 0; margin-bottom: 3rem;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1;"></div>
    <div class="container text-center" style="position: relative; z-index: 2;">
        <h1 class="hero-title mb-4">Welcome to PIETECH College Events Platform</h1>
        <p class="lead mb-4">Discover, register, and participate in exciting technical, cultural, and sports events.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="events.php" class="btn btn-light btn-lg">Browse Events</a>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-outline-light btn-lg">Sign Up Now</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Featured Events Section -->
<section class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Upcoming Events</h2>
        <a href="events.php" class="btn btn-primary">View All Events</a>
    </div>
    
    <?php if (empty($upcomingEvents)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No upcoming events available at the moment. Please check back later.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php 
            // Display up to 3 upcoming events
            $count = 0;
            foreach ($upcomingEvents as $event): 
                if ($count >= 3) break;
            ?>
                <div class="col">
                    <div class="card h-100 event-card">
                        <div class="card-header">
                            <?php echo getCategoryBadge($event['category']); ?>
                            <small class="text-muted ms-2">
                                <?php echo formatDate($event['date']); ?> at <?php echo formatTime($event['time']); ?>
                            </small>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                            <p class="card-text">
                                <?php echo nl2br(substr(htmlspecialchars($event['description']), 0, 150) . '...'); ?>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($event['venue']); ?>
                                </small>
                                <a href="event_details.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                $count++;
                endforeach; 
            ?>
        </div>
    <?php endif; ?>
</section>

<!-- Features Section -->
<section class="bg-light py-5 mb-5">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose PIETECH Events Platform?</h2>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-calendar-check fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">Easy Event Management</h5>
                        <p class="card-text">Create, manage, and organize events effortlessly with our intuitive platform.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-users fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">Team or Individual Registration</h5>
                        <p class="card-text">Register as a team or individual participant based on event requirements.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-check-circle fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">Attendance Tracking</h5>
                        <p class="card-text">Efficiently track and manage participant attendance for all your events.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="container mb-5 text-center">
    <div class="p-5 bg-primary text-white rounded">
        <h2 class="mb-3">Ready to Get Started?</h2>
        <p class="lead mb-4">Join PIETECH College today and discover amazing events!</p>
        <?php if (!isLoggedIn()): ?>
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="register.php" class="btn btn-light btn-lg px-4">Sign Up</a>
                <a href="login.php" class="btn btn-outline-light btn-lg px-4">Login</a>
            </div>
        <?php else: ?>
            <a href="events.php" class="btn btn-light btn-lg px-4">Browse Events</a>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include_once 'app/views/layouts/footer.php';
?> 