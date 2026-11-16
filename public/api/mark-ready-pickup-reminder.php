<?php
// API: mark a specific order's pickup reminder triggered manually.
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

if (empty($_SESSION['user_id'])) json_response(['ok' => false, 'error' => 'Unauthorized'], 403);

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) json_response(['ok' => false, 'error' => 'Bad id'], 400);

$ok = send_pickup_reminder($id);
json_response(['ok' => $ok]);
