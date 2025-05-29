<?php
/**
 * Admin Event Creation/Edit
 * 
 * This page allows administrators to create new events or edit existing ones.
 */

// Include initialization file
require_once '../includes/init.php';

// Require admin privileges
requireAdmin();

// Set default page title
$pageTitle = 'Create Event';

// Initialize variables
$event = [
    'id' => '',
    'title' => '',
    'description' => '',
    'category' => '',
    'date' => '',
    'time' => '',
    'venue' => '',
    'room_no' => '',
    'max_participants' => '',
    'team_based' => 0
];

// Check if editing an existing event
$isEditing = false;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $eventId = $_GET['edit'];
    $eventData = $eventModel->getById($eventId);
    
    if ($eventData) {
        $event = $eventData;
        $isEditing = true;
        $pageTitle = 'Edit Event';
    } else {
        setFlashMessage('Event not found.', 'danger');
        redirect('events.php');
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $date = trim($_POST['date']);
    $time = trim($_POST['time']);
    $venue = trim($_POST['venue']);
    $room_no = trim($_POST['room_no']);
    $max_participants = (int)$_POST['max_participants'];
    $team_based = isset($_POST['team_based']) ? 1 : 0;
    
    $errors = [];
    
    // Basic validation
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    
    if (empty($category)) {
        $errors[] = "Category is required";
    }
    
    if (empty($date)) {
        $errors[] = "Date is required";
    } elseif (strtotime($date) < strtotime(date('Y-m-d'))) {
        $errors[] = "Date cannot be in the past";
    }
    
    if (empty($time)) {
        $errors[] = "Time is required";
    }
    
    if (empty($venue)) {
        $errors[] = "Venue is required";
    }
    
    if (empty($room_no)) {
        $errors[] = "Room number is required";
    }
    
    if ($max_participants <= 0) {
        $errors[] = "Maximum participants must be greater than zero";
    }
    
    // If no errors, save the event
    if (empty($errors)) {
        $eventData = [
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'date' => $date,
            'time' => $time,
            'venue' => $venue,
            'room_no' => $room_no,
            'max_participants' => $max_participants,
            'team_based' => $team_based,
            'created_by' => $_SESSION['user_id']
        ];
        
        if ($isEditing) {
            // Update existing event
            if ($eventModel->update($event['id'], $eventData)) {
                setFlashMessage("Event updated successfully", "success");
                redirect('events.php');
            } else {
                $errors[] = "Failed to update event. Please try again.";
            }
        } else {
            // Create new event
            if ($eventModel->create($eventData)) {
                setFlashMessage("Event created successfully", "success");
                redirect('events.php');
            } else {
                $errors[] = "Failed to create event. Please try again.";
            }
        }
    }
    
    // If there were errors, repopulate the form with submitted values
    if (!empty($errors)) {
        $event = [
            'id' => $isEditing ? $event['id'] : '',
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'date' => $date,
            'time' => $time,
            'venue' => $venue,
            'room_no' => $room_no,
            'max_participants' => $max_participants,
            'team_based' => $team_based
        ];
    }
}

// Include header
include_once '../app/views/layouts/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="h4 mb-0">
                        <i class="fas <?php echo $isEditing ? 'fa-edit' : 'fa-plus-circle'; ?> me-2"></i>
                        <?php echo $pageTitle; ?>
                    </h2>
                </div>
                <div class="card-body">
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo $isEditing ? 'event_create.php?edit=' . $event['id'] : 'event_create.php'; ?>">
                        <div class="mb-3">
                            <label for="title" class="form-label">Event Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="" disabled <?php echo empty($event['category']) ? 'selected' : ''; ?>>Select a category</option>
                                    <option value="Workshop" <?php echo $event['category'] === 'technical' ? 'selected' : ''; ?>>Technical</option>
                                    <option value="Seminar" <?php echo $event['category'] === '' ? 'cultural' : ''; ?>>Cultural</option>
                                    <option value="Conference" <?php echo $event['category'] === 'sports' ? 'selected' : ''; ?>>Sports</option>
                                    <option value="Other" <?php echo $event['category'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="max_participants" class="form-label">Maximum Participants</label>
                                <input type="number" class="form-control" id="max_participants" name="max_participants" min="1" value="<?php echo htmlspecialchars($event['max_participants']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($event['date']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="time" class="form-label">Time</label>
                                <input type="time" class="form-control" id="time" name="time" value="<?php echo htmlspecialchars($event['time']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="venue" class="form-label">Venue</label>
                                <input type="text" class="form-control" id="venue" name="venue" value="<?php echo htmlspecialchars($event['venue']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="room_no" class="form-label">Room Number</label>
                                <input type="text" class="form-control" id="room_no" name="room_no" value="<?php echo htmlspecialchars($event['room_no']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="team_based" name="team_based" value="1" <?php echo $event['team_based'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="team_based">
                                    This is a team-based event
                                </label>
                                <div class="form-text">If checked, participants can register as teams rather than individuals.</div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="events.php" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <?php echo $isEditing ? 'Update Event' : 'Create Event'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../app/views/layouts/footer.php'; ?>