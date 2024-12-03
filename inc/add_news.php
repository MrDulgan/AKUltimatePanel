<?php
require_once 'configuration.php';
require_once 'csrf.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Read and decode JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($input === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input.']);
    exit;
}

// Validate CSRF token (use verify_csrf_token instead of validate_csrf_token)
if (!isset($input['csrf_token']) || !verify_csrf_token($input['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

// Check if user is logged in and has authority >= 5
if (!isset($_SESSION['authority']) || $_SESSION['authority'] < 5) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

// Get input data and sanitize
$title = isset($input['title']) ? trim($input['title']) : '';
$content = isset($input['content']) ? trim($input['content']) : '';
$category = isset($input['category']) ? strtoupper(trim($input['category'])) : '';

// Validate inputs
$errors = [];

if (empty($title)) {
    $errors[] = 'Title is required.';
}
if (empty($content)) {
    $errors[] = 'Content is required.';
}
if (empty($category)) {
    $errors[] = 'Category is required.';
} else {
    $valid_categories = ['UPDATE', 'IMPORTANT', 'EVENT', 'GENERAL'];
    if (!in_array($category, $valid_categories)) {
        $errors[] = 'Invalid category.';
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Insert into database
$insert_query = "INSERT INTO public.aku_news (title, content, category, created_at) VALUES ($1, $2, $3, NOW())";
$insert_stmt = pg_prepare($member_conn, "insert_news", $insert_query);

if (!$insert_stmt) {
    $error = pg_last_error($member_conn);
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $error]);
    exit;
}

$result = pg_execute($member_conn, "insert_news", [$title, $content, $category]);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'News added successfully.']);
} else {
    $error = pg_last_error($member_conn);
    echo json_encode(['success' => false, 'message' => 'Failed to add news: ' . $error]);
}

pg_close($member_conn);
?>