<?php
/**
 * Events Listing Page
 * 
 * This page displays all upcoming events with category filtering.
 */

// Include initialization file
require_once 'includes/init.php';

// Set page title
$pageTitle = 'Events';

// Get category filter from query string
$category = isset($_GET['category']) ? $_GET['category'] : null;

// Get upcoming events with optional category filter
$events = $eventModel->getUpcoming($category);

// Include header
include_once 'app/views/layouts/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-3">Upcoming Events</h1>
            <p class="lead">Discover and register for our upcoming events.</p>
        </div>
        <div class="col-md-4 text-end">
            <span id="event-counter" class="text-muted">
                Showing <?php echo count($events); ?> event<?php echo count($events) !== 1 ? 's' : ''; ?>
            </span>
        </div>
    </div>
    
    <!-- Category Filter -->
    <div class="category-filter mb-4">
        <button class="btn btn-outline-primary category-btn <?php echo !$category ? 'active' : ''; ?>" data-category="all">
            All Categories
        </button>
        <button class="btn btn-outline-primary category-btn <?php echo $category === 'technical' ? 'active' : ''; ?>" data-category="technical">
            Technical
        </button>
        <button class="btn btn-outline-primary category-btn <?php echo $category === 'cultural' ? 'active' : ''; ?>" data-category="cultural">
            Cultural
        </button>
        <button class="btn btn-outline-primary category-btn <?php echo $category === 'sports' ? 'active' : ''; ?>" data-category="sports">
            Sports
        </button>
    </div>
    
    <?php if (empty($events)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No upcoming events available at the moment. Please check back later.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($events as $event): ?>
                <div class="col event-item" data-category="<?php echo $event['category']; ?>">
                    <div class="card h-100 event-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <?php echo getCategoryBadge($event['category']); ?>
                            <?php if ($eventModel->isFull($event['id'])): ?>
                                <span class="badge bg-danger">Full</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                            <p class="card-text"><?php echo nl2br(substr(htmlspecialchars($event['description']), 0, 150) . '...'); ?></p>
                            
                            <div class="mt-3">
                                <div class="mb-2">
                                    <i class="fas fa-calendar-day text-muted me-2"></i>
                                    <?php echo formatDate($event['date']); ?>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-clock text-muted me-2"></i>
                                    <?php echo formatTime($event['time']); ?>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                    <?php echo htmlspecialchars($event['venue']); ?> (Room: <?php echo htmlspecialchars($event['room_no']); ?>)
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-users text-muted me-2"></i>
                                    <?php echo $event['team_based'] ? 'Team Event' : 'Individual Event'; ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-grid">
                                <a href="event_details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Category filter script
document.addEventListener('DOMContentLoaded', function() {
    // Handle direct URL filters
    const urlParams = new URLSearchParams(window.location.search);
    const categoryParam = urlParams.get('category');
    
    if (categoryParam) {
        const activeButton = document.querySelector(`.category-btn[data-category="${categoryParam}"]`);
        if (activeButton) {
            activeButton.classList.add('active');
        }
    }
    
    // Add click event listeners to filter buttons
    const filterButtons = document.querySelectorAll('.category-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Get category value
            const category = this.dataset.category;
            
            // Update URL without reloading the page
            if (category === 'all') {
                history.pushState({}, '', 'events.php');
            } else {
                history.pushState({}, '', `events.php?category=${category}`);
            }
            
            // Filter events using the existing JavaScript in scripts.js
            const eventCards = document.querySelectorAll('.event-item');
            eventCards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Update counter
            const visibleCount = document.querySelectorAll('.event-item[style="display: block;"], .event-item:not([style])').length;
            const counterElement = document.getElementById('event-counter');
            if (counterElement) {
                counterElement.textContent = `Showing ${visibleCount} event${visibleCount !== 1 ? 's' : ''}`;
            }
        });
    });
});
</script>

<?php
// Include footer
include_once 'app/views/layouts/footer.php';
?> 