<?php
// register.php

require_once 'configuration.php';
require_once 'csrf.php';
require_once 'rate_limit.php';
require_once 'captcha.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Prevent access if the user is already logged in
if (isset($_SESSION['user_id'])) {
    $errors = ["You are already logged in."];
    $success = false;
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

$errors = [];
$success = false;

// Check if registrations are allowed
if (!ALLOW_REGISTRATION) {
    $errors[] = "Registration is currently closed.";
}

// Rate Limiting
if (is_rate_limited($_SERVER['REMOTE_ADDR'])) {
    $errors[] = "Too many requests. Please try again later.";
}

// Get the Content-Type header
$contentType = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';

// Initialize $input array
$input = [];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    // Handle JSON input
    if (strpos($contentType, 'application/json') !== false) {
        // Receive the RAW post data
        $content = trim(file_get_contents('php://input'));
        $input = json_decode($content, true);
        if (!is_array($input)) {
            $errors[] = 'Invalid JSON input.';
        }
    } else {
        // Handle form-urlencoded or multipart/form-data
        $input = $_POST;
    }

    // CSRF Token validation
    $csrf_token = $input['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = "Invalid CSRF token.";
    }

    // CAPTCHA validation
    $captcha_input = $input['captcha'] ?? '';
    if (!validate_captcha($captcha_input)) {
        $errors[] = "CAPTCHA verification failed.";
    }

    // Honeypot field (anti-bot measure)
    if (!empty($input['website'])) {
        $errors[] = "Bot registrations are not allowed.";
    }

    // Collect and sanitize user inputs
    $username = trim($input['username'] ?? '');
    $email = trim($input['email'] ?? '');
    $confirm_email = trim($input['confirm_email'] ?? '');
    $password = $input['password'] ?? '';
    $confirm_password = $input['confirm_password'] ?? '';

    // Validate inputs
    if (empty($username) || !preg_match('/^[a-z0-9]{3,16}$/', $username)) {
        $errors[] = "Username must be 3-16 characters long and contain only lowercase letters and numbers.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || $email !== $confirm_email) {
        $errors[] = "Invalid or mismatched email address.";
    }

    if (empty($password) || strlen($password) < 8 || $password !== $confirm_password) {
        $errors[] = "Password must be at least 8 characters and match the confirmation.";
    }

    // Enforce strong passwords
    if (ENFORCE_STRONG_PASSWORDS) {
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must include at least one uppercase letter.";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must include at least one lowercase letter.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must include at least one number.";
        }
        if (!preg_match('/[@$!%*?&#]/', $password)) {
            $errors[] = "Password must include at least one special character.";
        }
    }

    // Proceed if no errors
    if (empty($errors)) {
        pg_query($member_conn, "BEGIN");
        pg_query($account_conn, "BEGIN");

        try {
            // Check if username exists
            $check_user = pg_prepare($member_conn, "check_user", "SELECT idnum FROM tb_user WHERE mid = $1");
            $result_user = pg_execute($member_conn, "check_user", [$username]);
            if (pg_num_rows($result_user) > 0) {
                throw new Exception("This username is already taken.");
            }

            // Check if email exists
            $check_email = pg_prepare($member_conn, "check_email", "SELECT idnum FROM tb_user WHERE mail_adress = $1");
            $result_email = pg_execute($member_conn, "check_email", [$email]);
            if (pg_num_rows($result_email) > 0) {
                throw new Exception("This email address is already in use.");
            }

            // Hash password securely
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Insert into FFMember database
            $stmt_member = pg_prepare($member_conn, "insert_member", "INSERT INTO tb_user (mid, mail_adress, password, pwd) VALUES ($1, $2, $3, $4)");
            $result_member = pg_execute($member_conn, "insert_member", [$username, $email, $password_hash, md5($password)]);

            if (!$result_member) {
                throw new Exception("Failed to add user to FFMember database.");
            }

            // Get user ID
            $stmt_id = pg_prepare($member_conn, "get_user_id", "SELECT idnum FROM tb_user WHERE mid = $1");
            $result_id = pg_execute($member_conn, "get_user_id", [$username]);
            if (!$result_id || pg_num_rows($result_id) === 0) {
                throw new Exception("Failed to retrieve user ID.");
            }
            $user = pg_fetch_assoc($result_id);
            $user_id = $user['idnum'];

            // Insert into FFAccount database
            $stmt_account = pg_prepare($account_conn, "insert_account", "INSERT INTO accounts (id, username, password) VALUES ($1, $2, $3)");
            $result_account = pg_execute($account_conn, "insert_account", [$user_id, $username, md5($password)]);

            if (!$result_account) {
                throw new Exception("Failed to add user to FFAccount database.");
            }

            // Update pvalues
            $stmt_pvalues = pg_prepare($member_conn, "update_pvalues", "UPDATE tb_user SET pvalues = $1 WHERE mid = $2");
            $result_pvalues = pg_execute($member_conn, "update_pvalues", [DEFAULT_PVALUES, $username]);

            if (!$result_pvalues) {
                throw new Exception("Failed to update pvalues.");
            }

            // Commit transactions
            pg_query($member_conn, "COMMIT");
            pg_query($account_conn, "COMMIT");

            // Increment rate limit
            increment_rate_limit($_SERVER['REMOTE_ADDR']);

            $success = true;
        } catch (Exception $e) {
            // Rollback transactions
            pg_query($member_conn, "ROLLBACK");
            pg_query($account_conn, "ROLLBACK");

            error_log("Registration failed: " . $e->getMessage());
            $errors[] = "An error occurred during registration. Please try again.";
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'errors' => $errors]);
}
?>