<?php
/**
 * Registration Details Page
 * 
 * This page displays detailed information about a user's event registration.
 */

// Include initialization file
require_once 'includes/init.php';

// Require login
requireLogin();

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('Invalid registration ID.', 'danger');
    redirect('my_registrations.php');
}

$registrationId = $_GET['id'];

// Get registration details
$registration = $registrationModel->getById($registrationId);

// Check if registration exists and belongs to the current user (or user is admin)
if (!$registration || ($registration['user_id'] != $_SESSION['user_id'] && !isAdmin())) {
    setFlashMessage('Registration not found or you don\'t have permission to view it.', 'danger');
    redirect('my_registrations.php');
}

// Get event details
$event = $eventModel->getById($registration['event_id']);

// Set page title
$pageTitle = 'Registration Details';

// Decode team members if present
$teamMembers = [];
if ($registration['members']) {
    $teamMembers = json_decode($registration['members'], true);
}

// Check if event is in the past
$isPast = isEventPast($event['date'], $event['time']);

// Include header
include_once 'app/views/layouts/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-0">Registration Details</h1>
            <p class="lead">
                <a href="event_details.php?id=<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['title']); ?></a>
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="my_registrations.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to My Registrations
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Event Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Event:</div>
                        <div class="col-md-8"><?php echo htmlspecialchars($event['title']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Category:</div>
                        <div class="col-md-8"><?php echo getCategoryBadge($event['category']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Date & Time:</div>
                        <div class="col-md-8">
                            <?php echo formatDate($event['date']); ?> at <?php echo formatTime($event['time']); ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Venue:</div>
                        <div class="col-md-8"><?php echo htmlspecialchars($event['venue']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Room Number:</div>
                        <div class="col-md-8"><?php echo htmlspecialchars($event['room_no']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Event Type:</div>
                        <div class="col-md-8"><?php echo $event['team_based'] ? 'Team Event' : 'Individual Event'; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Organizer:</div>
                        <div class="col-md-8"><?php echo htmlspecialchars($event['creator_name']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Registration Status</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="mb-3">
                            <?php 
                            $statusClass = '';
                            $statusIcon = '';
                            
                            switch ($registration['status']) {
                                case 'approved':
                                    $statusClass = 'text-success';
                                    $statusIcon = 'fa-check-circle';
                                    break;
                                case 'pending':
                                    $statusClass = 'text-warning';
                                    $statusIcon = 'fa-clock';
                                    break;
                                case 'rejected':
                                    $statusClass = 'text-danger';
                                    $statusIcon = 'fa-times-circle';
                                    break;
                            }
                            ?>
                            <i class="fas <?php echo $statusIcon; ?> <?php echo $statusClass; ?> fa-3x"></i>
                        </div>
                        <h4 class="<?php echo $statusClass; ?> mb-3">
                            <?php echo ucfirst($registration['status']); ?>
                        </h4>
                        
                        <?php if ($registration['status'] === 'pending'): ?>
                            <p class="text-muted">Your registration is awaiting approval from the event organizers.</p>
                        <?php elseif ($registration['status'] === 'approved'): ?>
                            <p class="text-muted">Your registration has been approved! We look forward to seeing you at the event.</p>
                        <?php elseif ($registration['status'] === 'rejected'): ?>
                            <p class="text-muted">Unfortunately, your registration has been rejected. This could be due to capacity constraints or other reasons.</p>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <div class="fw-bold mb-2">Registration Date:</div>
                        <div><?php echo date('F j, Y g:i A', strtotime($registration['created_at'])); ?></div>
                    </div>
                    
                    <?php if ($isPast): ?>
                        <div class="mb-3">
                            <div class="fw-bold mb-2">Attendance:</div>
                            <div><?php echo getAttendanceBadge($registration['check_in']); ?></div>
                        </div>
                        
                        <?php if (!$registration['feedback_submitted']): ?>
                            <div class="d-grid">
                                <a href="feedback.php?registration_id=<?php echo $registrationId; ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-comment me-2"></i>Provide Feedback
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php elseif ($registration['status'] !== 'rejected'): ?>
                        <div class="d-grid">
                            <button type="button" class="btn btn-outline-danger" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#cancelModal" 
                                    data-registration-id="<?php echo $registrationId; ?>"
                                    data-event-title="<?php echo htmlspecialchars($event['title']); ?>">
                                <i class="fas fa-times me-2"></i>Cancel Registration
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($event['team_based'] && $registration['team_name']): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Team Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-2 fw-bold">Team Name:</div>
                        <div class="col-md-10"><?php echo htmlspecialchars($registration['team_name']); ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-2 fw-bold">Team Leader:</div>
                        <div class="col-md-10"><?php echo htmlspecialchars($registration['user_name']); ?></div>
                    </div>
                    
                    <?php if (!empty($teamMembers)): ?>
                        <div class="row">
                            <div class="col-md-2 fw-bold">Team Members:</div>
                            <div class="col-md-10">
                                <ol class="mb-0">
                                    <?php foreach ($teamMembers as $member): ?>
                                        <li><?php echo htmlspecialchars($member); ?></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Cancel Registration Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancel Registration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel your registration for <span id="eventTitle"></span>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>                <form method="POST" action="cancel_registration_logged.php" id="cancelForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="registration_id" id="registrationId">
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </form>
                <div class="mt-2 text-center">
                    <small><a href="direct_cancel.php?id=<?php echo $registrationId; ?>" class="text-muted">Having trouble? Try direct cancellation</a></small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set up cancel modal data
    const cancelModal = document.getElementById('cancelModal');
    if (cancelModal) {
        cancelModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const registrationId = button.getAttribute('data-registration-id');
            const eventTitle = button.getAttribute('data-event-title');
            
            const modalRegistrationId = document.getElementById('registrationId');
            const modalEventTitle = document.getElementById('eventTitle');
            
            modalRegistrationId.value = registrationId;
            modalEventTitle.textContent = eventTitle;
        });
    }
});
</script>

<?php
// Include footer
include_once 'app/views/layouts/footer.php';
?> 