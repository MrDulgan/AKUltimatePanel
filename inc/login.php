<?php
// login.php

// Include configuration file for settings
require_once 'configuration.php';
require_once 'csrf.php';

// Set response header to JSON
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
];

// Start session with secure settings based on SITE_URL
$sessionOptions = [
    'cookie_httponly' => true,
    'cookie_secure' => (parse_url(SITE_URL, PHP_URL_SCHEME) === 'https'),
    'use_strict_mode' => true
];
session_start($sessionOptions);

// Define session timeout duration (e.g., 15 minutes)
define('SESSION_TIMEOUT', 900); // 900 seconds = 15 minutes

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    $response['message'] = 'Session expired due to inactivity.';
    echo json_encode($response);
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

$contentType = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';
$input = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (strpos($contentType, 'application/json') !== false) {
        $content = trim(file_get_contents('php://input'));
        $input = json_decode($content, true);
        if (!is_array($input)) {
            $response['message'] = 'Invalid JSON input.';
            echo json_encode($response);
            exit();
        }
    } else {
        $input = $_POST;
    }

    $action = $input['action'] ?? '';
    $csrf_token = $input['csrf_token'] ?? '';

    // Verify CSRF token
    if (!verify_csrf_token($csrf_token)) {
        $response['message'] = 'Invalid CSRF token.';
        echo json_encode($response);
        exit();
    }

    if ($action === 'login') {
        // Get and sanitize input data
        $username = trim(strtolower($input['username'] ?? ''));
        $password = $input['password'] ?? '';

        // Validate username contains only lowercase letters
        if (!ctype_lower($username)) {
            $response['message'] = 'Username must be lowercase letters only.';
            echo json_encode($response);
            exit();
        }

        if (empty($username) || empty($password)) {
            $response['message'] = 'Username and password cannot be empty.';
            echo json_encode($response);
            exit();
        }

        $password_md5 = md5($password);

        // Query user info
        $user_query = "SELECT idnum, mid, pwd, pvalues, bonus, regdate, lastlogindate, updatetime, mail_adress FROM tb_user WHERE mid = $1 AND pwd = $2";
        $user_result = pg_query_params($member_conn, $user_query, [$username, $password_md5]);

        if ($user_result && pg_num_rows($user_result) > 0) {
            $user = pg_fetch_assoc($user_result);

            // Fetch authority
            $gm_query = "SELECT privilege FROM gm_tool_accounts WHERE account_name = $1";
            $gm_result = pg_query_params($account_conn, $gm_query, [$username]);
            $authority = 0;

            if ($gm_result && pg_num_rows($gm_result) > 0) {
                $gm_account = pg_fetch_assoc($gm_result);
                $authority = intval($gm_account['privilege']);
            }

            // Check if user is banned
            if ($authority === 255) {
                $response['message'] = 'Your account is banned and cannot log in.';
                echo json_encode($response);
                exit();
            }

            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['user_id'] = intval($user['idnum']);
            $_SESSION['username'] = $user['mid'];
            $_SESSION['pvalues'] = intval($user['pvalues']);
            $_SESSION['bonus'] = intval($user['bonus']);
            $_SESSION['regdate'] = $user['regdate'];
            $_SESSION['lastlogin'] = $user['lastlogindate'];
            $_SESSION['email'] = $user['mail_adress'];
            $_SESSION['authority'] = $authority;

            // Generate and store admin token for users with authority level 5
            if ($authority === 5) {
                $_SESSION['admin_token'] = bin2hex(random_bytes(16));
            }

            // Fetch player's total gold from player_characters
            $gold_query = "SELECT SUM(gold) as total_gold FROM player_characters WHERE account_id = $1";
            $gold_result = pg_query_params($game_conn, $gold_query, [$_SESSION['user_id']]);
            if ($gold_result && pg_num_rows($gold_result) > 0) {
                $gold_data = pg_fetch_assoc($gold_result);
                $total_gold_value = intval($gold_data['total_gold']);

                // Calculate Gold and Silver
                $_SESSION['gold'] = floor($total_gold_value / 1000);
                $_SESSION['silver'] = $total_gold_value % 1000;
            } else {
                $_SESSION['gold'] = 0;
                $_SESSION['silver'] = 0;
            }
            pg_free_result($gold_result);

            // Fetch achievement_coins (Loyalty Points)
            $achievement_query = "SELECT achievement_coins FROM player_achievement_coins WHERE account_id = $1";
            $achievement_result = pg_query_params($game_conn, $achievement_query, [$_SESSION['user_id']]);
            if ($achievement_result && pg_num_rows($achievement_result) > 0) {
                $achievement_data = pg_fetch_assoc($achievement_result);
                $_SESSION['achievement_coins'] = intval($achievement_data['achievement_coins']);
            } else {
                $_SESSION['achievement_coins'] = 0;
            }
            pg_free_result($achievement_result);

            // Fetch coin amounts
            $coin_types = [
                2 => 'war_coins',
                6 => 'tokens',
                4 => 'valor_coins',
                7 => 'guardian',
                8 => 'arch_token',
                9 => 'ruby_coins',
                10 => 'green_shards',
                11 => 'dragon_point',
                12 => 'fish_token',
                13 => 'cook_token',
                14 => 'duel_coin',
                15 => 'coupon_2',
                16 => 'col_token',
                17 => 'merit_token',
                18 => 'golden_dragon_point',
                19 => 'housing',
                20 => 'eidolon_coin',
                21 => 'rainbow_coin',
                22 => 'dragon_coin',
                23 => 'dragon_shard',
                101 => 'fragment',
            ];

            $coin_amounts = [];
            foreach ($coin_types as $coin_id => $coin_name) {
                $coin_amounts[$coin_name] = 0; // Initialize
            }

            // Fetch coin amounts for each coin_type
            $player_ids = [];
            $player_query = "SELECT id FROM player_characters WHERE account_id = $1";
            $player_result = pg_query_params($game_conn, $player_query, [$_SESSION['user_id']]);
            if ($player_result && pg_num_rows($player_result) > 0) {
                while ($player_row = pg_fetch_assoc($player_result)) {
                    $player_ids[] = intval($player_row['id']);
                }
            }
            pg_free_result($player_result);

            if (!empty($player_ids)) {
                $player_ids_str = implode(',', $player_ids);
                $coins_query = "SELECT coin_type, SUM(amount) as total_amount FROM player_coins WHERE player_id IN ($player_ids_str) GROUP BY coin_type";
                $coins_result = pg_query($game_conn, $coins_query);
                if ($coins_result && pg_num_rows($coins_result) > 0) {
                    while ($coin_row = pg_fetch_assoc($coins_result)) {
                        $coin_type = intval($coin_row['coin_type']);
                        $total_amount = intval($coin_row['total_amount']);
                        if (isset($coin_types[$coin_type])) {
                            $coin_name = $coin_types[$coin_type];
                            $coin_amounts[$coin_name] += $total_amount;
                        }
                    }
                }
                pg_free_result($coins_result);
            }

            $_SESSION['coins'] = $coin_amounts;

            $response['success'] = true;
            $response['message'] = 'Login successful.';
        } else {
            $response['message'] = 'Invalid username or password.';
        }
    } elseif ($action === 'logout') {
        session_unset();
        session_destroy();

        $response['success'] = true;
        $response['message'] = 'Logout successful.';
    } else {
        $response['message'] = 'Invalid action.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
exit();
?>