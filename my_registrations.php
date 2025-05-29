<?php
/**
 * My Registrations Page
 * 
 * This page displays all registrations for the logged-in user.
 */

// Include initialization file
require_once 'includes/init.php';

// Require login
requireLogin();

// Set page title
$pageTitle = 'My Registrations';

// Get user registrations
$userId = $_SESSION['user_id'];
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
    <h1 class="mb-4">My Registrations</h1>
    
    <!-- Alternative cancellation info alert -->
    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Having trouble cancelling a registration?</strong> You can now use our <a href="test_cancel_registration.php" class="alert-link">alternative cancellation method</a>.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    
    <ul class="nav nav-tabs mb-4" id="registrationsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab" aria-controls="upcoming" aria-selected="true">
                Upcoming Events (<?php echo count($upcomingRegistrations); ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button" role="tab" aria-controls="past" aria-selected="false">
                Past Events (<?php echo count($pastRegistrations); ?>)
            </button>
        </li>
    </ul>
    
    <div class="tab-content" id="registrationsTabContent">
        <!-- Upcoming Events Tab -->
        <div class="tab-pane fade show active" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
            <?php if (empty($upcomingRegistrations)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>You don't have any upcoming registrations.
                    <a href="events.php" class="alert-link">Browse events</a> to register for upcoming events.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Registration Type</th>
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
                                    <td>
                                        <?php if ($registration['team_name']): ?>
                                            <span class="badge bg-info">Team: <?php echo htmlspecialchars($registration['team_name']); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Individual</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo getStatusBadge($registration['status']); ?></td>
                                    <td>
                                        <a href="registration_details.php?id=<?php echo $registration['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> Details
                                        </a>
                                        <?php if ($registration['status'] !== 'rejected'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#cancelModal" 
                                                    data-registration-id="<?php echo $registration['id']; ?>"
                                                    data-event-title="<?php echo htmlspecialchars($registration['event_title']); ?>">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Past Events Tab -->
        <div class="tab-pane fade" id="past" role="tabpanel" aria-labelledby="past-tab">
            <?php if (empty($pastRegistrations)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>You don't have any past registrations.
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
                                <th>Attendance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pastRegistrations as $registration): ?>
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
                                    <td><?php echo getAttendanceBadge($registration['check_in']); ?></td>
                                    <td>
                                        <a href="registration_details.php?id=<?php echo $registration['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> Details
                                        </a>
                                        <?php if (!$registration['feedback_submitted']): ?>
                                            <a href="feedback.php?registration_id=<?php echo $registration['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-comment"></i> Feedback
                                            </a>
                                        <?php endif; ?>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>                <form method="POST" action="cancel_registration_logged.php" id="cancelRegistrationForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="registration_id" id="registrationId">
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </form>
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
            // Get the button that triggered the modal
            const button = event.relatedTarget;
            
            // Extract data attributes
            const registrationId = button.getAttribute('data-registration-id');
            const eventTitle = button.getAttribute('data-event-title');
            
            console.log("Modal opening for registration:", registrationId, "Event:", eventTitle);
            
            // Get the elements inside the modal
            const modalRegistrationId = document.getElementById('registrationId');
            const modalEventTitle = document.getElementById('eventTitle');
            
            if (modalRegistrationId && modalEventTitle) {
                // Set the values
                modalRegistrationId.value = registrationId;
                modalEventTitle.textContent = eventTitle;
                console.log("Modal values set successfully");
            } else {
                console.error("Modal elements not found");
            }
        });
        
        // Set up form submission
        const cancelForm = document.getElementById('cancelRegistrationForm');
        if (cancelForm) {
            cancelForm.addEventListener('submit', function(event) {
                console.log("Form submitting with registration ID:", document.getElementById('registrationId').value);
            });
        }
    } else {
        console.error("Cancel modal element not found");
    }
    
    // Add a global click handler for cancel buttons to ensure they're working
    document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#cancelModal"]').forEach(function(button) {
        button.addEventListener('click', function() {
            console.log("Cancel button clicked for registration:", this.getAttribute('data-registration-id'));
        });
    });
});
</script>

<?php
// Include footer
include_once 'app/views/layouts/footer.php';
?> 