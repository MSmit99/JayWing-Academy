<?php
require_once __DIR__ . '/../data_src/includes/session_handler.php';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">JayWing Academy</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="/jaywing-academy/index.php">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Schedule
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/jaywing-academy/src/pages/calendar.php">Calendar</a></li>
                        <li><a class="dropdown-item" href="#">Appointments</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Tutors
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/jaywing-academy/src/pages/tutors.php">Find a Tutor</a></li>
                        <li><a class="dropdown-item" href="/jaywing-academy/src/pages/jobs.php">Job Board</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Wings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Messages</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/jaywing-academy/src/pages/profile.php">Profile</a>
                </li>
            </ul>
            <div class="ms-auto">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/src/pages/profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/jaywing-academy/src/data_src/api/login/logout.php">Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <button class="btn btn-outline-light" type="button" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Login Form -->
                <form id="loginForm" method="POST" onsubmit="handleLogin(event)">
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">Email address</label>
                        <input type="email" class="form-control bg-dark text-white" id="loginEmail" name="email" required>
                        </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Password</label>
                        <input type="password" class="form-control bg-dark text-white" id="loginPassword" name="password" required>
                        </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#signupModal" data-bs-dismiss="modal">Sign up</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Signup Modal -->
<div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="signupModalLabel">Create Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Signup Form -->
                <form id="signupForm" method="POST" onsubmit="handleSignup(event)">
                    <div class="mb-3">
                        <label for="signupUsername" class="form-label">Display Name</label>
                        <input type="text" class="form-control bg-dark text-white" id="signupUsername" name="username" required>
                        </div>
                    <div class="mb-3">
                        <label for="signupEmail" class="form-label">Email address</label>
                        <input type="email" class="form-control bg-dark text-white" id="signupEmail" name="email" required>
                        </div>
                    <div class="mb-3">
                        <label for="signupPassword" class="form-label">Password</label>
                        <input type="password" class="form-control bg-dark text-white" id="signupPassword" name="password" minlength="8" required>
                        </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control bg-dark text-white" id="confirmPassword" name="confirm_password" minlength="8" required>
                        </div>
                    <button type="submit" class="btn btn-primary w-100">Create Account</button>
                </form>
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>