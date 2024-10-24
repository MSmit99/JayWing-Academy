<?php
// src/data_src/api/login/login.php

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once '../../includes/session_handler.php';
require_once '../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $connection->real_escape_string(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'];

    try {
        $stmt = $connection->prepare("SELECT user_id, email, password, username FROM User WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($user_id, $email_db, $password_hash, $username);
        $stmt->fetch();
        $stmt->close();

        if ($email_db && password_verify($password, $password_hash)) {
            // Set session using session handler
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            
            // Regenerate session ID for security
            session_regenerate_id(true);

            echo json_encode(['success' => true, 'message' => 'Login successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Login failed: ' . $e->getMessage()]);
    }
}
?>