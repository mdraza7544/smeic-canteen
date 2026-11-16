<?php
// API: parent transfers funds to a child wallet (AJAX form). Controller also has a non-AJAX version.
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

if (($_SESSION['role'] ?? '') !== 'parent') json_response(['ok' => false, 'error' => 'Unauthorized'], 403);
if (!csrf_check()) json_response(['ok' => false, 'error' => 'Invalid CSRF token'], 400);

$in = json_input() ?: $_POST;
$sid    = (int)($in['student_id'] ?? 0);
$amount = (float)($in['amount'] ?? 0);
if ($sid <= 0 || $amount <= 0) json_response(['ok' => false, 'error' => 'Bad input'], 400);

$pdo = db();
$parentId = (int)$_SESSION['user_id'];

$pdo->beginTransaction();
try {
    $child = $pdo->prepare("SELECT id, full_name, parent_id FROM students WHERE id=? AND is_deleted=0");
    $child->execute([$sid]);
    $c = $child->fetch();
    if (!$c || (int)$c['parent_id'] !== $parentId) throw new RuntimeException('Invalid child');

    $pstmt = $pdo->prepare("SELECT wallet_balance FROM parents WHERE id=? FOR UPDATE");
    $pstmt->execute([$parentId]);
    $pBal = (float)$pstmt->fetchColumn();
    if ($pBal < $amount) throw new RuntimeException('Insufficient balance');

    $pdo->prepare("UPDATE parents SET wallet_balance = wallet_balance - ? WHERE id = ?")->execute([$amount, $parentId]);
    $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE student_id = ?")->execute([$amount, $sid]);
    $newPBal = $pBal - $amount;
    $newSBal = get_student_balance($sid);

    $pdo->prepare("INSERT INTO transactions (user_id, user_type, amount, type, reference, balance_after) VALUES (?, 'parent', ?, 'debit', ?, ?)")
        ->execute([$parentId, $amount, 'Transfer to student ' . $c['full_name'], $newPBal]);
    $pdo->prepare("INSERT INTO transactions (user_id, user_type, amount, type, reference, balance_after) VALUES (?, 'student', ?, 'credit', ?, ?)")
        ->execute([$sid, $amount, 'Transfer from parent', $newSBal]);
    notify($sid, 'student', 'Funds Received', 'Your parent transferred ' . money($amount) . '.');
    $pdo->commit();
    json_response(['ok' => true, 'parent_balance' => $newPBal, 'student_balance' => $newSBal]);
} catch (Throwable $e) {
    $pdo->rollBack();
    json_response(['ok' => false, 'error' => $e->getMessage()], 400);
}
