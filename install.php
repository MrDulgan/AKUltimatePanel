<?php
// Check if installation has already been completed
$configPath = 'inc/configuration.php';
if (file_exists($configPath)) {
    header("Location: /");
    exit("Installation already completed. Please remove install.php.");
}

$errors = [];
$success = false;

// Ensure $http_only is defined
$http_only = isset($_POST['http_only']) ? true : false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input data
    $site_title = trim($_POST['site_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $default_pvalues = trim($_POST['default_pvalues'] ?? '');
    $allow_registration = isset($_POST['allow_registration']) ? 'true' : 'false';
    $enforce_strong_passwords = isset($_POST['enforce_strong_passwords']) ? 'true' : 'false';
    $site_url = trim($_POST['site_url'] ?? '');
    $version_input = trim($_POST['version'] ?? '');
    $version = $version_input;
    $size_input = trim($_POST['size'] ?? '');
    $size = $size_input . ' GB';

    $google_drive_link = trim($_POST['google_drive_link'] ?? '');
    $mediafire_link = trim($_POST['mediafire_link'] ?? '');
    $mega_link = trim($_POST['mega_link'] ?? '');
    $gofile_link = trim($_POST['gofile_link'] ?? '');

    $paypal_client_id = trim($_POST['paypal_client_id'] ?? '');
    $paypal_secret = trim($_POST['paypal_secret'] ?? '');
    $paypal_sandbox = isset($_POST['paypal_sandbox']) ? 'true' : 'false';
    $paypal_currency = trim($_POST['paypal_currency'] ?? '');

    $donation_enabled = isset($_POST['donation_enabled']) && !$http_only ? 'true' : 'false';
    $donation_amounts = $_POST['donation_amounts'] ?? [];

    $db_host = trim($_POST['db_host'] ?? '');
    $db_port = trim($_POST['db_port'] ?? '5432');
    $db_member = trim($_POST['db_member'] ?? 'FFMember');
    $db_account = trim($_POST['db_account'] ?? 'FFAccount');
    $db_game = trim($_POST['db_game'] ?? 'FFDB1');
    $db_user = trim($_POST['db_user'] ?? 'postgres'); // Pre-filled with 'postgres'
    $db_password = trim($_POST['db_password'] ?? '');

    // Check if pgsql extension is enabled
    if (!extension_loaded('pgsql')) {
        $errors[] = "PostgreSQL extension is not enabled. Please enable it to proceed with installation.";
    }

    // Validate input fields
    if (empty($site_title)) {
        $errors[] = "Site title cannot be empty.";
    }
    if (empty($meta_description)) {
        $errors[] = "Meta description cannot be empty.";
    }
    if (empty($default_pvalues) || !is_numeric($default_pvalues)) {
        $errors[] = "Please enter a valid default PValues.";
    }

    // Site URL validation
    if ($http_only) {
        if (!preg_match('/^http:\/\/.+/', $site_url)) {
            $errors[] = "Please enter a valid site URL starting with 'http://'.";
        }
    } else {
        if (!preg_match('/^https:\/\/.+/', $site_url)) {
            $errors[] = "The site URL must start with 'https://'.";
        }
    }

    if (empty($version_input)) {
        $errors[] = "Version cannot be empty.";
    }
    if (empty($size_input)) {
        $errors[] = "Size cannot be empty.";
    }

    // Validate PayPal settings if donations are enabled
    if ($donation_enabled === 'true') {
        if (empty($paypal_client_id)) {
            $errors[] = "PayPal Client ID cannot be empty.";
        }
        if (empty($paypal_secret)) {
            $errors[] = "PayPal Secret cannot be empty.";
        }
        if (empty($paypal_currency)) {
            $errors[] = "Please select a PayPal currency.";
        }
    }

    // Validate database settings
    if (empty($db_host)) {
        $errors[] = "Database host cannot be empty.";
    }
    if (empty($db_port) || !is_numeric($db_port)) {
        $errors[] = "Please enter a valid database port.";
    }
    if (empty($db_member)) {
        $errors[] = "FFMember database name cannot be empty.";
    }
    if (empty($db_account)) {
        $errors[] = "FFAccount database name cannot be empty.";
    }
    if (empty($db_game)) {
        $errors[] = "FFDB1 database name cannot be empty.";
    }
    if (empty($db_user)) {
        $errors[] = "Database username cannot be empty.";
    }
    if (empty($db_password)) {
        $errors[] = "Database password cannot be empty.";
    }

    // If no errors, proceed with configuration
    if (empty($errors)) {
        // Initialize database connection
        $member_conn = @pg_connect("host=$db_host port=$db_port dbname=$db_member user=$db_user password=$db_password");
        $account_conn = @pg_connect("host=$db_host port=$db_port dbname=$db_account user=$db_user password=$db_password");

        if (!$member_conn || !$account_conn) {
            $errors[] = "Failed to connect to the database. Please check your database credentials.";
        }

        // Only proceed if no connection errors
        if (empty($errors)) {
            // Check if required columns exist and add them if they don't
            $columns_to_add = [
                'mail_adress' => 'VARCHAR(255)',
                'failed_attempts' => 'INT DEFAULT 0',
                'last_failed_attempt' => 'TIMESTAMP'
            ];

            foreach ($columns_to_add as $column => $type) {
                $check_column_query = "SELECT column_name FROM information_schema.columns WHERE table_schema = 'public' AND table_name = 'tb_user' AND column_name = '$column';";
                $check_result = pg_query($member_conn, $check_column_query);
                if (!$check_result) {
                    $errors[] = "Failed to check existing columns in tb_user table: " . pg_last_error($member_conn);
                } else {
                    if (pg_num_rows($check_result) == 0) {
                        // Column doesn't exist, add it
                        $alter_table_query = "ALTER TABLE tb_user ADD COLUMN $column $type;";
                        $alter_result = pg_query($member_conn, $alter_table_query);
                        if (!$alter_result) {
                            $errors[] = "Failed to add '$column' column to tb_user table: " . pg_last_error($member_conn);
                        }
                    }
                    pg_free_result($check_result);
                }
            }

            // Check and create 'aku_donations' table if it doesn't exist
            $check_donations_table_query = "SELECT to_regclass('public.aku_donations');";
            $check_donations_table_result = pg_query($member_conn, $check_donations_table_query);

            if ($check_donations_table_result) {
                $donations_table_exists = pg_fetch_result($check_donations_table_result, 0, 0);
                if (!$donations_table_exists) {
                    // Table does not exist, create it
                    $create_donations_table_query = "CREATE TABLE public.aku_donations (
                        id SERIAL PRIMARY KEY,
                        user_id INTEGER NOT NULL,
                        points_awarded INTEGER NOT NULL,
                        bonus_points INTEGER NOT NULL,
                        total_points INTEGER NOT NULL,
                        amount_paid NUMERIC(10, 2) NOT NULL,
                        payment_id VARCHAR(255) NOT NULL UNIQUE,
                        payment_status VARCHAR(50) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );";
                    $create_donations_table_result = pg_query($member_conn, $create_donations_table_query);
                    if (!$create_donations_table_result) {
                        $errors[] = "Failed to create 'aku_donations' table: " . pg_last_error($member_conn);
                    }
                }
                pg_free_result($check_donations_table_result);
            } else {
                $errors[] = "Failed to check for 'aku_donations' table: " . pg_last_error($member_conn);
            }

            // Check and create 'aku_news' table if it doesn't exist
            $check_news_table_query = "SELECT to_regclass('public.aku_news');";
            $check_news_table_result = pg_query($member_conn, $check_news_table_query);

            if ($check_news_table_result) {
                $news_table_exists = pg_fetch_result($check_news_table_result, 0, 0);
                if (!$news_table_exists) {
                    // Table does not exist, create it
                    $create_news_table_query = "CREATE TABLE public.aku_news (
                        id SERIAL PRIMARY KEY,
                        title TEXT NOT NULL,
                        content TEXT NOT NULL,
                        category TEXT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );";
                    $create_news_table_result = pg_query($member_conn, $create_news_table_query);
                    if (!$create_news_table_result) {
                        $errors[] = "Failed to create 'aku_news' table: " . pg_last_error($member_conn);
                    }
                }
                pg_free_result($check_news_table_result);
            } else {
                $errors[] = "Failed to check for 'aku_news' table: " . pg_last_error($member_conn);
            }
        }

        // If no errors, create configuration.php
        if (empty($errors)) {
            $installation_year = date('Y');

            // Prepare donation amounts
            $donation_amounts_array = [];
            foreach ($donation_amounts as $donation) {
                $amount = floatval($donation['amount']);
                $points = intval($donation['points']);
                $bonus = intval($donation['bonus']);
                if ($amount > 0 && $points > 0) {
                    $donation_amounts_array[] = [
                        'amount' => $amount,
                        'points' => $points,
                        'bonus' => $bonus
                    ];
                }
            }

            // Custom function to generate array code in desired format
            function arrayToPhpArrayCode($array) {
                $items = [];
                foreach ($array as $subArray) {
                    $elements = [];
                    foreach ($subArray as $key => $value) {
                        // Decide how to represent the value
                        if (is_int($value) || (is_float($value) && intval($value) == $value)) {
                            // Output integer without decimal point
                            $valueCode = intval($value);
                        } elseif (is_float($value)) {
                            // Output float
                            $valueCode = $value;
                        } else {
                            $valueCode = var_export($value, true);
                        }
                        $elements[] = "'$key' => $valueCode";
                    }
                    $items[] = '[' . implode(', ', $elements) . ']';
                }
                $code = "define('DONATION_AMOUNTS', [\n    " . implode(",\n    ", $items) . "\n]);";
                return $code;
            }

            // Generate the donation amounts code
            $donation_amounts_code = arrayToPhpArrayCode($donation_amounts_array);

            // Prepare the configuration content
            $configContent = "<?php
// /inc/configuration.php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Site Settings
define('SITE_TITLE', '" . addslashes($site_title) . "');
define('META_DESCRIPTION', '" . addslashes($meta_description) . "');
define('INSTALLATION_YEAR', '" . $installation_year . "');
define('DEFAULT_PVALUES', " . intval($default_pvalues) . ");
define('ALLOW_REGISTRATION', " . $allow_registration . ");
define('ENFORCE_STRONG_PASSWORDS', " . $enforce_strong_passwords . ");
define('VERSION', '" . addslashes($version) . "');
define('SIZE', '" . addslashes($size) . "');
define('SITE_URL', '" . addslashes($site_url) . "');

// Download Links
define('GOOGLE_DRIVE_LINK', '" . addslashes($google_drive_link) . "');
define('MEDIAFIRE_LINK', '" . addslashes($mediafire_link) . "');
define('MEGA_LINK', '" . addslashes($mega_link) . "');
define('GOFILE_LINK', '" . addslashes($gofile_link) . "');

// PayPal API Settings
define('PAYPAL_CLIENT_ID', '" . addslashes($paypal_client_id) . "');
define('PAYPAL_SECRET', '" . addslashes($paypal_secret) . "');
define('PAYPAL_SANDBOX', " . $paypal_sandbox . "); // 'true' for testing, 'false' for live
define('PAYPAL_CURRENCY', '" . addslashes($paypal_currency) . "');

// Available PayPal Currencies (for reference)
define('AVAILABLE_CURRENCIES', json_encode([
    'AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'INR',
    'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP',
    'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD'
]));

// Donation Settings
define('DONATION_ENABLED', " . $donation_enabled . "); // Set to 'false' to disable the donation system
" . $donation_amounts_code . "

// Database Settings
define('DB_HOST', '" . addslashes($db_host) . "');
define('DB_PORT', '" . intval($db_port) . "');
define('DB_MEMBER', '" . addslashes($db_member) . "');
define('DB_ACCOUNT', '" . addslashes($db_account) . "');
define('DB_GAME', '" . addslashes($db_game) . "');
define('DB_USER', '" . addslashes($db_user) . "');
define('DB_PASSWORD', '" . addslashes($db_password) . "');

// Security Settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();

// Database Connections
\$member_conn = pg_connect(\"host=\" . DB_HOST . \" port=\" . DB_PORT . \" dbname=\" . DB_MEMBER . \" user=\" . DB_USER . \" password=\" . DB_PASSWORD);
\$account_conn = pg_connect(\"host=\" . DB_HOST . \" port=\" . DB_PORT . \" dbname=\" . DB_ACCOUNT . \" user=\" . DB_USER . \" password=\" . DB_PASSWORD);
\$game_conn = pg_connect(\"host=\" . DB_HOST . \" port=\" . DB_PORT . \" dbname=\" . DB_GAME . \" user=\" . DB_USER . \" password=\" . DB_PASSWORD);

if (!\$member_conn || !\$account_conn || !\$game_conn) {
    error_log(\"Database connection failed.\");
    die(\"Unable to establish database connection.\");
}
?>";

            // Create /inc directory if it doesn't exist
            $inc_dir = 'inc';
            if (!file_exists($inc_dir)) {
                if (!mkdir($inc_dir, 0700, true)) {
                    $errors[] = "Failed to create inc directory.";
                }
            }

            // Create configuration.php file
            $configFilePath = $inc_dir . '/configuration.php';
            if (file_put_contents($configFilePath, $configContent)) {
                chmod($configFilePath, 0600);

                // If no errors, mark success and attempt to delete install.php
                if (empty($errors)) {
                    $success = true;

                    // Self-delete: remove install.php
                    if (!unlink(__FILE__)) {
                        $errors[] = "Installation successful, but failed to delete install.php. Please remove it manually.";
                    }
                }
            } else {
                $errors[] = "Failed to create configuration.php.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Updated explanations -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Kingdom Ultimate Website Configuration</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        /* Center the form headings */
        .form-section-title {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 1.25rem;
        }
        /* Card style */
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        /* Center the header text */
        .navbar-brand {
            margin: 0 auto;
            text-align: center;
            width: 100%;
        }
        /* Style success and error messages */
        .alert {
            border-radius: 8px;
            font-size: 1rem;
        }
        /* Add spacing between form sections */
        .form-section {
            margin-bottom: 30px;
        }
        /* Style tooltips */
        .form-text {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Aura Kingdom Ultimate Website Configuration</a>
    </div>
</nav>

<!-- Main Content -->
<div class="container my-5">
    <?php if ($success): ?>
        <!-- Center Align the Installation Success Message -->
        <div class="alert alert-success text-center" role="alert">
            <h4 class="alert-heading">Installation Successful!</h4>
            <p>The <code>configuration.php</code> file has been created.</p>
            <p>For security reasons, <code>install.php</code> has been automatically deleted. However, please check and ensure it has been removed manually.</p>
            <hr>
            <p>You will be redirected to the homepage in <span id="countdown">5</span> seconds.</p>
        </div>
        <script>
            // Redirect after 5 seconds
            let countdown = 5;
            const interval = setInterval(function() {
                countdown--;
                document.getElementById('countdown').textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(interval);
                    window.location.href = '/';
                }
            }, 1000);
        </script>
    <?php else: ?>
        <!-- Display Errors if Any -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Installation Form -->
        <div class="card p-4">
            <form method="POST" action="install.php">
                <!-- Site Settings Section -->
                <div class="form-section">
                    <div class="form-section-title">Site Settings</div>

                    <!-- Site Title -->
                    <div class="mb-3">
                        <label for="site_title" class="form-label">Site Title</label>
                        <input type="text" class="form-control" id="site_title" name="site_title" required
                               value="<?php echo htmlspecialchars($_POST['site_title'] ?? ''); ?>">
                        <div class="form-text">The name of your website, displayed in the browser title and headers.</div>
                    </div>

                    <!-- Meta Description -->
                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" rows="3" required><?php echo htmlspecialchars($_POST['meta_description'] ?? ''); ?></textarea>
                        <div class="form-text">A brief description of your website for search engines.</div>
                    </div>

                    <!-- Default PValues -->
                    <div class="mb-3">
                        <label for="default_pvalues" class="form-label">Default PValues</label>
                        <input type="number" class="form-control" id="default_pvalues" name="default_pvalues" required
                               value="<?php echo htmlspecialchars($_POST['default_pvalues'] ?? '999999'); ?>">
                        <div class="form-text">The default amount of PValues (points) new users receive upon registration.</div>
                    </div>

                    <!-- Allow User Registrations -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="allow_registration" name="allow_registration" <?php echo isset($_POST['allow_registration']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="allow_registration">
                            Allow User Registrations
                        </label>
                        <div class="form-text">Enable or disable user registrations on your site.</div>
                    </div>

                    <!-- Enforce Strong Passwords -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="enforce_strong_passwords" name="enforce_strong_passwords" <?php echo isset($_POST['enforce_strong_passwords']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="enforce_strong_passwords">
                            Enforce Strong Passwords
                        </label>
                        <div class="form-text">Require users to create passwords with a minimum level of complexity.</div>
                    </div>

                    <!-- Site URL -->
                    <div class="mb-3">
                        <label for="site_url" class="form-label">Site URL</label>
                        <input type="url" class="form-control" id="site_url" name="site_url" required
                               value="<?php echo htmlspecialchars($_POST['site_url'] ?? ''); ?>">
                        <div class="form-text">The base URL of your website, e.g., 'https://your-domain.com'</div>
                    </div>

                    <!-- HTTP Only Option -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="http_only" name="http_only" <?php echo isset($_POST['http_only']) ? 'checked' : ''; ?> onchange="toggleDonationSettings()">
                        <label class="form-check-label" for="http_only">
                            HTTP Only (Use HTTP instead of HTTPS)
                        </label>
                        <div class="form-text">Enable this option if you are using localhost or an IP address without SSL.</div>
                    </div>

                    <!-- Version -->
                    <div class="mb-3">
                        <label for="version" class="form-label">Version</label>
                        <input type="text" class="form-control" id="version" name="version" required
                               value="<?php echo htmlspecialchars($_POST['version'] ?? '015.001.01.16'); ?>">
                        <div class="form-text">The current version of your client.</div>
                    </div>

                    <!-- Size -->
                    <div class="mb-3">
                        <label for="size" class="form-label">Size</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="size" name="size" required
                                   value="<?php echo htmlspecialchars($_POST['size'] ?? ''); ?>">
                            <span class="input-group-text">GB</span>
                        </div>
                        <div class="form-text">The total size of your client.</div>
                    </div>
                </div>

                <!-- Download Links Section -->
                <div class="form-section">
                    <div class="form-section-title">Download Links</div>

                    <!-- Google Drive Link -->
                    <div class="mb-3">
                        <label for="google_drive_link" class="form-label">Google Drive Link</label>
                        <input type="url" class="form-control" id="google_drive_link" name="google_drive_link"
                               value="<?php echo htmlspecialchars($_POST['google_drive_link'] ?? ''); ?>">
                        <div class="form-text">Link to your client on Google Drive.</div>
                    </div>

                    <!-- MediaFire Link -->
                    <div class="mb-3">
                        <label for="mediafire_link" class="form-label">MediaFire Link</label>
                        <input type="url" class="form-control" id="mediafire_link" name="mediafire_link"
                               value="<?php echo htmlspecialchars($_POST['mediafire_link'] ?? ''); ?>">
                        <div class="form-text">Link to your client on MediaFire.</div>
                    </div>

                    <!-- Mega.nz Link -->
                    <div class="mb-3">
                        <label for="mega_link" class="form-label">Mega.nz Link</label>
                        <input type="url" class="form-control" id="mega_link" name="mega_link"
                               value="<?php echo htmlspecialchars($_POST['mega_link'] ?? ''); ?>">
                        <div class="form-text">Link to your client on Mega.nz.</div>
                    </div>

                    <!-- GoFile Link -->
                    <div class="mb-3">
                        <label for="gofile_link" class="form-label">GoFile Link</label>
                        <input type="url" class="form-control" id="gofile_link" name="gofile_link"
                               value="<?php echo htmlspecialchars($_POST['gofile_link'] ?? ''); ?>">
                        <div class="form-text">Link to your client on GoFile.</div>
                    </div>
                </div>

                <!-- Donation Settings Section -->
                <div class="form-section">
                    <div class="form-section-title">Donation Settings</div>

                    <!-- Enable Donations -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="donation_enabled" name="donation_enabled" <?php echo isset($_POST['donation_enabled']) && !$http_only ? 'checked' : ''; ?> <?php echo $http_only ? 'disabled' : ''; ?> onchange="toggleDonationSections()">
                        <label class="form-check-label" for="donation_enabled">
                            Enable Donations
                        </label>
                        <div class="form-text">Enable or disable the donation system.</div>
                    </div>

                    <!-- PayPal API Settings -->
                    <div id="paypal-settings" style="display: <?php echo isset($_POST['donation_enabled']) && !$http_only ? 'block' : 'none'; ?>;">
                        <div class="form-section-title">PayPal API Settings</div>

                        <!-- PayPal Client ID -->
                        <div class="mb-3">
                            <label for="paypal_client_id" class="form-label">PayPal Client ID</label>
                            <input type="text" class="form-control" id="paypal_client_id" name="paypal_client_id"
                                   value="<?php echo htmlspecialchars($_POST['paypal_client_id'] ?? ''); ?>">
                            <div class="form-text">Your PayPal API Client ID.</div>
                        </div>

                        <!-- PayPal Secret -->
                        <div class="mb-3">
                            <label for="paypal_secret" class="form-label">PayPal Secret</label>
                            <input type="text" class="form-control" id="paypal_secret" name="paypal_secret"
                                   value="<?php echo htmlspecialchars($_POST['paypal_secret'] ?? ''); ?>">
                            <div class="form-text">Your PayPal API Secret.</div>
                        </div>

                        <!-- PayPal Sandbox Mode -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="paypal_sandbox" name="paypal_sandbox" <?php echo isset($_POST['paypal_sandbox']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="paypal_sandbox">
                                Enable PayPal Sandbox Mode
                            </label>
                            <div class="form-text">Enable sandbox mode for testing (set to 'true' for testing, 'false' for live).</div>
                        </div>

                        <!-- PayPal Currency -->
                        <div class="mb-3">
                            <label for="paypal_currency" class="form-label">PayPal Currency</label>
                            <select class="form-select" id="paypal_currency" name="paypal_currency">
                                <option value="">Select Currency</option>
                                <?php
                                $currencies = ['AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'INR',
                                               'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP',
                                               'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD'];
                                foreach ($currencies as $currency) {
                                    $selected = (isset($_POST['paypal_currency']) && $_POST['paypal_currency'] === $currency) ? 'selected' : '';
                                    echo "<option value=\"$currency\" $selected>$currency</option>";
                                }
                                ?>
                            </select>
                            <div class="form-text">Select the default currency for PayPal transactions.</div>
                        </div>
                    </div>

                    <!-- Donation Amounts and Points -->
                    <div id="donation-settings" style="display: <?php echo isset($_POST['donation_enabled']) && !$http_only ? 'block' : 'none'; ?>;">
                        <label class="form-label">Donation Amounts and Points</label>
                        <div class="form-text mb-2">Configure donation amounts and the corresponding points and bonuses.</div>

                        <!-- Table Headers -->
                        <div class="row mb-2">
                            <div class="col"><strong>Price</strong></div>
                            <div class="col"><strong>Points</strong></div>
                            <div class="col"><strong>Bonus</strong></div>
                        </div>

                        <?php
                        $donation_amounts = $_POST['donation_amounts'] ?? [
                            ['amount' => 5.00, 'points' => 600, 'bonus' => 0],
                            ['amount' => 10.00, 'points' => 1200, 'bonus' => 100],
                            ['amount' => 15.00, 'points' => 1800, 'bonus' => 200],
                        ];
                        foreach ($donation_amounts as $index => $donation) {
                            ?>
                            <div class="row mb-2 donation-row">
                                <div class="col">
                                    <input type="number" step="0.01" class="form-control" name="donation_amounts[<?php echo $index; ?>][amount]" placeholder="Price"
                                           value="<?php echo htmlspecialchars($donation['amount'] ?? ''); ?>">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" name="donation_amounts[<?php echo $index; ?>][points]" placeholder="Points"
                                           value="<?php echo htmlspecialchars($donation['points'] ?? ''); ?>">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" name="donation_amounts[<?php echo $index; ?>][bonus]" placeholder="Bonus"
                                           value="<?php echo htmlspecialchars($donation['bonus'] ?? ''); ?>">
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addDonationAmount()">Add More</button>
                    </div>
                </div>

                <!-- Database Settings Section -->
                <div class="form-section">
                    <div class="form-section-title">Database Settings</div>

                    <!-- Database Host -->
                    <div class="mb-3">
                        <label for="db_host" class="form-label">Database Host</label>
                        <input type="text" class="form-control" id="db_host" name="db_host" required
                               value="<?php echo htmlspecialchars($_POST['db_host'] ?? 'localhost'); ?>">
                        <div class="form-text">The hostname of your database server.</div>
                    </div>

                    <!-- Database Port -->
                    <div class="mb-3">
                        <label for="db_port" class="form-label">Database Port</label>
                        <input type="number" class="form-control" id="db_port" name="db_port" required
                               value="<?php echo htmlspecialchars($_POST['db_port'] ?? '5432'); ?>">
                        <div class="form-text">The port number for your database server.</div>
                    </div>

                    <!-- FFMember Database Name -->
                    <div class="mb-3">
                        <label for="db_member" class="form-label">FFMember Database Name</label>
                        <input type="text" class="form-control" id="db_member" name="db_member" required
                               value="<?php echo htmlspecialchars($_POST['db_member'] ?? 'FFMember'); ?>">
                        <div class="form-text">The database name for FFMember.</div>
                    </div>

                    <!-- FFAccount Database Name -->
                    <div class="mb-3">
                        <label for="db_account" class="form-label">FFAccount Database Name</label>
                        <input type="text" class="form-control" id="db_account" name="db_account" required
                               value="<?php echo htmlspecialchars($_POST['db_account'] ?? 'FFAccount'); ?>">
                        <div class="form-text">The database name for FFAccount.</div>
                    </div>

                    <!-- FFDB1 Database Name -->
                    <div class="mb-3">
                        <label for="db_game" class="form-label">FFDB1 Database Name</label>
                        <input type="text" class="form-control" id="db_game" name="db_game" required
                               value="<?php echo htmlspecialchars($_POST['db_game'] ?? 'FFDB1'); ?>">
                        <div class="form-text">The database name for FFDB1.</div>
                    </div>

                    <!-- Database Username -->
                    <div class="mb-3">
                        <label for="db_user" class="form-label">Database Username</label>
                        <input type="text" class="form-control" id="db_user" name="db_user" required
                               value="<?php echo htmlspecialchars($_POST['db_user'] ?? 'postgres'); ?>">
                        <div class="form-text">The username to connect to your database.</div>
                    </div>

                    <!-- Database Password -->
                    <div class="mb-3">
                        <label for="db_password" class="form-label">Database Password</label>
                        <input type="password" class="form-control" id="db_password" name="db_password" required>
                        <div class="form-text">The password to connect to your database.</div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" class="btn btn-primary w-100">Complete Installation</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="footer bg-light py-4">
    <div class="container text-center">
        <p>&copy; <?php echo date('Y'); ?> Aura Kingdom Ultimate. All rights reserved. Developed by Dulgan</p>
    </div>
</footer>

<script src="js/bootstrap.bundle.min.js"></script>
<script>
    function addDonationAmount() {
        const container = document.getElementById('donation-settings');
        const existingRows = container.querySelectorAll('.donation-row');
        const index = existingRows.length;

        if (index >= 12) {
            alert('You can only add up to 12 donation amounts.');
            return;
        }

        const row = document.createElement('div');
        row.className = 'row mb-2 donation-row';
        row.innerHTML = `
            <div class="col">
                <input type="number" step="0.01" class="form-control" name="donation_amounts[${index}][amount]" placeholder="Price">
            </div>
            <div class="col">
                <input type="number" class="form-control" name="donation_amounts[${index}][points]" placeholder="Points">
            </div>
            <div class="col">
                <input type="number" class="form-control" name="donation_amounts[${index}][bonus]" placeholder="Bonus">
            </div>
        `;
        container.insertBefore(row, container.lastElementChild);
    }

    function toggleDonationSections() {
        const donationEnabled = document.getElementById('donation_enabled').checked;
        const paypalSettings = document.getElementById('paypal-settings');
        const donationSettings = document.getElementById('donation-settings');
        paypalSettings.style.display = donationEnabled ? 'block' : 'none';
        donationSettings.style.display = donationEnabled ? 'block' : 'none';
    }

    function toggleDonationSettings() {
        const httpOnly = document.getElementById('http_only').checked;
        const donationEnabledCheckbox = document.getElementById('donation_enabled');
        if (httpOnly) {
            donationEnabledCheckbox.checked = false;
            donationEnabledCheckbox.disabled = true;
            toggleDonationSections();
        } else {
            donationEnabledCheckbox.disabled = false;
        }
    }

    // Initialize the form on page load
    window.onload = function() {
        toggleDonationSettings();
        toggleDonationSections();
    };
</script>
</body>
</html>