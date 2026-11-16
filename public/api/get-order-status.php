<?php
// API: get current order status (for student order-tracking live poll)
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

if (empty($_SESSION['user_id'])) json_response(['ok' => false, 'error' => 'Unauthorized'], 403);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) json_response(['ok' => false, 'error' => 'Bad id'], 400);

$pdo = db();
$role = $_SESSION['role'];

if ($role === 'student') {
    // Student can only see their own order
    $stmt = $pdo->prepare("SELECT id, status, ready_at, pickup_reminder_count, prepared_time_minutes FROM orders WHERE id=? AND student_id=?");
    $stmt->execute([$id, (int)$_SESSION['user_id']]);
} elseif ($role === 'parent') {
    // Parent can see their child's order
    $stmt = $pdo->prepare("SELECT o.id, o.status, o.ready_at, o.pickup_reminder_count, o.prepared_time_minutes
                            FROM orders o
                            JOIN students s ON s.id = o.student_id
                            WHERE o.id=? AND s.parent_id=?");
    $stmt->execute([$id, (int)$_SESSION['user_id']]);
} elseif ($role === 'canteen' || $role === 'admin') {
    $stmt = $pdo->prepare("SELECT id, status, ready_at, pickup_reminder_count, prepared_time_minutes FROM orders WHERE id=?");
    $stmt->execute([$id]);
} else {
    json_response(['ok' => false, 'error' => 'Unauthorized'], 403);
}

$o = $stmt->fetch();
if (!$o) json_response(['ok' => false, 'error' => 'Not found'], 404);
json_response(['ok' => true, 'data' => $o]);
