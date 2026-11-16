<?php
// API: update cart (add / change qty / remove)
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

if (($_SESSION['role'] ?? '') !== 'student') {
    json_response(['ok' => false, 'error' => 'Unauthorized'], 403);
}
if (!csrf_check()) json_response(['ok' => false, 'error' => 'Invalid CSRF token'], 400);

// Allow JSON or form-encoded
$in = json_input();
if (!empty($in)) {
    $itemId = (int)($in['item_id'] ?? 0);
    $qty    = (int)($in['quantity'] ?? 1);
} else {
    $itemId = (int)($_POST['item_id'] ?? 0);
    $qty    = (int)($_POST['quantity'] ?? 1);
}

if ($itemId <= 0) json_response(['ok' => false, 'error' => 'Invalid item'], 400);

$pdo = db();
$studentId = (int)$_SESSION['user_id'];

// Check item availability
$st = $pdo->prepare("SELECT id, is_available, name, price FROM menu_items WHERE id = ?");
$st->execute([$itemId]);
$it = $st->fetch();
if (!$it) json_response(['ok' => false, 'error' => 'Item not found'], 404);

if ($qty <= 0) {
    $pdo->prepare("DELETE FROM carts WHERE student_id=? AND menu_item_id=?")
        ->execute([$studentId, $itemId]);
    json_response(['ok' => true, 'removed' => true]);
}

if ((int)$it['is_available'] !== 1) {
    json_response(['ok' => false, 'error' => 'Item currently unavailable'], 400);
}

// Cap quantity at 20 to prevent abuse
if ($qty > 20) $qty = 20;

// Upsert
$pdo->prepare("INSERT INTO carts (student_id, menu_item_id, quantity) VALUES (?,?,?)
                ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), added_at = NOW()")
    ->execute([$studentId, $itemId, $qty]);

// Compute running cart count for badge
$cc = (int)$pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM carts WHERE student_id=?");
$cstmt = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM carts WHERE student_id=?");
$cstmt->execute([$studentId]);
$cartCount = (int)$cstmt->fetchColumn();

json_response(['ok' => true, 'quantity' => $qty, 'cart_count' => $cartCount]);
