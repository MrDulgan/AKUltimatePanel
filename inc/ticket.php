<?php
require_once 'configuration.php';
session_start();

if (!$game_conn || !$member_conn) {
    die("Database connection failed.");
}

$action = $_POST['action'] ?? 'fetch';

if ($action === 'fetch') {
    // Fetch tickets based on authority level
    $authority = $_SESSION['authority'] ?? 0;
    $user_id = $_SESSION['user_id'] ?? 0;

    $ticket_query = "
        SELECT t.id, t.title, t.status, t.reported_player, t.reported_account, t.message, t.created_at, pc.given_name as reporter
        FROM tickets t
        LEFT JOIN player_characters pc ON t.reported_player = pc.id
    ";

    if ($authority == 0) {
        // For regular users, only fetch their tickets
        $ticket_query .= " WHERE t.reporter_id = $1";
        $ticket_result = pg_query_params($game_conn, $ticket_query, [$user_id]);
    } else {
        // For admins, fetch all tickets
        $ticket_result = pg_query($game_conn, $ticket_query);
    }

    if (!$ticket_result) {
        die('Query failed: ' . pg_last_error($game_conn));
    }

    $tickets = pg_fetch_all($ticket_result);
    $ticket_items = '';

    // Generate ticket list items
    if ($tickets) {
        foreach ($tickets as $ticket) {
            $status_class = match (strtolower($ticket['status'])) {
                'open' => 'bg-success',
                'closed' => 'bg-danger',
                'in progress' => 'bg-warning',
                default => 'bg-secondary'
            };

            $ticket_items .= "
            <li class='list-group-item d-flex justify-content-between align-items-center'>
                <span class='badge {$status_class}'>".strtoupper($ticket['status'])."</span>
                <span class='ticket-title'><a href='#' data-ticket-id='{$ticket['id']}'>{$ticket['title']}</a></span>
                <span>{$ticket['created_at']}</span>
            </li>";
        }
    } else {
        $ticket_items = "<li class='list-group-item'>No tickets available.</li>";
    }

    echo $ticket_items;

} elseif ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle new ticket creation
    $title = $_POST['title'];
    $reported_player = $_POST['reported_player'];
    $reported_account = $_POST['reported_account'] ?? null;
    $message = $_POST['message'];
    $image_evidence = $_POST['image_evidence'] ?? null;
    $video_evidence = $_POST['video_evidence'] ?? null;
    $reporter_id = $_SESSION['user_id'];

    $insert_query = "
        INSERT INTO tickets (title, reported_player, reported_account, message, image_evidence, video_evidence, reporter_id, status)
        VALUES ($1, $2, $3, $4, $5, $6, $7, 'open')
    ";

    $result = pg_query_params($game_conn, $insert_query, [
        $title, $reported_player, $reported_account, $message, $image_evidence, $video_evidence, $reporter_id
    ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Ticket created successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating ticket: ' . pg_last_error($game_conn)]);
    }
}

pg_close($game_conn);
pg_close($member_conn);
?>