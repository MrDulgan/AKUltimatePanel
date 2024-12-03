<?php
// inc/donation.php

require_once 'configuration.php';
require_once 'csrf.php';
require_once 'paypal_api.php';
require_once 'rate_limit.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set Content-Type header to JSON
header('Content-Type: application/json');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to make a donation.']);
    exit;
}

// Rate Limiting
if (is_rate_limited($_SERVER['REMOTE_ADDR'])) {
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again later.']);
    exit;
}

// Get the Content-Type header
$contentType = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';

// Initialize $input array
$input = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle JSON input
    if (strpos($contentType, 'application/json') !== false) {
        // Receive the RAW post data
        $content = trim(file_get_contents('php://input'));
        $input = json_decode($content, true);
        if (!is_array($input)) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON input.']);
            exit;
        }
    } else {
        // Handle form-urlencoded or multipart/form-data
        $input = $_POST;
    }

    // CSRF Token validation
    $csrf_token = $input['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
        exit;
    }

    // Validate input data
    $amount = floatval($input['amount'] ?? 0);
    $points = intval($input['points'] ?? 0);
    $bonus = intval($input['bonus'] ?? 0);

    if ($amount <= 0 || $points <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid donation amount or points.']);
        exit;
    }

    // Process the donation using PayPal API
    try {
        $paypal = new PayPalAPI(PAYPAL_CLIENT_ID, PAYPAL_SECRET, PAYPAL_SANDBOX);
        $paymentResponse = $paypal->createPayment($amount, $points, $bonus);

        if ($paymentResponse['success']) {
            echo json_encode(['success' => true, 'redirect_url' => $paymentResponse['redirect_url']]);
        } else {
            echo json_encode(['success' => false, 'message' => $paymentResponse['message']]);
        }
    } catch (Exception $e) {
        // Log the exception
        error_log('Donation processing error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while processing your donation. Please try again later.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>