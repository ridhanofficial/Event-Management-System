<?php
/**
 * Admin Registrations Management
 * 
 * This page allows administrators to view and manage event registrations.
 */

// Include initialization file
require_once '../includes/init.php';

// Require admin privileges
requireAdmin();

// Set page title
$pageTitle = 'Manage Registrations';

// Handle registration status changes
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $registrationId = $_GET['id'];
    $action = $_GET['action'];
    $status = '';
    
    switch ($action) {
        case 'approve':
            $status = 'approved';
            break;
            
        case 'reject':
            $status = 'rejected';
            break;
            
        case 'pending':
            $status = 'pending';
            break;
            
        case 'cancel':
            $status = 'cancelled';
            break;
            
        default:
            setFlashMessage('Invalid action.', 'danger');
            redirect('registrations.php');
    }
    
    if ($registrationModel->updateStatus($registrationId, $status)) {
        setFlashMessage('Registration status updated successfully.', 'success');
    } else {
        setFlashMessage('Failed to update registration status.', 'danger');
    }
    
    // Redirect back to the same page with filters preserved
    $redirectUrl = 'registrations.php';
    if (isset($_GET['event_id'])) {
        $redirectUrl .= '?event_id=' . $_GET['event_id'];
    } elseif (isset($_GET['user_id'])) {
        $redirectUrl .= '?user_id=' . $_GET['user_id'];
    }
    
    redirect($redirectUrl);
}

// Filter registrations by event or user if specified
$registrations = [];
$filterTitle = 'All Registrations';

if (isset($_GET['event_id']) && is_numeric($_GET['event_id'])) {
    $eventId = $_GET['event_id'];
    $event = $eventModel->getById($eventId);
    
    if ($event) {
        $registrations = $registrationModel->getByEventId($eventId);
        $filterTitle = 'Registrations for: ' . htmlspecialchars($event['title']);
    } else {
        setFlashMessage('Event not found.', 'danger');
        redirect('registrations.php');
    }
} elseif (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $userId = $_GET['user_id'];
    $user = $userModel->findById($userId);
    
    if ($user) {
        $registrations = $registrationModel->getByUserId($userId);
        $filterTitle = 'Registrations by: ' . htmlspecialchars($user['name']);
    } else {
        setFlashMessage('User not found.', 'danger');
        redirect('registrations.php');
    }
} else {
    // Get all registrations
    $registrations = $registrationModel->getAll();
}

