<?php
/**
 * Admin Users Management
 * 
 * This page allows administrators to view and manage user accounts.
 */

// Include initialization file
require_once '../includes/init.php';

// Require admin privileges
requireAdmin();

// Set page title
$pageTitle = 'Manage Users';

// Handle user status changes
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $userId = $_GET['id'];
    $action = $_GET['action'];
    
    // Prevent admins from modifying their own account
    if ($userId == $_SESSION['user_id']) {
        setFlashMessage('You cannot modify your own account.', 'danger');
        redirect('users.php');
    }
    
    switch ($action) {
        case 'verify':
            if ($userModel->verifyUserByAdmin($userId)) {
                setFlashMessage('User email verified successfully.', 'success');
            } else {
                setFlashMessage('Failed to verify user email.', 'danger');
            }
            break;
            
        case 'admin':
            if ($userModel->setUserRole($userId, 'admin')) {
                setFlashMessage('User promoted to admin successfully.', 'success');
            } else {
                setFlashMessage('Failed to promote user to admin.', 'danger');
            }
            break;
            
        case 'user':
            if ($userModel->setUserRole($userId, 'user')) {
                setFlashMessage('Admin privileges removed successfully.', 'success');
            } else {
                setFlashMessage('Failed to remove admin privileges.', 'danger');
            }
            break;
            
        case 'activate':
            if ($userModel->setUserStatus($userId, 1)) {
                setFlashMessage('User account activated successfully.', 'success');
            } else {
                setFlashMessage('Failed to activate user account.', 'danger');
            }
            break;
            
        case 'deactivate':
            if ($userModel->setUserStatus($userId, 0)) {
                setFlashMessage('User account deactivated successfully.', 'success');
            } else {
                setFlashMessage('Failed to deactivate user account.', 'danger');
            }
            break;
            
        default:
            setFlashMessage('Invalid action.', 'danger');
    }
    
    redirect('users.php');
}

// Get all users with sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$order = isset($_GET['order']) ? $_GET['order'] : 'asc';

// Validate sort and order parameters
$validSortFields = ['name', 'email', 'department', 'created_at'];
$validOrderValues = ['asc', 'desc'];

if (!in_array($sort, $validSortFields)) {
    $sort = 'name';
}

if (!in_array($order, $validOrderValues)) {
    $order = 'asc';
}

// Get users with sorting
$users = $userModel->getAll($sort, $order);

// Include header
include_once '../app/views/layouts/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Users</h1>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
    
    <!-- Users Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">All Users</h5>
                </div>
                <div class="col-auto">
                    <div class="input-group">
                        <input type="text" id="userSearch" class="form-control" placeholder="Search users...">
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
                                <a href="?sort=name&order=<?php echo $sort === 'name' && $order === 'asc' ? 'desc' : 'asc'; ?>" class="text-decoration-none text-dark">
                                    Name
                                    <?php if ($sort === 'name'): ?>
                                        <i class="fas fa-sort-<?php echo $order === 'asc' ? 'up' : 'down'; ?> ms-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="?sort=email&order=<?php echo $sort === 'email' && $order === 'asc' ? 'desc' : 'asc'; ?>" class="text-decoration-none text-dark">
                                    Email
                                    <?php if ($sort === 'email'): ?>
                                        <i class="fas fa-sort-<?php echo $order === 'asc' ? 'up' : 'down'; ?> ms-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="?sort=department&order=<?php echo $sort === 'department' && $order === 'asc' ? 'desc' : 'asc'; ?>" class="text-decoration-none text-dark">
                                    Department
                                    <?php if ($sort === 'department'): ?>
                                        <i class="fas fa-sort-<?php echo $order === 'asc' ? 'up' : 'down'; ?> ms-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col">Registration No.</th>
                            <th scope="col">
                                <a href="?sort=created_at&order=<?php echo $sort === 'created_at' && $order === 'asc' ? 'desc' : 'asc'; ?>" class="text-decoration-none text-dark">
                                    Joined
                                    <?php if ($sort === 'created_at'): ?>
                                        <i class="fas fa-sort-<?php echo $order === 'asc' ? 'up' : 'down'; ?> ms-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $index => $user): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($user['name']); ?>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-primary ms-1">Admin</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['department']); ?></td>
                                    <td><?php echo htmlspecialchars($user['reg_no']); ?></td>
                                    <td><?php echo formatDate($user['created_at']); ?></td>
                                    <td>
                                        <?php if ($user['is_active'] == 0): ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php else: ?>
                                            <?php if ($user['is_verified'] == 0): ?>
                                                <span class="badge bg-warning text-dark">Unverified</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <!-- View registrations -->
                                                <li>
                                                    <a class="dropdown-item" href="registrations.php?user_id=<?php echo $user['id']; ?>">
                                                        <i class="fas fa-ticket-alt me-2"></i>View Registrations
                                                    </a>
                                                </li>
                                                
                                                <!-- Email verification -->
                                                <?php if ($user['is_verified'] == 0): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="users.php?action=verify&id=<?php echo $user['id']; ?>">
                                                            <i class="fas fa-check-circle me-2"></i>Verify Email
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                <!-- Admin role management -->
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <?php if ($user['role'] === 'admin'): ?>
                                                        <li>
                                                            <a class="dropdown-item" href="users.php?action=user&id=<?php echo $user['id']; ?>">
                                                                <i class="fas fa-user me-2"></i>Remove Admin Role
                                                            </a>
                                                        </li>
                                                    <?php else: ?>
                                                        <li>
                                                            <a class="dropdown-item" href="users.php?action=admin&id=<?php echo $user['id']; ?>">
                                                                <i class="fas fa-user-shield me-2"></i>Make Admin
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                
                                                <!-- Account status management -->
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <?php if ($user['is_active'] == 1): ?>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="users.php?action=deactivate&id=<?php echo $user['id']; ?>">
                                                                <i class="fas fa-ban me-2"></i>Deactivate Account
                                                            </a>
                                                        </li>
                                                    <?php else: ?>
                                                        <li>
                                                            <a class="dropdown-item text-success" href="users.php?action=activate&id=<?php echo $user['id']; ?>">
                                                                <i class="fas fa-check me-2"></i>Activate Account
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="text-muted mb-0">No users found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // User search functionality
    document.getElementById('userSearch').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('tbody tr');
        
        tableRows.forEach(row => {
            const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const department = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
            const regNo = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
            
            if (name.includes(searchValue) || email.includes(searchValue) || 
                department.includes(searchValue) || regNo.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

<?php include_once '../app/views/layouts/footer.php'; ?>