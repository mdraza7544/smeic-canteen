<?php
// API: check new notifications for the current user.
// Auth required: only the logged-in user can see their own notifications.
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    json_response(['ok' => false, 'error' => 'Unauthorized'], 403);
}

// Ignore any client-supplied IDs; always use session.
$userId   = (int)$_SESSION['user_id'];
$userType = $_SESSION['role'];

if (!in_array($userType, ['student', 'parent', 'canteen', 'admin'], true)) {
    json_response(['ok' => false, 'error' => 'Bad role'], 400);
}

$pdo = db();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND user_type=? AND is_read=0");
$stmt->execute([$userId, $userType]);
$unread = (int)$stmt->fetchColumn();

$latest = $pdo->prepare("SELECT id, title, message, created_at, is_read FROM notifications WHERE user_id=? AND user_type=? ORDER BY id DESC LIMIT 1");
$latest->execute([$userId, $userType]);
$latest = $latest->fetch() ?: null;

json_response(['ok' => true, 'unread' => $unread, 'latest' => $latest]);
