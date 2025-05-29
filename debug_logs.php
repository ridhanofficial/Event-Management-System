<?php
/**
 * Debug and Logging Page
 * 
 * This page displays application logs for troubleshooting.
 */

// Include initialization file
require_once 'includes/init.php';

// Ensure this script is only accessible to admins
if (!isAdmin()) {
    setFlashMessage('You do not have permission to access this page.', 'danger');
    redirect('index.php');
}

// Set page title
$pageTitle = 'Debug Logs';

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/logs';
if (!file_exists($logsDir)) {
    mkdir($logsDir, 0755, true);
}

// Check for log clearing action
if (isset($_GET['clear']) && $_GET['clear'] == 'true') {
    $logFile = $logsDir . '/app_debug.log';
    if (file_exists($logFile)) {
        file_put_contents($logFile, '');
        setFlashMessage('Log file has been cleared.', 'success');
    }
    redirect('debug_logs.php');
}

// Create a test log entry if requested
if (isset($_GET['test']) && $_GET['test'] == 'true') {
    error_log("Test log entry created at " . date('Y-m-d H:i:s'));
    setFlashMessage('Test log entry created.', 'info');
    redirect('debug_logs.php');
}

// Include header
include_once 'app/views/layouts/header.php';

// Get the log file content
$logFile = $logsDir . '/app_debug.log';
$phpErrorLog = ini_get('error_log');

$appLogs = '';
if (file_exists($logFile)) {
    $appLogs = file_get_contents($logFile);
}

$phpLogs = '';
if (file_exists($phpErrorLog)) {
    $phpLogs = file_get_contents($phpErrorLog);
}
?>

<div class="container">
    <h1>Application Debug Logs</h1>
    
    <div class="mb-3">
        <a href="debug_logs.php?test=true" class="btn btn-primary me-2">
            <i class="fas fa-plus-circle"></i> Create Test Log
        </a>
        <a href="debug_logs.php?clear=true" class="btn btn-warning me-2" onclick="return confirm('Are you sure you want to clear the log file?')">
            <i class="fas fa-eraser"></i> Clear Application Logs
        </a>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Error Log Configuration</h5>
        </div>
        <div class="card-body">
            <p><strong>PHP error_log setting:</strong> <?php echo ini_get('error_log') ?: 'Not set (using default)'; ?></p>
            <p><strong>display_errors:</strong> <?php echo ini_get('display_errors') ? 'On' : 'Off'; ?></p>
            <p><strong>log_errors:</strong> <?php echo ini_get('log_errors') ? 'On' : 'Off'; ?></p>
            <p><strong>Application log path:</strong> <?php echo $logFile; ?></p>
        </div>
    </div>
    
    <!-- Application Logs -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Application Logs</h5>
        </div>
        <div class="card-body">
            <?php if (empty($appLogs)): ?>
                <div class="alert alert-info">No application logs found.</div>
            <?php else: ?>
                <pre class="bg-dark text-light p-3" style="max-height: 500px; overflow-y: auto; white-space: pre-wrap;"><?php echo htmlspecialchars($appLogs); ?></pre>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- PHP Error Logs -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">PHP Error Logs</h5>
        </div>
        <div class="card-body">
            <?php if (empty($phpLogs)): ?>
                <div class="alert alert-info">No PHP error logs found or file not accessible.</div>
            <?php else: ?>
                <pre class="bg-dark text-light p-3" style="max-height: 500px; overflow-y: auto; white-space: pre-wrap;"><?php echo htmlspecialchars($phpLogs); ?></pre>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include_once 'app/views/layouts/footer.php';
?>
