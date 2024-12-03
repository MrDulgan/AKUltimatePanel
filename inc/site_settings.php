<?php
// inc/site_settings.php
require_once 'configuration.php';
require_once 'csrf.php';

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!verify_csrf_token($data['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

if (!isset($_SESSION['authority']) || intval($_SESSION['authority']) != 5) {
    echo json_encode(['success' => false, 'message' => 'Insufficient permissions.']);
    exit;
}

// Validate and process form data
$siteTitle = trim($data['site_title']);
$metaDescription = trim($data['meta_description']);
$allowRegistration = isset($data['allow_registration']) ? 'true' : 'false';
$installationYear = trim($data['installation_year']);
$defaultPValues = intval($data['default_pvalues']);
$enforceStrongPasswords = isset($data['enforce_strong_passwords']) ? 'true' : 'false';
$version = trim($data['version']);
$size = trim($data['size']);
$siteURL = trim($data['site_url']);
$googleDriveLink = trim($data['google_drive_link']);
$mediafireLink = trim($data['mediafire_link']);
$megaLink = trim($data['mega_link']);
$gofileLink = trim($data['gofile_link']);
$paypalCurrency = trim($data['paypal_currency']);
$donationEnabled = isset($data['donation_enabled']) ? 'true' : 'false';

// Sanitize inputs as necessary
// ...

// Path to configuration.php
$configFilePath = __DIR__ . '/configuration.php';

// Read the existing configuration file
$configContent = file_get_contents($configFilePath);

if ($configContent === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to read configuration file.']);
    exit;
}

// Update the configuration values using regular expressions
$configContent = preg_replace("/define\('SITE_TITLE',\s*'[^']*'\);/", "define('SITE_TITLE', '" . addslashes($siteTitle) . "');", $configContent);
$configContent = preg_replace("/define\('META_DESCRIPTION',\s*'[^']*'\);/", "define('META_DESCRIPTION', '" . addslashes($metaDescription) . "');", $configContent);
$configContent = preg_replace("/define\('ALLOW_REGISTRATION',\s*(true|false)\);/", "define('ALLOW_REGISTRATION', $allowRegistration);", $configContent);
$configContent = preg_replace("/define\('INSTALLATION_YEAR',\s*'[^']*'\);/", "define('INSTALLATION_YEAR', '" . addslashes($installationYear) . "');", $configContent);
$configContent = preg_replace("/define\('DEFAULT_PVALUES',\s*[^;]*;/", "define('DEFAULT_PVALUES', $defaultPValues);", $configContent);
$configContent = preg_replace("/define\('ENFORCE_STRONG_PASSWORDS',\s*(true|false)\);/", "define('ENFORCE_STRONG_PASSWORDS', $enforceStrongPasswords);", $configContent);
$configContent = preg_replace("/define\('VERSION',\s*'[^']*'\);/", "define('VERSION', '" . addslashes($version) . "');", $configContent);
$configContent = preg_replace("/define\('SIZE',\s*'[^']*'\);/", "define('SIZE', '" . addslashes($size) . "');", $configContent);
$configContent = preg_replace("/define\('SITE_URL',\s*'[^']*'\);/", "define('SITE_URL', '" . addslashes($siteURL) . "');", $configContent);

// Update download links
$configContent = preg_replace("/define\('GOOGLE_DRIVE_LINK',\s*'[^']*'\);/", "define('GOOGLE_DRIVE_LINK', '" . addslashes($googleDriveLink) . "');", $configContent);
$configContent = preg_replace("/define\('MEDIAFIRE_LINK',\s*'[^']*'\);/", "define('MEDIAFIRE_LINK', '" . addslashes($mediafireLink) . "');", $configContent);
$configContent = preg_replace("/define\('MEGA_LINK',\s*'[^']*'\);/", "define('MEGA_LINK', '" . addslashes($megaLink) . "');", $configContent);
$configContent = preg_replace("/define\('GOFILE_LINK',\s*'[^']*'\);/", "define('GOFILE_LINK', '" . addslashes($gofileLink) . "');", $configContent);

// Update PayPal Currency
$configContent = preg_replace("/define\('PAYPAL_CURRENCY',\s*'[^']*'\);/", "define('PAYPAL_CURRENCY', '" . addslashes($paypalCurrency) . "');", $configContent);

// Update Donation Enabled
$configContent = preg_replace("/define\('DONATION_ENABLED',\s*(true|false)\);/", "define('DONATION_ENABLED', $donationEnabled);", $configContent);

// Write the updated configuration back to the file
if (file_put_contents($configFilePath, $configContent) === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to write to configuration file.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Site settings updated successfully.']);
?>