<?php
// API: dispatch pickup reminders for orders ready > 3 minutes.
// Auth required (any logged-in user; harmless if not — dispatcher is idempotent).
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

if (empty($_SESSION['user_id'])) json_response(['ok' => false, 'error' => 'Unauthorized'], 403);

$count = dispatch_pickup_reminders();
json_response(['ok' => true, 'sent' => $count]);
