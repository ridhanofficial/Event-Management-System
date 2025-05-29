<?php
// Include initialization file
require_once '../includes/init.php';

// Require admin privileges
requireAdmin();

// Set page title
$pageTitle = 'Select Winners';

// Database and models are already available from init.php
// $db, $dbConnection, $eventModel, etc.

// Fetch all events for dropdown
$stmt = $dbConnection->prepare("SELECT id, title FROM events ORDER BY title");
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize variables
$eventId = null;
$participants = [];
$eventName = '';

// On form submit (POST), fetch selected eventId
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eventId'])) {
    $eventId = filter_input(INPUT_POST, 'eventId', FILTER_SANITIZE_NUMBER_INT);
      // Fetch event title for display
    $eventStmt = $dbConnection->prepare("SELECT title FROM events WHERE id = ?");
    $eventStmt->execute([$eventId]);
    $eventName = $eventStmt->fetchColumn();
    
    // Fetch all participants registered for that event
    $participantsStmt = $dbConnection->prepare("
        SELECT u.id, u.name, u.email, r.winner_position
        FROM users u
        JOIN registrations r ON u.id = r.user_id
        WHERE r.event_id = ?
        ORDER BY u.name
    ");
    $participantsStmt->execute([$eventId]);
    $participants = $participantsStmt->fetchAll(PDO::FETCH_ASSOC);
}
// Add custom styles
$customCSS = '
<style>
    /* Ribbon effect for winner cards */
    .ribbon {
        width: 150px;
        height: 150px;
        overflow: hidden;
        position: absolute;
        z-index: 1;
    }
    .ribbon-top-right {
        top: -10px;
        right: -10px;
    }
    .ribbon-top-right::before,
    .ribbon-top-right::after {
        border-top-color: transparent;
        border-right-color: transparent;
    }
    .ribbon-top-right::before {
        top: 0;
        left: 0;
    }
    .ribbon-top-right::after {
        bottom: 0;
        right: 0;
    }
    .ribbon-top-right span {
        position: absolute;
        top: 30px;
        right: -25px;
        transform: rotate(45deg);
        width: 100px;
        padding: 5px 0;
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        color: white;
    }
    /* Card hover effect */
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>
';

// Include header with custom CSS
include_once '../app/views/layouts/header.php';
?>
      <div class="container py-4">
        <h1 class="mb-4">Select Event Winners</h1>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == 'true'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Winner positions have been saved and 
                <?php if (isset($_GET['notifications']) && $_GET['notifications'] > 0): ?>
                    <?= $_GET['notifications'] ?> notification emails have been sent.
                <?php else: ?>
                    notifications have been processed.
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> There was a problem processing winners: <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Event Selection Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Select an Event</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="eventId" class="form-label">Event</label>
                        <select name="eventId" id="eventId" class="form-select" required>
                            <option value="">-- Select an Event --</option>                            <?php foreach ($events as $event): ?>
                                <option value="<?= htmlspecialchars($event['id']) ?>" 
                                    <?= ($eventId == $event['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($event['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Show Participants</button>
                </form>
            </div>
        </div>
          <?php if (!empty($participants)): ?>
            <div class="card mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Participants for: <?= htmlspecialchars($eventName) ?></h5>
                    <span class="badge bg-light text-dark"><?= count($participants) ?> Participants</span>
                </div>
                <div class="card-body">
                    <!-- Winners quick stats -->
                    <?php 
                    $winners = array_filter($participants, function($p) { 
                        return !empty($p['winner_position']); 
                    });
                    ?>
                    
                    <div class="alert alert-info mb-4">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Current Winners:</strong>
                                <span class="badge bg-primary ms-2"><?= count($winners) ?> of 3 positions filled</span>
                            </div>
                            <div>
                                <?php
                                $positions = ['1st' => false, '2nd' => false, '3rd' => false];
                                foreach ($participants as $p) {
                                    if (!empty($p['winner_position']) && isset($positions[$p['winner_position']])) {
                                        $positions[$p['winner_position']] = true;
                                    }
                                }
                                
                                foreach ($positions as $pos => $filled): ?>
                                    <span class="badge <?= $filled ? 'bg-success' : 'bg-secondary' ?> me-1">
                                        <?= $pos ?> Place <?= $filled ? '✓' : '○' ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
            
                    <form action="process_winners.php" method="POST">
                        <input type="hidden" name="eventId" value="<?= htmlspecialchars($eventId) ?>">
                        
                        <div class="row row-cols-1 row-cols-md-3 g-4">
                            <?php foreach ($participants as $participant): ?>
                                <div class="col">
                                    <div class="card h-100 <?= !empty($participant['winner_position']) ? 'border-success' : '' ?>">
                                        <?php if (!empty($participant['winner_position'])): ?>
                                            <div class="ribbon ribbon-top-right">
                                                <span class="bg-success"><?= htmlspecialchars($participant['winner_position']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="text-center pt-3">
                                            <?php $profilePic = file_exists("../uploads/profile_pics/{$participant['id']}.jpg") 
                                                ? "../uploads/profile_pics/{$participant['id']}.jpg" 
                                                : "../images/default-avatar.png"; ?>
                                            <img src="<?= $profilePic ?>" class="rounded-circle" alt="Profile Picture" style="width: 100px; height: 100px; object-fit: cover;">
                                        </div>
                                        
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($participant['name']) ?></h5>
                                            <p class="card-text"><?= htmlspecialchars($participant['email']) ?></p>
                                            
                                            <div class="form-group mt-3">
                                                <label>Winner Position:</label>
                                                <select name="position[<?= $participant['id'] ?>]" class="form-select">
                                                    <option value="">Not a winner</option>
                                                    <option value="1st" <?= ($participant['winner_position'] === '1st') ? 'selected' : '' ?>>1st Place</option>
                                                    <option value="2nd" <?= ($participant['winner_position'] === '2nd') ? 'selected' : '' ?>>2nd Place</option>
                                                    <option value="3rd" <?= ($participant['winner_position'] === '3rd') ? 'selected' : '' ?>>3rd Place</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-trophy me-2"></i>Save Winners & Send Notifications
                            </button>                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php
// Include footer
include_once '../app/views/layouts/footer.php';
?>
