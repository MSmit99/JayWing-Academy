<?php
// src/data_src/api/login/signup.php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once '../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $connection->real_escape_string(filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $firstName = $connection->real_escape_string(filter_var($_POST['firstName'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $lastName = $connection->real_escape_string(filter_var($_POST['lastName'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $email = $connection->real_escape_string(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit;
    }

    try {
        // Check if email already exists
        $stmt = $connection->prepare("SELECT email FROM User WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            $stmt->close();
            exit;
        }
        $stmt->close();

        // Insert new user with additional fields
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $connection->prepare("INSERT INTO User (username, firstName, lastName, email, password, wings, admin) VALUES (?, ?, ?, ?, ?, 0, 0)");
        $stmt->bind_param('sssss', $username, $firstName, $lastName, $email, $hashed_password);
        $stmt->execute();

        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['username'] = $username;
        $_SESSION['firstName'] = $firstName;
        $_SESSION['lastName'] = $lastName;

        $stmt->close();

        echo json_encode(['success' => true, 'message' => 'Account created successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
    }
}
?>