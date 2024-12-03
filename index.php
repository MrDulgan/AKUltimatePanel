<?php
require_once 'inc/configuration.php';
require_once 'inc/csrf.php';

ini_set('display_errors', 0);
error_reporting(0);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fetch register date and last login date if not already in session
if (isset($_SESSION['user_id']) && (!isset($_SESSION['regdate']) || !isset($_SESSION['lastlogin']))) {
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT regdate, lastlogindate FROM tb_user WHERE idnum = $1";
    $user_stmt = pg_prepare($member_conn, "fetch_user_dates", $user_query);
    $user_result = pg_execute($member_conn, "fetch_user_dates", [$user_id]);
    if ($user_result && pg_num_rows($user_result) > 0) {
        $user_data = pg_fetch_assoc($user_result);
        $_SESSION['regdate'] = $user_data['regdate'];
        $_SESSION['lastlogin'] = $user_data['lastlogin'];
    }
    pg_free_result($user_result);
}

$current_year = date('Y');
$year_display = (INSTALLATION_YEAR == $current_year) ? INSTALLATION_YEAR : INSTALLATION_YEAR . " - " . $current_year;

$csrf_token = generate_csrf_token();

$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_news_query = "SELECT COUNT(*) FROM public.aku_news";
$total_news_result = pg_query($member_conn, $total_news_query);
$total_news_count = pg_fetch_result($total_news_result, 0, 0);
$total_pages = ceil($total_news_count / $limit);

$news_query = "SELECT id, title, category, created_at FROM public.aku_news ORDER BY created_at DESC LIMIT $1 OFFSET $2";
$news_stmt = pg_prepare($member_conn, "fetch_news", $news_query);
$news_result = pg_execute($member_conn, "fetch_news", [$limit, $offset]);

if (!$news_result) {
    die('Query failed: ' . pg_last_error($member_conn));
}

$news = pg_fetch_all($news_result);
pg_free_result($news_result);

$server_query = "SELECT name, ip, port, online_user, maxnum_user FROM public.worlds";
$server_result = pg_query($account_conn, $server_query);

if (!$server_result) {
    die('Query failed: ' . pg_last_error($account_conn));
}

$servers = pg_fetch_all($server_result);

// Fetch Top Players
$top_players_query = "SELECT pc.id, pc.given_name, pc.level, pw.weapon_type_1, pw.weapon_type_2
                      FROM player_characters pc
                      LEFT JOIN player_weapon_type pw ON pc.id = pw.id
                      ORDER BY pc.level DESC
                      LIMIT 10";
$top_players_result = pg_query($game_conn, $top_players_query);
if (!$top_players_result) {
    die('Query failed: ' . pg_last_error($game_conn));
}
$top_players = pg_fetch_all($top_players_result);
pg_free_result($top_players_result);

// Fetch Top Guilds
$top_guilds_query = "SELECT id, name, lv FROM family ORDER BY lv DESC LIMIT 10";
$top_guilds_result = pg_query($game_conn, $top_guilds_query);
if (!$top_guilds_result) {
    die('Query failed: ' . pg_last_error($game_conn));
}
$top_guilds = pg_fetch_all($top_guilds_result);
pg_free_result($top_guilds_result);

function checkServerStatus($ip, $port, $timeout = 1)
{
    $status = 'offline';
    $fp = @fsockopen($ip, $port, $errno, $errstr, $timeout);

    if ($fp) {
        fclose($fp);
        $status = 'online';
    }

    return $status;
}

function format_class_name($class_name) {
    $formatted_name = str_replace('_', ' ', $class_name);
    $formatted_name = ucwords($formatted_name);
    return $formatted_name;
}

$class_map = [
    1 => 'duelist',
    2 => 'guard',
    4 => 'ravager',
    8 => 'wizard',
    16 => 'gunslinger',
    32 => 'grenadier',
    64 => 'sorcerer',
    128 => 'bard',
    226 => 'brawler',
    512 => 'ranger',
    1024 => 'ronin',
    2048 => 'reaper',
    4096 => 'holy_sword',
    8192 => 'shinobi',
    16384 => 'lancer',
    32768 => 'guitar',
    65536 => 'star_caller',
    131072 => 'whipmaster',
];

