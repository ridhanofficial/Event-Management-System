<?php
/**
 * Admin Attendance Management
 * 
 * This page allows administrators to track and manage event attendance.
 */

// Include initialization file
require_once '../includes/init.php';

// Require admin privileges
requireAdmin();

// Set page title
$pageTitle = 'Manage Attendance';

// Handle attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $registrationId = $_POST['registration_id'];
    $attended = isset($_POST['attended']) ? 1 : 0;
    
    if ($registrationModel->markAttendance($registrationId, $attended)) {
        setFlashMessage('Attendance marked successfully.', 'success');
    } else {
        setFlashMessage('Failed to mark attendance.', 'danger');
    }
    
    // Redirect back to the same page with filters preserved
    $redirectUrl = 'attendance.php';
    if (isset($_GET['event_id'])) {
        $redirectUrl .= '?event_id=' . $_GET['event_id'];
    }
    
    redirect($redirectUrl);
}

// Get events for the dropdown
$events = $eventModel->getAll('date', 'desc');

// Filter registrations by event if specified
$registrations = [];
$selectedEvent = null;

// Get sorting parameters
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'asc';

// Validate sort parameters
$validSorts = ['name', 'email', 'date', 'attendance'];
if (!in_array($sortBy, $validSorts)) {
    $sortBy = 'name';
}

// Validate sort order
$validOrders = ['asc', 'desc'];
if (!in_array(strtolower($sortOrder), $validOrders)) {
    $sortOrder = 'asc';
}

if (isset($_GET['event_id']) && is_numeric($_GET['event_id'])) {
    $eventId = $_GET['event_id'];
    $selectedEvent = $eventModel->getById($eventId);
    
    if ($selectedEvent) {
        // Get approved registrations for this event with sorting
        $registrations = $registrationModel->getApprovedByEventId($eventId, $sortBy, $sortOrder);
    } else {
        setFlashMessage('Event not found.', 'danger');
        redirect('attendance.php');
    }
}

