<?php
require_once '../data_src/includes/session_handler.php';
require_once '../data_src/includes/db_connect.php';

if (!isAdmin()) {
    header('Location: ../../index.php');
    exit();
}

// Get classes and users for dropdowns
$classes = $connection->query("SELECT * FROM Class");
$users = $connection->query("SELECT * FROM User");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JayWing Academy - Class Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <?php include '../components/navbar.php'; ?>
    </header>

    <main>
        <div class="container mt-4">
            <h2>Class Management</h2>
            <!-- Class Management Section -->
            <div class="card bg-dark text-white mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Manage Classes</h5>
                </div>
                <div class="card-body">
                    <form id="classForm">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="className" class="form-label">Class Name</label>
                                <input type="text" class="form-control bg-dark text-white" id="className" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="courseCode" class="form-label">Course Code</label>
                                <input type="text" class="form-control bg-dark text-white" id="courseCode" maxlength="7">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="classDescription" class="form-label">Description</label>
                                <textarea class="form-control bg-dark text-white" id="classDescription"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Class</button>
                    </form>
                    
                    <div class="table-responsive mt-4">
                        <table class="table table-dark">
                            <thead>
                                <tr>
                                    <th>Class Name</th>
                                    <th>Course Code</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="classesTable">
                                <!-- Filled dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add Enrollment Form -->
            <div class="card bg-dark text-white mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Add New Enrollment</h5>
                </div>
                <div class="card-body">
                    <form id="enrollmentForm">
                        <div class="row">
                        <!-- Class dropdown -->
                        <div class="col-md-4 mb-3">
                            <label for="class_id" class="form-label">Class</label>
                            <div class="dropdown">
                                <button class="btn btn-dark dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span id="selectedClassText">Select Class</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2 bg-dark">
                                    <input type="text" class="form-control bg-dark text-white mb-2" id="classSearchInput" placeholder="Search classes...">
                                    <div class="class-list" style="max-height: 200px; overflow-y: auto;">
                                        <?php while($class = $classes->fetch_assoc()): ?>
                                            <div class="dropdown-item text-white" data-value="<?= $class['class_id'] ?>">
                                                <?= htmlspecialchars($class['className']) ?> (<?= htmlspecialchars($class['courseCode']) ?>)
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                                <input type="hidden" id="class_id" name="class_id" required>
                            </div>
                        </div>

                        <!-- User dropdown -->
                        <div class="col-md-4 mb-3">
                            <label for="user_id" class="form-label">User</label>
                            <div class="dropdown">
                                <button class="btn btn-dark dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span id="selectedUserText">Select User</span>
                                </button>
                                <div class="dropdown-menu w-100 p-2 bg-dark">
                                    <input type="text" class="form-control bg-dark text-white mb-2" id="userSearchInput" placeholder="Search users...">
                                    <div class="user-list" style="max-height: 200px; overflow-y: auto;">
                                        <?php while($user = $users->fetch_assoc()): ?>
                                            <div class="dropdown-item text-white" data-value="<?= $user['user_id'] ?>">
                                                <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['firstName']) ?> <?= htmlspecialchars($user['lastName']) ?>)
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                                <input type="hidden" id="user_id" name="user_id" required>
                            </div>
                        </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="roleOfClass" class="form-label">Role</label>
                                <select class="form-select bg-dark text-white" id="roleOfClass" name="roleOfClass" required>
                                    <option value="tutor">Tutor</option>
                                    <option value="tutee">Tutee</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Add Enrollment</button>
                    </form>
                </div>
            </div>

            <!-- Enrollments Table -->
            <div class="card bg-dark text-white">
                <div class="card-header">
                    <h5 class="mb-0">Current Enrollments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="enrollmentsTable">
                                <!-- Filled dynamically via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-dark text-white">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Enrollment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editForm">
                            <input type="hidden" id="edit_enrollment_id">
                            <div class="mb-3">
                                <label for="edit_class_id" class="form-label">Class</label>
                                <select class="form-select bg-dark text-white" id="edit_class_id" required>
                                    <?php 
                                    $classes->data_seek(0);
                                    while($class = $classes->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $class['class_id'] ?>">
                                            <?= htmlspecialchars($class['className']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_user_id" class="form-label">User</label>
                                <select class="form-select bg-dark text-white" id="edit_user_id" required>
                                    <?php 
                                    $users->data_seek(0);
                                    while($user = $users->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $user['user_id'] ?>">
                                            <?= htmlspecialchars($user['username']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_roleOfClass" class="form-label">Role</label>
                                <select class="form-select bg-dark text-white" id="edit_roleOfClass" required>
                                    <option value="tutor">Tutor</option>
                                    <option value="tutee">Tutee</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer id="footer"></footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="../js/class_management.js"></script>
    <script src="../js/global.js"></script>
</body>
</html>