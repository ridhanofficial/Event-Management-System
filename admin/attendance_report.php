<?php
/**
 * Detailed Attendance Report
 * 
 * This page displays a detailed attendance report with visualizations and statistics
 */

// Include initialization file
require_once '../includes/init.php';

// Require admin privileges
requireAdmin();

// Set page title
$pageTitle = 'Attendance Detailed Report';

// Check if an event ID is provided
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    setFlashMessage('Please select an event to view its detailed report.', 'info');
    redirect('attendance.php');
}

$eventId = $_GET['event_id'];

// Get event details
$event = $eventModel->getById($eventId);
if (!$event) {
    setFlashMessage('Event not found.', 'danger');
    redirect('attendance.php');
}

// Get approved registrations for this event
$registrations = $registrationModel->getApprovedByEventId($eventId);

// Calculate attendance statistics
$totalRegistered = count($registrations);
$presentCount = 0;
$absentCount = 0;
$departmentStats = [];
$dailyRegistrations = [];

foreach ($registrations as $reg) {
    // Count attendance
    if ($reg['check_in']) {
        $presentCount++;
    } else {
        $absentCount++;
    }
    
    // Track department statistics
    $department = $reg['user_department'] ?? 'Unknown';
    if (!isset($departmentStats[$department])) {
        $departmentStats[$department] = [
            'total' => 0,
            'present' => 0,
            'absent' => 0
        ];
    }
    
    $departmentStats[$department]['total']++;
    if ($reg['check_in']) {
        $departmentStats[$department]['present']++;
    } else {
        $departmentStats[$department]['absent']++;
    }
    
    // Track registration dates for timeline
    $regDate = date('Y-m-d', strtotime($reg['created_at']));
    if (!isset($dailyRegistrations[$regDate])) {
        $dailyRegistrations[$regDate] = 0;
    }
    $dailyRegistrations[$regDate]++;
}

$attendanceRate = $totalRegistered > 0 ? round(($presentCount / $totalRegistered) * 100) : 0;

// Sort department statistics by total registrations
uasort($departmentStats, function($a, $b) {
    return $b['total'] - $a['total'];
});

// Sort daily registrations by date
ksort($dailyRegistrations);