// Include header
include_once '../app/views/layouts/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Attendance Management</h1>
        <div>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
    
    <!-- Event Selection -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Select Event</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="attendance.php" class="row g-3">
                <div class="col-md-8">
                    <select name="event_id" class="form-select" required>
                        <option value="" disabled <?php echo !isset($_GET['event_id']) ? 'selected' : ''; ?>>Select an event to manage attendance</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?php echo $event['id']; ?>" <?php echo (isset($_GET['event_id']) && $_GET['event_id'] == $event['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($event['title']); ?> - <?php echo formatDate($event['date']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($selectedEvent): ?>
        <!-- Event Details -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><?php echo htmlspecialchars($selectedEvent['title']); ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Date:</strong> <?php echo formatDate($selectedEvent['date']); ?></p>
                        <p><strong>Time:</strong> <?php echo formatTime($selectedEvent['time']); ?></p>
                        <p><strong>Venue:</strong> <?php echo htmlspecialchars($selectedEvent['venue']); ?> (Room: <?php echo htmlspecialchars($selectedEvent['room_no']); ?>)</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($selectedEvent['category']); ?></p>
                        <p><strong>Registration Type:</strong> <?php echo $selectedEvent['team_based'] ? 'Team-based' : 'Individual'; ?></p>
                        <p><strong>Maximum Participants:</strong> <?php echo $selectedEvent['max_participants']; ?></p>
                    </div>                </div>
            </div>
        </div>

        <?php
        // Calculate attendance statistics
        $totalRegistered = count($registrations);
        $presentCount = 0;
        $absentCount = 0;
        
        foreach ($registrations as $reg) {
            if ($reg['check_in']) {
                $presentCount++;
            } else {
                $absentCount++;
            }
        }
        
        $attendanceRate = $totalRegistered > 0 ? round(($presentCount / $totalRegistered) * 100) : 0;
        ?>        <!-- Attendance Statistics -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card shadow-sm bg-light">
                            <div class="card-body text-center">
                                <div class="h1 mb-0">
                                    <span class="text-primary"><?= $totalRegistered ?></span>
                                </div>
                                <div class="small text-muted text-uppercase">Total Registered</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card shadow-sm bg-success text-white">
                            <div class="card-body text-center">
                                <div class="h1 mb-0">
                                    <?= $presentCount ?>
                                </div>
                                <div class="small text-uppercase">Present</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card shadow-sm bg-danger text-white">
                            <div class="card-body text-center">
                                <div class="h1 mb-0">
                                    <?= $absentCount ?>
                                </div>
                                <div class="small text-uppercase">Absent</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card shadow-sm <?= $attendanceRate >= 70 ? 'bg-success' : ($attendanceRate >= 40 ? 'bg-warning' : 'bg-danger') ?> text-white">
                            <div class="card-body text-center">
                                <div class="h1 mb-0">
                                    <?= $attendanceRate ?>%
                                </div>
                                <div class="small text-uppercase">Attendance Rate</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Metrics -->
                    <div class="col-12 mt-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Attendance Timeline</h6>
                                <div class="timeline-metrics">
                                    <?php
                                        // Get the event date
                                        $eventDate = new DateTime($selectedEvent['date']);
                                        $today = new DateTime();
                                        
                                        // Calculate days between now and event date
                                        $interval = $today->diff($eventDate);
                                        $daysRemaining = $interval->format('%R%a');
                                        
                                        if ($daysRemaining > 0) {
                                            // Event is in the future
                                            echo '<div class="alert alert-info">
                                                <i class="fas fa-calendar-day me-2"></i>
                                                Event is scheduled to take place in ' . abs($daysRemaining) . ' days.
                                            </div>';
                                        } elseif ($daysRemaining == 0) {
                                            // Event is today
                                            echo '<div class="alert alert-success">
                                                <i class="fas fa-calendar-check me-2"></i>
                                                Event is happening today!
                                            </div>';
                                        } else {
                                            // Event has passed
                                            echo '<div class="alert alert-secondary">
                                                <i class="fas fa-calendar me-2"></i>
                                                Event took place ' . abs($daysRemaining) . ' days ago.
                                            </div>';
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Attendance Chart</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="attendanceChart" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Attendance Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">Attendance List</h5>
                    </div>                    <div class="col-md-auto">                        <!-- Download Options -->
                        <div class="btn-group d-inline-block me-2">
                            <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-file-export me-1"></i> Export
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="export_attendance.php?event_id=<?= $selectedEvent['id'] ?>&format=csv">
                                        <i class="fas fa-file-csv me-2"></i> CSV Format
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="export_attendance.php?event_id=<?= $selectedEvent['id'] ?>&format=pdf">
                                        <i class="fas fa-file-pdf me-2"></i> PDF Format
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="printAttendanceReport(); return false;">
                            <i class="fas fa-print me-1"></i> Print Report
                        </button>
                        <a href="attendance_report.php?event_id=<?= $selectedEvent['id'] ?>" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-chart-bar me-1"></i> Detailed Analytics
                        </a>
                    </div>
                    <div class="col-auto">
                        <div class="input-group">
                            <input type="text" id="attendanceSearch" class="form-control" placeholder="Search participants...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (count($registrations) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">                        <thead class="table-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">
                                        <a href="?event_id=<?= $selectedEvent['id'] ?>&sort=name&order=<?= (isset($_GET['sort']) && $_GET['sort'] == 'name' && isset($_GET['order']) && $_GET['order'] == 'asc') ? 'desc' : 'asc' ?>" class="text-dark text-decoration-none">
                                            Participant
                                            <?php if (isset($_GET['sort']) && $_GET['sort'] == 'name'): ?>
                                                <i class="fas fa-sort-<?= $_GET['order'] == 'asc' ? 'up' : 'down' ?> ms-1 text-muted"></i>
                                            <?php else: ?>
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th scope="col">Registration Type</th>
                                    <th scope="col">
                                        <a href="?event_id=<?= $selectedEvent['id'] ?>&sort=date&order=<?= (isset($_GET['sort']) && $_GET['sort'] == 'date' && isset($_GET['order']) && $_GET['order'] == 'asc') ? 'desc' : 'asc' ?>" class="text-dark text-decoration-none">
                                            Registration Date
                                            <?php if (isset($_GET['sort']) && $_GET['sort'] == 'date'): ?>
                                                <i class="fas fa-sort-<?= $_GET['order'] == 'asc' ? 'up' : 'down' ?> ms-1 text-muted"></i>
                                            <?php else: ?>
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th scope="col">
                                        <a href="?event_id=<?= $selectedEvent['id'] ?>&sort=attendance&order=<?= (isset($_GET['sort']) && $_GET['sort'] == 'attendance' && isset($_GET['order']) && $_GET['order'] == 'asc') ? 'desc' : 'asc' ?>" class="text-dark text-decoration-none">
                                            Attendance
                                            <?php if (isset($_GET['sort']) && $_GET['sort'] == 'attendance'): ?>
                                                <i class="fas fa-sort-<?= $_GET['order'] == 'asc' ? 'up' : 'down' ?> ms-1 text-muted"></i>
                                            <?php else: ?>
                                                <i class="fas fa-sort ms-1 text-muted"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registrations as $index => $registration): ?>
                                    <?php 
                                        // Get user details
                                        $user = $userModel->findById($registration['user_id']);
                                        
                                        // Skip if user not found (shouldn't happen, but just in case)
                                        if (!$user) continue;
                                        
                                        // Determine registration type
                                        $registrationType = $selectedEvent['team_based'] ? 
                                            ($registration['team_name'] ? 'Team: ' . htmlspecialchars($registration['team_name']) : 'Individual') : 
                                            'Individual';
                                    ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
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
                                            <?php if ($registration['attended'] == 1): ?>
                                                <span class="badge bg-success">Present</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Absent</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="attendance.php?event_id=<?php echo $selectedEvent['id']; ?>" class="d-inline-block">
                                                <input type="hidden" name="registration_id" value="<?php echo $registration['id']; ?>">
                                                <div class="form-check form-switch d-inline-block me-2">
                                                    <input class="form-check-input" type="checkbox" id="attended<?php echo $registration['id']; ?>" 
                                                           name="attended" <?php echo $registration['attended'] == 1 ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="attended<?php echo $registration['id']; ?>"></label>
                                                </div>
                                                <button type="submit" name="mark_attendance" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-save"></i> Save
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-4 text-center">
                        <p class="text-muted mb-0">No approved registrations found for this event.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (count($registrations) > 0): ?>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Total Registrations:</strong> <?php echo count($registrations); ?>
                        </div>
                        <div>
                            <?php 
                                $presentCount = 0;
                                foreach ($registrations as $reg) {
                                    if ($reg['attended'] == 1) $presentCount++;
                                }
                                $attendanceRate = count($registrations) > 0 ? round(($presentCount / count($registrations)) * 100) : 0;
                            ?>
                            <strong>Attendance Rate:</strong> 
                            <span class="badge bg-<?php echo $attendanceRate >= 70 ? 'success' : ($attendanceRate >= 40 ? 'warning' : 'danger'); ?>">
                                <?php echo $presentCount; ?> / <?php echo count($registrations); ?> (<?php echo $attendanceRate; ?>%)
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>Please select an event from the dropdown above to manage attendance.
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Initialize attendance chart
    <?php if (isset($selectedEvent)): ?>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('attendanceChart');
        
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent'],
                    datasets: [{
                        data: [<?= $presentCount ?>, <?= $absentCount ?>],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(220, 53, 69, 0.8)'
                        ],
                        borderColor: [
                            'rgba(40, 167, 69, 1)',
                            'rgba(220, 53, 69, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = <?= $totalRegistered ?>;
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    });
    <?php endif; ?>

    // Attendance search functionality
    document.getElementById('attendanceSearch')?.addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('tbody tr');
        
        tableRows.forEach(row => {
            const participant = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const type = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            
            if (participant.includes(searchValue) || type.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
      // Print attendance report
    function printAttendanceReport() {
        // Create a new window for printing
        const printWindow = window.open('', '_blank');
        
        <?php if (isset($selectedEvent) && $selectedEvent): ?>
        // Get event details and attendance data
        const eventTitle = <?php echo json_encode($selectedEvent['title']); ?>;
        const eventDate = <?php echo json_encode(formatDate($selectedEvent['date'])); ?>;
        const eventTime = <?php echo json_encode(formatTime($selectedEvent['time'])); ?>;
        const eventVenue = <?php echo json_encode($selectedEvent['venue'] . ' (Room: ' . $selectedEvent['room_no'] . ')'); ?>;
        const totalRegistrations = <?php echo count($registrations); ?>;
        const presentCount = <?php echo $presentCount ?? 0; ?>;
        const absentCount = <?php echo $absentCount ?? 0; ?>;
        const attendanceRate = <?php echo $attendanceRate ?? 0; ?>;
        
        // Get attendance rating
        let ratingText, ratingClass;
        if (attendanceRate >= 80) {
            ratingText = "Excellent";
            ratingClass = "text-success";
        } else if (attendanceRate >= 60) {
            ratingText = "Good";
            ratingClass = "text-primary";
        } else if (attendanceRate >= 40) {
            ratingText = "Average";
            ratingClass = "text-warning";
        } else {
            ratingText = "Poor";
            ratingClass = "text-danger";
        }
        
        // Create HTML content for printing
        let printContent = `
            <html>
            <head>
                <title>Attendance Report - ${eventTitle}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
                    h1, h2, h3 { color: #333; margin-top: 20px; }
                    .report-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                    .logo { max-height: 60px; margin-bottom: 10px; }
                    .event-details { margin-bottom: 20px; background-color: #f9f9f9; padding: 15px; border-radius: 5px; }
                    .event-details p { margin: 5px 0; }
                    .stats-container { display: flex; margin: 20px 0; }
                    .stats-box { flex: 1; margin: 0 10px; padding: 15px; border-radius: 5px; text-align: center; }
                    .stats-box h2 { font-size: 28px; margin: 5px 0; }
                    .stats-box p { margin: 5px 0; font-weight: bold; }
                    .present-box { background-color: rgba(40, 167, 69, 0.2); }
                    .absent-box { background-color: rgba(220, 53, 69, 0.2); }
                    .rate-box { background-color: rgba(255, 193, 7, 0.2); }
                    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    table, th, td { border: 1px solid #ddd; }
                    th, td { padding: 10px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    tr:nth-child(even) { background-color: #f9f9f9; }
                    .present { color: green; font-weight: bold; }
                    .absent { color: red; font-weight: bold; }
                    .text-success { color: green; }
                    .text-primary { color: blue; }
                    .text-warning { color: orange; }
                    .text-danger { color: red; }
                    .summary { margin-top: 20px; background-color: #f0f0f0; padding: 15px; border-radius: 5px; }
                    .summary-title { border-bottom: 1px solid #ddd; padding-bottom: 5px; }
                    .summary-item { margin: 10px 0; }
                    .footer { margin-top: 50px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 12px; text-align: center; color: #666; }
                    @media print {
                        .no-print { display: none; }
                        body { margin: 0.5cm; }
                        .stats-container { break-inside: avoid; }
                        h1, h2, h3 { break-after: avoid; }
                    }
                </style>
            </head>
            <body>
                <div class="report-header">
                    <h1>Attendance Report</h1>
                    <p>PIETECH Events Platform</p>
                </div>
                
                <div class="event-details">
                    <h2>${eventTitle}</h2>
                    <p><strong>Date:</strong> ${eventDate}</p>
                    <p><strong>Time:</strong> ${eventTime}</p>
                    <p><strong>Venue:</strong> ${eventVenue}</p>
                </div>
                
                <div class="stats-container">
                    <div class="stats-box present-box">
                        <p>Present</p>
                        <h2>${presentCount}</h2>
                    </div>
                    
                    <div class="stats-box absent-box">
                        <p>Absent</p>
                        <h2>${absentCount}</h2>
                    </div>
                    
                    <div class="stats-box rate-box">
                        <p>Attendance Rate</p>
                        <h2>${attendanceRate}%</h2>
                        <p class="${ratingClass}">${ratingText}</p>
                    </div>
                </div>
                
                <h3>Attendance List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Participant</th>
                            <th>Email</th>
                            <th>Registration Type</th>
                            <th>Registration Date</th>
                            <th>Attendance</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        // Add table rows from registrations
        <?php 
        if (isset($registrations) && count($registrations) > 0): 
            foreach ($registrations as $index => $registration): 
        ?>
            printContent += `
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($registration['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($registration['user_email']); ?></td>
                    <td><?php echo $registration['team_based'] ? 'Team: ' . htmlspecialchars($registration['team_name'] ?? 'N/A') : 'Individual'; ?></td>
                    <td><?php echo formatDate($registration['created_at']); ?></td>
                    <td class="<?php echo $registration['check_in'] ? 'present' : 'absent'; ?>">
                        <?php echo $registration['check_in'] ? 'Present' : 'Absent'; ?>
                    </td>
                </tr>
            `;
        <?php 
            endforeach;
        endif; 
        ?>
        
        // Add summary section and close HTML
        printContent += `
                    </tbody>
                </table>
                
                <div class="summary">
                    <h3 class="summary-title">Attendance Summary</h3>
                    <div class="summary-item">
                        <strong>Total Registered:</strong> ${totalRegistrations}
                    </div>
                    <div class="summary-item">
                        <strong>Present:</strong> ${presentCount}
                    </div>
                    <div class="summary-item">
                        <strong>Absent:</strong> ${absentCount}
                    </div>
                    <div class="summary-item">
                        <strong>Attendance Rate:</strong> ${attendanceRate}%
                    </div>
                    <div class="summary-item">
                        <strong>Rating:</strong> <span class="${ratingClass}">${ratingText}</span>
                    </div>
                </div>
                
                <div class="footer">
                    <p>Generated on ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()} by PIETECH Events Platform</p>
                    <p>This is an official attendance report. For inquiries, please contact the admin.</p>
                </div>
                
                <div class="no-print" style="text-align: center; margin-top: 20px;">
                    <button onclick="window.print();" style="padding: 8px 16px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">Print Report</button>
                    <button onclick="window.close();" style="padding: 8px 16px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                </div>
            </body>
            </html>
        `;
        
        // Write to the new window and trigger print
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.focus();
        <?php else: ?>
        printWindow.document.write(`
            <html>
            <head>
                <title>No Event Selected</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; text-align: center; }
                    .message { margin: 50px auto; padding: 20px; max-width: 500px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24; }
                    button { padding: 8px 16px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class="message">
                    <h3>No Event Selected</h3>
                    <p>Please select an event first to generate an attendance report.</p>
                </div>
                <button onclick="window.close();">Close</button>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        <?php endif; ?>
    }
</script>

<?php include_once '../app/views/layouts/footer.php'; ?>