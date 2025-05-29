<?php
/**
 * Event Details Page
 * 
 * This page displays details of a specific event and allows users to register for it.
 */

// Include initialization file
require_once 'includes/init.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('Invalid event ID.', 'danger');
    redirect('events.php');
}

$eventId = $_GET['id'];

// Get event details
$event = $eventModel->getById($eventId);

// Check if event exists
if (!$event) {
    setFlashMessage('Event not found.', 'danger');
    redirect('events.php');
}

// Set page title
$pageTitle = $event['title'];

// Check if user is already registered for this event
$isRegistered = false;
if (isLoggedIn()) {
    $isRegistered = $registrationModel->isUserRegistered($_SESSION['user_id'], $eventId);
}

// Check if event is full
$isFull = $eventModel->isFull($eventId);

// Check if event date is in the past
$isPast = isEventPast($event['date'], $event['time']);

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid request. Please try again.', 'danger');
        redirect('event_details.php?id=' . $eventId);
    }
    
    // Check if already registered
    if ($isRegistered) {
        setFlashMessage('You are already registered for this event.', 'warning');
        redirect('event_details.php?id=' . $eventId);
    }
    
    // Check if event is full
    if ($isFull) {
        setFlashMessage('This event is already full.', 'warning');
        redirect('event_details.php?id=' . $eventId);
    }
    
    // Check if event is in the past
    if ($isPast) {
        setFlashMessage('This event has already taken place.', 'warning');
        redirect('event_details.php?id=' . $eventId);
    }
    
    // Get registration data
    $teamName = null;
    $members = null;
    
    // Handle team-based events
    if ($event['team_based']) {
        $teamName = sanitizeInput($_POST['team_name']);
        
        // Validate team name
        if (empty($teamName)) {
            setFlashMessage('Team name is required for team events.', 'warning');
            redirect('event_details.php?id=' . $eventId);
        }
        
        // Get team members
        $teamMembers = [];
        if (isset($_POST['team_members']) && is_array($_POST['team_members'])) {
            foreach ($_POST['team_members'] as $member) {
                $member = sanitizeInput($member);
                if (!empty($member)) {
                    $teamMembers[] = $member;
                }
            }
        }
        
        // Encode team members as JSON
        $members = json_encode($teamMembers);
    }
    
    // Prepare registration data
    $registrationData = [
        'user_id' => $_SESSION['user_id'],
        'event_id' => $eventId,
        'team_name' => $teamName,
        'members' => $members,
        'status' => 'pending'
    ];
    
    // Register for the event
    $registrationId = $registrationModel->register($registrationData);
    
    if ($registrationId) {
        // Get user data for email
        $user = $userModel->findById($_SESSION['user_id']);
        
        // Send confirmation email
        $mailer->sendEventRegistrationConfirmation([
            'name' => $user['name'],
            'email' => $user['email']
        ], [
            'title' => $event['title'],
            'venue' => $event['venue'],
            'room_no' => $event['room_no'],
            'date' => $event['date'],
            'time' => $event['time']
        ]);
        
        setFlashMessage('You have successfully registered for this event. A confirmation email has been sent.', 'success');
        redirect('my_registrations.php');
    } else {
        setFlashMessage('Registration failed. Please try again.', 'danger');
    }
}

// Calculate remaining spots
$participantCount = $eventModel->getParticipantCount($eventId);
$remainingSpots = $event['max_participants'] - $participantCount;

// Include header
include_once 'app/views/layouts/header.php';
?>

