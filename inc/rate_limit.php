<?php
define('RATE_LIMIT', 5);
define('RATE_LIMIT_WINDOW', 60);

function is_rate_limited($ip) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }

    $current_time = time();
    if (!isset($_SESSION['rate_limit'][$ip])) {
        $_SESSION['rate_limit'][$ip] = [];
    }

    // Clean up old requests
    $_SESSION['rate_limit'][$ip] = array_filter($_SESSION['rate_limit'][$ip], function($timestamp) use ($current_time) {
        return ($timestamp + RATE_LIMIT_WINDOW) > $current_time;
    });

    if (count($_SESSION['rate_limit'][$ip]) >= RATE_LIMIT) {
        return true;
    }

    return false;
}

function increment_rate_limit($ip) {
    $_SESSION['rate_limit'][$ip][] = time();
}
?>