// Include header
include_once '../app/views/layouts/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Attendance Detailed Report</h1>
        <div>
            <a href="attendance.php?event_id=<?= $event['id'] ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Attendance
            </a>
        </div>
    </div>
    
    <!-- Event Details -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><?php echo htmlspecialchars($event['title']); ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Date:</strong> <?php echo formatDate($event['date']); ?></p>
                    <p><strong>Time:</strong> <?php echo formatTime($event['time']); ?></p>
                    <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?> (Room: <?php echo htmlspecialchars($event['room_no']); ?>)</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($event['category']); ?></p>
                    <p><strong>Registration Type:</strong> <?php echo $event['team_based'] ? 'Team-based' : 'Individual'; ?></p>
                    <p><strong>Maximum Participants:</strong> <?php echo $event['max_participants']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Summary Statistics -->
    <div class="row mb-4">
        <!-- Attendance Rate Card -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Attendance Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <canvas id="attendanceDonutChart" height="200"></canvas>
                        </div>
                        <div class="col-md-7">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th>Total Registered:</th>
                                            <td><?= $totalRegistered ?></td>
                                        </tr>
                                        <tr class="table-success">
                                            <th>Present:</th>
                                            <td><?= $presentCount ?> (<?= $attendanceRate ?>%)</td>
                                        </tr>
                                        <tr class="table-danger">
                                            <th>Absent:</th>
                                            <td><?= $absentCount ?> (<?= $totalRegistered > 0 ? (100 - $attendanceRate) : 0 ?>%)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Attendance Rating -->
                            <div class="mt-3">
                                <p class="mb-1"><strong>Attendance Rating:</strong></p>
                                <?php
                                if ($attendanceRate >= 80) {
                                    $ratingText = "Excellent";
                                    $ratingClass = "text-success";
                                    $ratingIcon = "trophy";
                                } elseif ($attendanceRate >= 60) {
                                    $ratingText = "Good";
                                    $ratingClass = "text-primary";
                                    $ratingIcon = "thumbs-up";
                                } elseif ($attendanceRate >= 40) {
                                    $ratingText = "Average";
                                    $ratingClass = "text-warning";
                                    $ratingIcon = "exclamation-circle";
                                } else {
                                    $ratingText = "Poor";
                                    $ratingClass = "text-danger";
                                    $ratingIcon = "thumbs-down";
                                }
                                ?>
                                <div class="fs-4 <?= $ratingClass ?>">
                                    <i class="fas fa-<?= $ratingIcon ?> me-2"></i><?= $ratingText ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Registration Timeline Card -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Registration Timeline</h5>
                </div>
                <div class="card-body">
                    <canvas id="registrationTimelineChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Department Statistics -->
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Attendance by Department</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="departmentChart" height="300"></canvas>
                        </div>
                        <div class="col-md-4">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Department</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Present</th>
                                            <th class="text-center">Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($departmentStats as $dept => $stats): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($dept) ?></td>
                                                <td class="text-center"><?= $stats['total'] ?></td>
                                                <td class="text-center"><?= $stats['present'] ?></td>
                                                <td class="text-center">
                                                    <?php 
                                                        $deptRate = $stats['total'] > 0 ? round(($stats['present'] / $stats['total']) * 100) : 0;
                                                        $rateClass = $deptRate >= 70 ? 'bg-success' : ($deptRate >= 40 ? 'bg-warning' : 'bg-danger');
                                                    ?>
                                                    <span class="badge <?= $rateClass ?>"><?= $deptRate ?>%</span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Export Options -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Export Options</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <a href="export_attendance.php?event_id=<?= $event['id'] ?>&format=csv" class="btn btn-success">
                                    <i class="fas fa-file-csv me-2"></i>Export as CSV
                                </a>
                                <a href="export_attendance.php?event_id=<?= $event['id'] ?>&format=pdf" class="btn btn-danger">
                                    <i class="fas fa-file-pdf me-2"></i>Export as PDF
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" onclick="printAttendanceReport(); return false;">
                                    <i class="fas fa-print me-2"></i>Print Attendance Report
                                </button>
                                <a href="attendance.php?event_id=<?= $event['id'] ?>" class="btn btn-secondary">
                                    <i class="fas fa-table me-2"></i>Return to Attendance Management
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Attendance Donut Chart
        const donutCtx = document.getElementById('attendanceDonutChart');
        if (donutCtx) {
            new Chart(donutCtx, {
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
        
        // Registration Timeline Chart
        const timelineCtx = document.getElementById('registrationTimelineChart');
        if (timelineCtx) {
            const timelineData = {
                labels: [<?php 
                    $labels = [];
                    foreach ($dailyRegistrations as $date => $count) {
                        $labels[] = "'" . date('M d', strtotime($date)) . "'";
                    }
                    echo implode(', ', $labels);
                ?>],
                datasets: [{
                    label: 'New Registrations',
                    data: [<?php 
                        $values = [];
                        foreach ($dailyRegistrations as $count) {
                            $values[] = $count;
                        }
                        echo implode(', ', $values);
                    ?>],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    fill: true
                }]
            };
            
            new Chart(timelineCtx, {
                type: 'line',
                data: timelineData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                title: function(tooltipItems) {
                                    return tooltipItems[0].label;
                                },
                                label: function(context) {
                                    return `Registrations: ${context.raw}`;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Department Chart
        const deptCtx = document.getElementById('departmentChart');
        if (deptCtx) {
            const departmentNames = [<?php 
                $deptNames = [];
                foreach ($departmentStats as $dept => $stats) {
                    $deptNames[] = "'" . addslashes($dept) . "'";
                }
                echo implode(', ', $deptNames);
            ?>];
            
            const presentData = [<?php 
                $deptPresent = [];
                foreach ($departmentStats as $dept => $stats) {
                    $deptPresent[] = $stats['present'];
                }
                echo implode(', ', $deptPresent);
            ?>];
            
            const absentData = [<?php 
                $deptAbsent = [];
                foreach ($departmentStats as $dept => $stats) {
                    $deptAbsent[] = $stats['absent'];
                }
                echo implode(', ', $deptAbsent);
            ?>];
            
            new Chart(deptCtx, {
                type: 'bar',
                data: {
                    labels: departmentNames,
                    datasets: [
                        {
                            label: 'Present',
                            data: presentData,
                            backgroundColor: 'rgba(40, 167, 69, 0.8)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Absent',
                            data: absentData,
                            backgroundColor: 'rgba(220, 53, 69, 0.7)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    });
    
    // Print attendance report
    function printAttendanceReport() {
        window.print();
    }
</script>

<?php include_once '../app/views/layouts/footer.php'; ?>
