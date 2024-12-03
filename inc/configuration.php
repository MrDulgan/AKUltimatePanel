<?php
// /inc/configuration.php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Site Settings
define('SITE_TITLE', 'Aura Kingdom Eternal');
define('META_DESCRIPTION', 'Eternal always');
define('INSTALLATION_YEAR', '2024');
define('DEFAULT_PVALUES', 1453);
define('ALLOW_REGISTRATION', true);
define('ENFORCE_STRONG_PASSWORDS', true);
define('VERSION', '015.001.01.16');
define('SIZE', '15.7 GB');
define('SITE_URL', 'https://aurakralligi.mmotutkunu.com');

// Download Links
define('GOOGLE_DRIVE_LINK', '');
define('MEDIAFIRE_LINK', '');
define('MEGA_LINK', '');
define('GOFILE_LINK', '');

// PayPal API Settings
define('PAYPAL_CLIENT_ID', 'testa');
define('PAYPAL_SECRET', 'testx');
define('PAYPAL_SANDBOX', false); // 'true' for testing, 'false' for live
define('PAYPAL_CURRENCY', 'USD');

// Available PayPal Currencies (for reference)
define('AVAILABLE_CURRENCIES', json_encode([
    'AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'INR',
    'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP',
    'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD'
]));

// Donation Settings
define('DONATION_ENABLED', true); // Set to 'false' to disable the donation system
define('DONATION_AMOUNTS', [
    ['amount' => 5, 'points' => 600, 'bonus' => 0],
    ['amount' => 10, 'points' => 1200, 'bonus' => 100],
    ['amount' => 15, 'points' => 1800, 'bonus' => 200],
    ['amount' => 30, 'points' => 3600, 'bonus' => 400],
    ['amount' => 50, 'points' => 6000, 'bonus' => 800],
    ['amount' => 100, 'points' => 12000, 'bonus' => 2000],
]);

// Database Settings
define('DB_HOST', '185.158.132.113');
define('DB_PORT', '5432');
define('DB_MEMBER', 'FFMember');
define('DB_ACCOUNT', 'FFAccount');
define('DB_GAME', 'FFDB1');
define('DB_USER', 'postgres');
define('DB_PASSWORD', 'wpwcUZEsxt2tHX5PEqZ08NJcqi0XPvyW');

// Security Settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();

// Database Connections
$member_conn = pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_MEMBER . " user=" . DB_USER . " password=" . DB_PASSWORD);
$account_conn = pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_ACCOUNT . " user=" . DB_USER . " password=" . DB_PASSWORD);
$game_conn = pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_GAME . " user=" . DB_USER . " password=" . DB_PASSWORD);

if (!$member_conn || !$account_conn || !$game_conn) {
    error_log("Database connection failed.");
    die("Unable to establish database connection.");
}
?>