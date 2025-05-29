<?php
/**
 * Admin Events Management
 * 
 * This page allows administrators to view, create, edit, and delete events.
 */

// Include initialization file
require_once '../includes/init.php';

// Require admin privileges
requireAdmin();

// Set page title
$pageTitle = 'Manage Events';

// Handle event deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $eventId = $_GET['delete'];
    
    // Check if the event has registrations
    $registrations = $registrationModel->getByEventId($eventId);
    
    if (count($registrations) > 0) {
        setFlashMessage('Cannot delete event: There are registrations associated with this event.', 'danger');
    } else {
        // Delete the event
        if ($eventModel->delete($eventId)) {
            setFlashMessage('Event deleted successfully.', 'success');
        } else {
            setFlashMessage('Failed to delete event.', 'danger');
        }
    }
    
    redirect('events.php');
}

// Get all events with sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date';
$order = isset($_GET['order']) ? $_GET['order'] : 'asc';

// Validate sort and order parameters
$validSortFields = ['title', 'date', 'venue', 'category'];
$validOrderValues = ['asc', 'desc'];

if (!in_array($sort, $validSortFields)) {
    $sort = 'date';
}

if (!in_array($order, $validOrderValues)) {
    $order = 'asc';
}

// Get events with sorting
$events = $eventModel->getAll($sort, $order, null);

// Include header
include_once '../app/views/layouts/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Events</h1>
        <div>
            <a href="event_create.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Create New Event
            </a>
            <a href="index.php" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
    
    <!-- Events Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">All Events</h5>
                </div>
                <div class="col-auto">
                    <div class="input-group">
                        <input type="text" id="eventSearch" class="form-control" placeholder="Search events...">
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
                            <th scope="col">
                                <a href="?sort=title&order=<?php echo $sort === 'title' && $order === 'asc' ? 'desc' : 'asc'; ?>" class="text-decoration-none text-dark">
                                    Title
                                    <?php if ($sort === 'title'): ?>
                                        <i class="fas fa-sort-<?php echo $order === 'asc' ? 'up' : 'down'; ?> ms-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="?sort=category&order=<?php echo $sort === 'category' && $order === 'asc' ? 'desc' : 'asc'; ?>" class="text-decoration-none text-dark">
                                    Category
                                    <?php if ($sort === 'category'): ?>
                                        <i class="fas fa-sort-<?php echo $order === 'asc' ? 'up' : 'down'; ?> ms-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="?sort=date&order=<?php echo $sort === 'date' && $order === 'asc' ? 'desc' : 'asc'; ?>" class="text-decoration-none text-dark">
                                    Date & Time
                                    <?php if ($sort === 'date'): ?>
                                        <i class="fas fa-sort-<?php echo $order === 'asc' ? 'up' : 'down'; ?> ms-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="?sort=venue&order=<?php echo $sort === 'venue' && $order === 'asc' ? 'desc' : 'asc'; ?>" class="text-decoration-none text-dark">
                                    Venue
                                    <?php if ($sort === 'venue'): ?>
                                        <i class="fas fa-sort-<?php echo $order === 'asc' ? 'up' : 'down'; ?> ms-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col">Participants</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($events) > 0): ?>
                            <?php foreach ($events as $index => $event): ?>
                                <?php 
                                    $isPast = isEventPast($event['date'], $event['time']);
                                    $registrationCount = count($registrationModel->getByEventId($event['id']));
                                ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <a href="../event_details.php?id=<?php echo $event['id']; ?>" class="fw-bold text-decoration-none">
                                            <?php echo htmlspecialchars($event['title']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($event['category']); ?></td>
                                    <td>
                                        <div><?php echo formatDate($event['date']); ?></div>
                                        <small class="text-muted"><?php echo formatTime($event['time']); ?></small>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($event['venue']); ?></div>
                                        <small class="text-muted">Room: <?php echo htmlspecialchars($event['room_no']); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <span class="badge bg-primary rounded-pill">
                                                    <?php echo $registrationCount; ?> / <?php echo $event['max_participants']; ?>
                                                </span>
                                            </div>
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <?php $percentage = ($event['max_participants'] > 0) ? ($registrationCount / $event['max_participants']) * 100 : 0; ?>
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $percentage; ?>%" 
                                                    aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($isPast): ?>
                                            <span class="badge bg-secondary">Past</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Upcoming</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="event_create.php?edit=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="registrations.php?event_id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-users"></i>
                                            </a>
                                            <a href="attendance.php?event_id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-clipboard-check"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete(<?php echo $event['id']; ?>, '<?php echo addslashes($event['title']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="text-muted mb-0">No events found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the event: <span id="eventTitle" class="fw-bold"></span>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete Event</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Event search functionality
    document.getElementById('eventSearch').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('tbody tr');
        
        tableRows.forEach(row => {
            const title = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const category = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const venue = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
            
            if (title.includes(searchValue) || category.includes(searchValue) || venue.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Delete confirmation
    function confirmDelete(eventId, eventTitle) {
        document.getElementById('eventTitle').textContent = eventTitle;
        document.getElementById('confirmDeleteBtn').href = 'events.php?delete=' + eventId;
        
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
</script>

<?php include_once '../app/views/layouts/footer.php'; ?>