pg_free_result($server_result);
pg_close($account_conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(SITE_TITLE); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars(META_DESCRIPTION); ?>">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/all.min.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token); ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light">
    <div class="header-container d-flex justify-content-between align-items-center">
        <ul class="navbar-nav d-flex align-items-center">
            <li class="nav-item mx-4"><button id="homeButton" class="nav-link btn btn-link">Home</button></li>
            <li class="nav-item mx-4"><button id="registerButton" class="nav-link btn btn-link">Registration</button></li>
            <li class="nav-item mx-4"><button id="forumButton" class="nav-link btn btn-link">Forum</button></li>
        </ul>

        <button id="logoButton" class="navbar-brand mx-4 btn btn-link">
            <img src="../img/logo.png" alt="Logo" class="logo-img">
        </button>

        <ul class="navbar-nav d-flex align-items-center">
            <li class="nav-item mx-4"><button id="topPlayersButton" class="nav-link btn btn-link">Top Players</button></li>
            <li class="nav-item mx-4"><button id="topGuildsButton" class="nav-link btn btn-link">Top Guilds</button></li>
            <li class="nav-item mx-4"><button id="rulesButton" class="nav-link btn btn-link">Rules</button></li>
        </ul>
    </div>
</nav>

<script>
    var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>

<div class="container my-4 ak-main-card">
    <div class="container mb-5">
        <div class="row equal-height">
            <div class="col-md-8">
                <div id="carouselExample" class="carousel slide h-100" data-bs-ride="carousel">
                    <div class="carousel-inner h-100">
                        <div class="carousel-item active h-100">
                            <img src="../img/banner.jpg" class="d-block w-100 h-100" alt="Slide 1">
                        </div>
                        <div class="carousel-item h-100">
                            <img src="../img/banner.jpg" class="d-block w-100 h-100" alt="Slide 2">
                        </div>
                        <div class="carousel-item h-100">
                            <img src="../img/banner.jpg" class="d-block w-100 h-100" alt="Slide 3">
                        </div>
                    </div>

                    <a class="carousel-control-prev custom-arrow" href="#carouselExample" role="button" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </a>

                    <a class="carousel-control-next custom-arrow" href="#carouselExample" role="button" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="login-box h-100">
                    <div class="login-box-body d-flex flex-column justify-content-center">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <h5 class="ak-card-title">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h5>
                            <div class="icon-row">
                                <a href="profile.php" class="icon-link" data-bs-toggle="tooltip" data-bs-placement="top" title="Profile">
                                    <i class="fas fa-user"></i>
                                </a>
                                <a href="settings.php" class="icon-link" data-bs-toggle="tooltip" data-bs-placement="top" title="Settings">
                                    <i class="fas fa-cog"></i>
                                </a>
                                <a href="item_mall.php" class="icon-link" data-bs-toggle="tooltip" data-bs-placement="top" title="Item Mall">
                                    <i class="fas fa-shopping-cart"></i>
                                </a>
                                <a href="ticket.php" class="icon-link" data-bs-toggle="tooltip" data-bs-placement="top" title="Ticket">
                                    <i class="fas fa-ticket-alt"></i>
                                </a>
                                <?php if (isset($_SESSION['authority']) && $_SESSION['authority'] >= 5): ?>
                                    <a href="admin_panel.php" class="icon-link" data-bs-toggle="tooltip" data-bs-placement="top" title="Admin Panel">
                                        <i class="fas fa-user-shield"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="#" id="logoutButton" class="icon-link" data-bs-toggle="tooltip" data-bs-placement="top" title="Logout">
                                    <i class="fas fa-sign-out-alt"></i>
                                </a>
                            </div>

                            <!-- Wallet Section -->
                            <div class="wallet-section">
                                <h6 class="wallet-title">Wallet</h6>
                                <!-- Points Icons with Separator -->
                                <div class="wallet-icons">
                                    <?php
                                    $wallet_items = [
                                        ['img' => 'ap.png', 'title' => 'Points', 'amount' => intval($_SESSION['pvalues'])],
                                        ['img' => 'bp.png', 'title' => 'Bonus Points', 'amount' => intval($_SESSION['bonus'])],
                                        ['img' => 'lp.png', 'title' => 'Loyalty Points', 'amount' => isset($_SESSION['achievement_coins']) ? intval($_SESSION['achievement_coins']) : 0],
                                        ['img' => 'gold.png', 'title' => 'Gold', 'amount' => intval($_SESSION['gold'])],
                                        ['img' => 'silver.png', 'title' => 'Silver', 'amount' => intval($_SESSION['silver'])],
                                        ['img' => 'fp.png', 'title' => 'Fragments', 'amount' => $_SESSION['coins']['fragment'] ?? 0],
                                        ['img' => 'gf.png', 'title' => 'Guild Funds', 'amount' => $_SESSION['coins']['guild_funds'] ?? 0],
                                        ['img' => 'to.png', 'title' => 'Tokens', 'amount' => $_SESSION['coins']['tokens'] ?? 0],
                                        ['img' => 'dp.png', 'title' => 'Dragon Points', 'amount' => $_SESSION['coins']['dragon_point'] ?? 0],
                                        ['img' => 'gdp.png', 'title' => 'Golden Dragon Points', 'amount' => $_SESSION['coins']['golden_dragon_point'] ?? 0],
                                        ['img' => 'wc.png', 'title' => 'War Coins', 'amount' => $_SESSION['coins']['war_coins'] ?? 0],
                                        ['img' => 'vc.png', 'title' => 'Valor Coins', 'amount' => $_SESSION['coins']['valor_coins'] ?? 0],
                                        ['img' => 'du.png', 'title' => 'Duel Coins', 'amount' => $_SESSION['coins']['duel_coin'] ?? 0],
                                        ['img' => 'gu.png', 'title' => 'Guardian Medals', 'amount' => $_SESSION['coins']['guardian'] ?? 0],
                                        ['img' => 'me.png', 'title' => 'Merit Token', 'amount' => $_SESSION['coins']['merit_token'] ?? 0],
                                        ['img' => 'ei.png', 'title' => 'Eidolon Coin', 'amount' => $_SESSION['coins']['eidolon_coin'] ?? 0],
                                        ['img' => 'ft.png', 'title' => 'Fish Token', 'amount' => $_SESSION['coins']['fish_token'] ?? 0],
                                        ['img' => 'at.png', 'title' => 'Archeology Token', 'amount' => $_SESSION['coins']['arch_token'] ?? 0],
                                        ['img' => 'cot.png', 'title' => 'Cook Token', 'amount' => $_SESSION['coins']['cook_token'] ?? 0],
                                        ['img' => 'ct.png', 'title' => 'Collector Token', 'amount' => $_SESSION['coins']['col_token'] ?? 0],
                                        ['img' => 'vo.png', 'title' => 'Vouchers', 'amount' => $_SESSION['coins']['coupon_2'] ?? 0],
                                        ['img' => 'gs.png', 'title' => 'Green Shards', 'amount' => $_SESSION['coins']['green_shards'] ?? 0],
                                        ['img' => 'dc.png', 'title' => 'Dragon Coin', 'amount' => $_SESSION['coins']['dragon_coin'] ?? 0],
                                        ['img' => 'rc.png', 'title' => 'Ruby Coins', 'amount' => $_SESSION['coins']['ruby_coins'] ?? 0],
                                    ];

                                    $icons_per_row = 6;
                                    $count = 0;
                                    echo '<div class="row">';
                                    foreach ($wallet_items as $item) {
                                        $count++;
                                        echo '<div class="col-2 text-center">';
                                        echo '<div class="points-item">';
                                        echo '<img src="../img/wallet/' . $item['img'] . '" alt="' . $item['title'] . ' Icon" data-bs-toggle="tooltip" data-bs-placement="top" title="' . $item['amount'] . ' ' . $item['title'] . '">';
                                        echo '</div>';
                                        echo '</div>';
                                        if ($count % $icons_per_row == 0 && $count != count($wallet_items)) {
                                            echo '</div><div class="row">';
                                        }
                                    }
                                    echo '</div>';
                                    ?>
                                </div>
                            </div>

                            <!-- Information Section -->
                            <div class="information-section">
                                <h6 class="information-title">Information</h6>
                                <div class="info-details">
                                    <p>Register Date: <?php echo date('d/m/Y', strtotime($_SESSION['regdate'])); ?></p>
                                    <p>Last Login Date: <?php echo date('d/m/Y', strtotime($_SESSION['lastlogin'])); ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <h5 class="ak-card-title">Account Panel</h5>
                            <div id="loginMessageContainer"></div>
                            <form id="loginForm">
                                <div class="ak-input-group mb-3">
                                    <input type="text" class="form-control ak-input" id="login_username" placeholder="Username" required>
                                </div>
                                <div class="ak-input-group mb-3">
                                    <input type="password" class="form-control ak-input" id="login_password" placeholder="Password" required>
                                </div>
                                <button type="submit" class="ak-button mx-auto">Login</button>
                            </form>
                            <a href="#" class="d-block mt-2 text-center ak-link">Forgot your ID or password?</a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container text-center my-4">
        <div class="row">
            <div class="col-md-4">
                <button class="btn fast-link donation-link w-100">
                    Donation
                    <span class="fast-link-text">Help in the development of the server</span>
                </button>
            </div>

            <div class="col-md-4">
                <button class="btn fast-link start-game-link w-100">
                    Download
                    <span class="fast-link-text">Start the game in a few clicks</span>
                </button>
            </div>

            <div class="col-md-4">
                <button class="btn fast-link statistics-link w-100">
                    Statistics
                    <span class="fast-link-text">Detailed statistics of the game server</span>
                </button>
            </div>
        </div>
    </div>

    <div class="container page-container my-4">
        <div class="row">
            <div class="col-md-8 mt-4">
                <div class="page-title d-flex align-items-center">
                    <span>Last News</span>

                    <?php if (isset($_SESSION['authority']) && $_SESSION['authority'] >= 5): ?>
                        <button id="add-news-button" class="add-news-button ms-3" data-bs-toggle="tooltip" data-bs-placement="top" title="Add News">
                            <img src="../img/plus.png" alt="Add News" class="last-news-icon">
                        </button>
                    <?php endif; ?>
                </div>
                <div class="page-content">
                    <ul class="list-group news-list">
                        <?php if ($news): ?>
                            <?php foreach ($news as $news_item):
                                $badge_class = '';
                                switch (strtolower($news_item['category'])) {
                                    case 'update':
                                        $badge_class = 'bg-success';
                                        break;
                                    case 'important':
                                        $badge_class = 'bg-danger';
                                        break;
                                    case 'event':
                                        $badge_class = 'bg-primary';
                                        break;
                                    default:
                                        $badge_class = 'bg-secondary';
                                        break;
                                }
                            ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="news-tag-container">
                                        <span class="badge <?php echo $badge_class; ?> tag-width"><?php echo strtoupper($news_item['category']); ?></span>
                                        <a href="#" class="news-item-link" data-news-id="<?php echo $news_item['id']; ?>">【<?php echo htmlspecialchars($news_item['title']); ?>】</a>
                                    </span>
                                    <span><?php echo date('d/m/Y', strtotime($news_item['created_at'])); ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item">No news available at the moment.</li>
                        <?php endif; ?>
                    </ul>

                    <nav aria-label="Page navigation" class="mt-3">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <button class="page-link" data-page="<?php echo $page - 1; ?>">&#8249;</button>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <button class="page-link" data-page="<?php echo $i; ?>"><?php echo $i; ?></button>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <button class="page-link" data-page="<?php echo $page + 1; ?>">&#8250;</button>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>

            <div class="col-md-4">
                <div class="top-servers">
                    <h5 class="tab-header">Servers</h5>

                    <?php if ($servers): ?>
                        <?php foreach ($servers as $server):
                            $status = checkServerStatus($server['ip'], $server['port']);
                            $status_class = $status === 'online' ? 'online' : 'offline';
                            $status_icon = $status === 'online' ? 'online.png' : 'offline.png';
                            $online_users = $status === 'online' ? $server['online_user'] : 0;
                            $max_users = $server['maxnum_user'];
                            $percentage_filled = ($online_users / $max_users) * 100;
                        ?>
                            <div class="server-item">
                                <h6><?php echo htmlspecialchars($server['name']); ?></h6>
                                <div class="progress-container">
                                    <div class="progress-bar">
                                        <img src="../img/progress-bar.png" alt="Progress" class="progress-bar-img">
                                        <div class="fill" style="width: <?php echo round($percentage_filled); ?>%;"></div>
                                    </div>
                                    <span class="status-circle">
                                        <img src="../img/<?php echo $status_icon; ?>" alt="<?php echo ucfirst($status); ?>" class="status-icon">
                                    </span>
                                </div>
                                <?php if ($status === 'online'): ?>
                                    <p class="status-text <?php echo $status_class; ?>">
                                        Active Players: <?php echo $online_users; ?>
                                    </p>
                                <?php else: ?>
                                    <p class="status-text <?php echo $status_class; ?>">
                                        Server is offline
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No servers available at the moment.</p>
                    <?php endif; ?>
                </div>

<!-- Top Players and Guilds Section -->
<div class="top-section mt-4">
    <!-- Header -->
    <h5 class="tab-header" id="dynamicTabHeader">Top Players</h5>

    <!-- Tab Content -->
    <div class="tab-content" id="topTabsContent">
        <!-- Top Players Table -->
        <div class="tab-pane fade show active" id="players" role="tabpanel" aria-labelledby="players-tab">
            <div class="table-responsive no-scroll">
                <table class="table top-players-table">
                    <thead>
                        <tr class="aku-color">
                            <th>#</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Level</th>
                            <th title="Achievement Points">Ach.</th> <!-- Added Tooltip -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($top_players): ?>
                            <?php foreach ($top_players as $index => $player):
                                $class_key = $player['weapon_type_1'];
                                $class_icon = '../img/classes/' . $class_map[$class_key] . '.png';
                                $class_name = $class_map[$class_key];
                                $class_display_name = format_class_name($class_name);
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($player['given_name']); ?></td>
                                <td><img src="<?php echo $class_icon; ?>" alt="<?php echo $class_display_name; ?>" class="class-icon" data-bs-toggle="tooltip" title="<?php echo $class_display_name; ?>"></td>
                                <td><?php echo $player['level']; ?></td>
                                <td><?php echo $player['achievements'] ?? 0; ?></td> <!-- Achievements column -->
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No players available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Guilds Table -->
        <div class="tab-pane fade" id="guilds" role="tabpanel" aria-labelledby="guilds-tab">
            <div class="table-responsive">
                <table class="table top-guilds-table">
                    <thead>
                        <tr class="aku-color">
                            <th>#</th>
                            <th>Name</th>
                            <th>Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($top_guilds): ?>
                            <?php foreach ($top_guilds as $index => $guild): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($guild['name']); ?></td>
                                <td><?php echo $guild['lv']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No guilds available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabs Below the Table -->
    <ul class="nav nav-tabs justify-content-center mt-3" id="topTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="players-tab" data-bs-toggle="tab" data-bs-target="#players" type="button" role="tab" aria-controls="players" aria-selected="true">Players</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="guilds-tab" data-bs-toggle="tab" data-bs-target="#guilds" type="button" role="tab" aria-controls="guilds" aria-selected="false">Guilds</button>
        </li>
    </ul>
</div>
<!-- End of Top Players and Guilds Section -->
            </div>
        </div>
    </div>
</div>

<footer class="footer bg-light py-4">
    <div class="container text-center">
        <p>&copy; <?php echo $year_display . " " . SITE_TITLE; ?>. All rights reserved.</p>
    </div>
</footer>

<!-- Bootstrap JS and your custom JS -->
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/akultimate.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tabHeader = document.getElementById('dynamicTabHeader');
    var tabs = document.querySelectorAll('#topTabs button[data-bs-toggle="tab"]');

    tabs.forEach(function (tab) {
        tab.addEventListener('shown.bs.tab', function (event) {
            var targetId = event.target.getAttribute('aria-controls');
            if (targetId === 'players') {
                tabHeader.textContent = 'Top Players';
            } else if (targetId === 'guilds') {
                tabHeader.textContent = 'Top Guilds';
            }
        });
    });
});
</script>
</body>
</html>