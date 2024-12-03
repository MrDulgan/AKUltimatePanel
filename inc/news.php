<?php
require_once 'configuration.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $news_id = (int) $_GET['id'];
    
    $news_query = "SELECT title, content, created_at FROM public.aku_news WHERE id = $1";
    $news_stmt = pg_prepare($member_conn, "fetch_news_detail", $news_query);
    $result = pg_execute($member_conn, "fetch_news_detail", [$news_id]);

    if ($result && pg_num_rows($result) > 0) {
        $news = pg_fetch_assoc($result);
        $response = [
            'success' => true,
            'title' => $news['title'],
            'content' => $news['content'],
            'created_at' => date('d/m/Y', strtotime($news['created_at']))
        ];
    } else {
        $response = ['success' => false, 'error' => 'News not found.'];
    }

    pg_free_result($result);
} else {
    $response = ['success' => false, 'error' => 'Invalid news ID.'];
}

echo json_encode($response);

pg_close($member_conn);
?>