// Include header
include_once '../app/views/layouts/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo $filterTitle; ?></h1>
        <div>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
    
    <!-- Registrations Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Registration List</h5>
                </div>
                <div class="col-auto">
                    <div class="input-group">
                        <input type="text" id="registrationSearch" class="form-control" placeholder="Search registrations...">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Event</th>
                            <th scope="col">Participant</th>
                            <th scope="col">Registration Type</th>
                            <th scope="col">Registered On</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($registrations) > 0): ?>
                            <?php foreach ($registrations as $index => $registration): ?>
                                <?php 
                                    // Get event and user details
                                    $event = $eventModel->getById($registration['event_id']);
                                    $user = $userModel->findById($registration['user_id']);
                                    
                                    // Skip if event or user not found (shouldn't happen, but just in case)
                                    if (!$event || !$user) continue;
                                    
                                    // Determine registration type
                                    $registrationType = $event['team_based'] ? 
                                        ($registration['team_name'] ? 'Team: ' . htmlspecialchars($registration['team_name']) : 'Individual') : 
                                        'Individual';
                                ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <a href="../event_details.php?id=<?php echo $event['id']; ?>" class="fw-bold text-decoration-none">
                                            <?php echo htmlspecialchars($event['title']); ?>
                                        </a>
                                        <div class="small text-muted"><?php echo formatDate($event['date']); ?> at <?php echo formatTime($event['time']); ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($user['name']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </td>
                                    <td>
                                        <?php echo $registrationType; ?>
                                        <?php if ($registration['members']): ?>
                                            <button class="btn btn-sm btn-link p-0 ms-1" type="button" data-bs-toggle="modal" 
                                                    data-bs-target="#teamModal<?php echo $registration['id']; ?>">
                                                <i class="fas fa-users"></i>
                                            </button>
                                            
                                            <!-- Team Members Modal -->
                                            <div class="modal fade" id="teamModal<?php echo $registration['id']; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Team Members</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <h6 class="mb-3"><?php echo htmlspecialchars($registration['team_name']); ?></h6>
                                                            <ul class="list-group">
                                                                <?php 
                                                                    $members = json_decode($registration['members'], true);
                                                                    if (is_array($members)) {
                                                                        foreach ($members as $member) {
                                                                            echo '<li class="list-group-item">' . 
                                                                                htmlspecialchars($member['name']) . 
                                                                                ' <small class="text-muted">(' . 
                                                                                htmlspecialchars($member['reg_no']) . ')</small></li>';
                                                                        }
                                                                    }
                                                                ?>
                                                            </ul>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatDate($registration['created_at']); ?></td>
                                    <td>
                                        <span class="badge <?php echo getStatusBadgeClass($registration['status']); ?>">
                                            <?php echo ucfirst($registration['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <!-- View registration details -->
                                                <li>
                                                    <a class="dropdown-item" href="../registration_details.php?id=<?php echo $registration['id']; ?>">
                                                        <i class="fas fa-eye me-2"></i>View Details
                                                    </a>
                                                </li>
                                                
                                                <!-- Status management -->
                                                <li><hr class="dropdown-divider"></li>
                                                
                                                <?php if ($registration['status'] !== 'approved'): ?>
                                                    <li>
                                                        <a class="dropdown-item text-success" href="registrations.php?action=approve&id=<?php echo $registration['id']; ?><?php echo isset($_GET['event_id']) ? '&event_id=' . $_GET['event_id'] : ''; ?><?php echo isset($_GET['user_id']) ? '&user_id=' . $_GET['user_id'] : ''; ?>">
                                                            <i class="fas fa-check-circle me-2"></i>Approve
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                <?php if ($registration['status'] !== 'rejected'): ?>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="registrations.php?action=reject&id=<?php echo $registration['id']; ?><?php echo isset($_GET['event_id']) ? '&event_id=' . $_GET['event_id'] : ''; ?><?php echo isset($_GET['user_id']) ? '&user_id=' . $_GET['user_id'] : ''; ?>">
                                                            <i class="fas fa-times-circle me-2"></i>Reject
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                <?php if ($registration['status'] !== 'pending'): ?>
                                                    <li>
                                                        <a class="dropdown-item text-warning" href="registrations.php?action=pending&id=<?php echo $registration['id']; ?><?php echo isset($_GET['event_id']) ? '&event_id=' . $_GET['event_id'] : ''; ?><?php echo isset($_GET['user_id']) ? '&user_id=' . $_GET['user_id'] : ''; ?>">
                                                            <i class="fas fa-clock me-2"></i>Mark as Pending
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                <?php if ($registration['status'] !== 'cancelled'): ?>
                                                    <li>
                                                        <a class="dropdown-item text-secondary" href="registrations.php?action=cancel&id=<?php echo $registration['id']; ?><?php echo isset($_GET['event_id']) ? '&event_id=' . $_GET['event_id'] : ''; ?><?php echo isset($_GET['user_id']) ? '&user_id=' . $_GET['user_id'] : ''; ?>">
                                                            <i class="fas fa-ban me-2"></i>Cancel Registration
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">No registrations found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Get the appropriate badge class for a registration status
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'bg-warning text-dark';
        case 'approved':
            return 'bg-success';
        case 'rejected':
            return 'bg-danger';
        case 'cancelled':
            return 'bg-secondary';
        default:
            return 'bg-info';
    }
}
?>

<script>
    // Registration search functionality
    document.getElementById('registrationSearch').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('tbody tr');
        
        tableRows.forEach(row => {
            const event = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const participant = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const type = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
            
            if (event.includes(searchValue) || participant.includes(searchValue) || type.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

<?php include_once '../app/views/layouts/footer.php'; ?>