<div class="event-header">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <h1 class="mb-2"><?php echo htmlspecialchars($event['title']); ?></h1>
                <div class="mb-3">
                    <?php echo getCategoryBadge($event['category']); ?>
                    <?php if ($isRegistered): ?>
                        <span class="badge bg-success ms-2">You are registered</span>
                    <?php endif; ?>
                    <?php if ($isFull): ?>
                        <span class="badge bg-danger ms-2">Full</span>
                    <?php endif; ?>
                    <?php if ($isPast): ?>
                        <span class="badge bg-secondary ms-2">Past Event</span>
                    <?php endif; ?>
                </div>
                <p class="lead mb-0">Organized by <?php echo htmlspecialchars($event['creator_name']); ?></p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <?php if (!isLoggedIn()): ?>
                    <a href="login.php" class="btn btn-light">Login to Register</a>
                <?php elseif (isAdmin()): ?>
                    <a href="admin/event_create.php?edit=<?php echo $eventId; ?>" class="btn btn-light">Edit Event</a>
                <?php elseif (!$isRegistered && !$isFull && !$isPast): ?>
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#registrationModal">
                        Register Now
                    </button>
                <?php elseif ($isRegistered): ?>
                    <a href="my_registrations.php" class="btn btn-light">View My Registration</a>
                <?php else: ?>
                    <button class="btn btn-light" disabled>
                        <?php echo $isFull ? 'Event Full' : 'Registration Closed'; ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <div class="col-md-8">
            <div class="event-info">
                <h3 class="mb-3">About This Event</h3>
                <div class="mb-4">
                    <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                </div>
                
                <h3 class="mb-3">Event Details</h3>
                <ul class="list-group mb-4">
                    <li class="list-group-item">
                        <i class="fas fa-calendar-day event-detail-icon"></i>
                        <strong>Date:</strong> <?php echo formatDate($event['date']); ?>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-clock event-detail-icon"></i>
                        <strong>Time:</strong> <?php echo formatTime($event['time']); ?>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-map-marker-alt event-detail-icon"></i>
                        <strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-door-open event-detail-icon"></i>
                        <strong>Room Number:</strong> <?php echo htmlspecialchars($event['room_no']); ?>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-users event-detail-icon"></i>
                        <strong>Registration Type:</strong> <?php echo $event['team_based'] ? 'Team Registration' : 'Individual Registration'; ?>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="event-info">
                <h5 class="mb-3">Registration Status</h5>
                <div class="progress mb-3" style="height: 20px;">
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
                <p class="text-center mb-4">
                    <?php if ($isFull): ?>
                        <span class="text-danger">Event is full</span>
                    <?php else: ?>
                        <span class="text-success"><?php echo $remainingSpots; ?> spots remaining</span>
                    <?php endif; ?>
                </p>
                
                <h5 class="mb-3">Share This Event</h5>
                <div class="d-flex justify-content-around mb-4">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(APP_URL . '/event_details.php?id=' . $eventId); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fab fa-facebook-f"></i> Share
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode('Join ' . $event['title'] . ' at PIETECH Events!'); ?>&url=<?php echo urlencode(APP_URL . '/event_details.php?id=' . $eventId); ?>" target="_blank" class="btn btn-outline-info btn-sm">
                        <i class="fab fa-twitter"></i> Tweet
                    </a>
                    <a href="mailto:?subject=<?php echo urlencode('Join ' . $event['title'] . ' at PIETECH Events!'); ?>&body=<?php echo urlencode('Check out this event: ' . APP_URL . '/event_details.php?id=' . $eventId); ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-envelope"></i> Email
                    </a>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="events.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Events
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="admin/event_create.php?edit=<?php echo $eventId; ?>" class="btn btn-outline-warning">
                            <i class="fas fa-edit me-2"></i>Edit this Event
                        </a>
                    <?php elseif (isLoggedIn() && !$isRegistered && !$isFull && !$isPast): ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registrationModal">
                            <i class="fas fa-clipboard-check me-2"></i>Register for this Event
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (isLoggedIn() && !$isRegistered && !$isFull && !$isPast): ?>
<!-- Registration Modal -->
<div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registrationModalLabel">Register for <?php echo htmlspecialchars($event['title']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="event_details.php?id=<?php echo $eventId; ?>">
                <div class="modal-body">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    
                    <p>You are registering for:</p>
                    <ul class="mb-3">
                        <li><strong>Event:</strong> <?php echo htmlspecialchars($event['title']); ?></li>
                        <li><strong>Date:</strong> <?php echo formatDate($event['date']); ?></li>
                        <li><strong>Time:</strong> <?php echo formatTime($event['time']); ?></li>
                        <li><strong>Location:</strong> <?php echo htmlspecialchars($event['venue'] . ' (Room ' . $event['room_no'] . ')'); ?></li>
                    </ul>
                    
                    <?php if ($event['team_based']): ?>
                        <div class="mb-3">
                            <label for="team_name" class="form-label">Team Name</label>
                            <input type="text" class="form-control" id="team_name" name="team_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Team Members</label>
                            <p class="text-muted small">Enter the names of your team members (you are already included as team leader)</p>
                            
                            <div id="team_members_container">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" name="team_members[]" placeholder="Member 1">
                                    <button type="button" class="btn btn-outline-secondary" onclick="removeTeamMember(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addTeamMember()">
                                <i class="fas fa-plus me-1"></i>Add Another Member
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirm_registration" required>
                        <label class="form-check-label" for="confirm_registration">
                            I confirm that I will attend this event and follow all event guidelines.
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addTeamMember() {
    const container = document.getElementById('team_members_container');
    const memberCount = container.querySelectorAll('.input-group').length + 1;
    
    const memberInput = document.createElement('div');
    memberInput.className = 'input-group mb-2';
    memberInput.innerHTML = `
        <input type="text" class="form-control" name="team_members[]" placeholder="Member ${memberCount}">
        <button type="button" class="btn btn-outline-secondary" onclick="removeTeamMember(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(memberInput);
}

function removeTeamMember(button) {
    const inputGroup = button.closest('.input-group');
    inputGroup.remove();
    
    // Reorder placeholders
    const container = document.getElementById('team_members_container');
    const inputs = container.querySelectorAll('.input-group input');
    
    inputs.forEach((input, index) => {
        input.placeholder = `Member ${index + 1}`;
    });
}
</script>
<?php endif; ?>

<?php
// Include footer
include_once 'app/views/layouts/footer.php';
?>
