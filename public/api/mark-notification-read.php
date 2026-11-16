<?php
// API: mark a notification (or all) as read for the current user.
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    json_response(['ok' => false, 'error' => 'Unauthorized'], 403);
}

$in = json_input() ?: $_POST;
$id = isset($in['id']) ? (int)$in['id'] : 0;
$pdo = db();

if ($id > 0) {
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=? AND user_type=?")
        ->execute([$id, (int)$_SESSION['user_id'], $_SESSION['role']]);
} else {
    // Mark all as read
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=? AND user_type=?")
        ->execute([(int)$_SESSION['user_id'], $_SESSION['role']]);
}
json_response(['ok' => true]);
