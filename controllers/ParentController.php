<?php
declare(strict_types=1);

class ParentController
{
    private function loadParent(): array
    {
        require_role(['parent']);
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM parents WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $p = $stmt->fetch();
        if (!$p) { flash_set('error', 'Parent account not found.'); redirect(url('index.php?action=login')); }
        return $p;
    }

    private function getChildren(int $parentId): array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT s.*, w.balance FROM students s LEFT JOIN wallets w ON w.student_id=s.id WHERE s.parent_id=? AND s.is_deleted=0");
        $stmt->execute([$parentId]);
        return $stmt->fetchAll();
    }

    public function dashboard(): void
    {
        $parent = $this->loadParent();
        $children = $this->getChildren((int)$parent['id']);
        $pdo = db();

        $childOrders = [];
        $spendByCategory = [];
        $scores = [];
        foreach ($children as $c) {
            $s = $pdo->prepare("SELECT * FROM orders WHERE student_id=? ORDER BY id DESC LIMIT 5");
            $s->execute([$c['id']]);
            $childOrders[$c['id']] = $s->fetchAll();
            $scores[] = (int)$c['behavioral_score'];
            $sc = $pdo->prepare("SELECT mi.category, SUM(oi.quantity*oi.price) AS amt FROM order_items oi JOIN orders o ON o.id=oi.order_id JOIN menu_items mi ON mi.id=oi.menu_item_id WHERE o.student_id=? GROUP BY mi.category");
            $sc->execute([$c['id']]);
            foreach ($sc->fetchAll() as $r) {
                $k = $r['category'];
                $spendByCategory[$k] = ($spendByCategory[$k] ?? 0) + (float)$r['amt'];
            }
        }
        $special = get_active_special();

        $pageTitle = 'Parent Dashboard';
        render_with_layout('parent/dashboard', compact('pageTitle', 'parent', 'children', 'childOrders', 'spendByCategory', 'scores', 'special'));
    }

    public function transfer(): void
    {
        $parent = $this->loadParent();
        $children = $this->getChildren((int)$parent['id']);
        $pdo = db();
        $msg = '';
        $err = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) { $err = 'Invalid CSRF token.'; }
            else {
                $sid = (int)($_POST['student_id'] ?? 0);
                $amount = (float)($_POST['amount'] ?? 0);
                if ($amount <= 0) {
                    $err = 'Enter a positive amount.';
                } elseif ($sid <= 0) {
                    $err = 'Select a child.';
                } else {
                    $child = null;
                    foreach ($children as $c) if ((int)$c['id'] === $sid) $child = $c;
                    if (!$child) { $err = 'Invalid child selection.'; }
                    else {
                        $pdo->beginTransaction();
                        try {
                            $pstmt = $pdo->prepare("SELECT wallet_balance FROM parents WHERE id=? FOR UPDATE");
                            $pstmt->execute([$parent['id']]);
                            $pBal = (float)$pstmt->fetchColumn();
                            if ($pBal < $amount) {
                                throw new RuntimeException('Insufficient wallet balance.');
                            }
                            $pdo->prepare("UPDATE parents SET wallet_balance = wallet_balance - ? WHERE id = ?")
                                ->execute([$amount, $parent['id']]);
                            $newPBal = $pBal - $amount;

                            $wstmt = $pdo->prepare("SELECT balance FROM wallets WHERE student_id=? FOR UPDATE");
                            $wstmt->execute([$sid]);
                            $sBal = (float)$wstmt->fetchColumn();
                            $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE student_id = ?")
                                ->execute([$amount, $sid]);
                            $newSBal = $sBal + $amount;

                            $pdo->prepare("INSERT INTO transactions (user_id, user_type, amount, type, reference, balance_after) VALUES (?, 'parent', ?, 'debit', ?, ?)")
                                ->execute([$parent['id'], $amount, 'Transfer to ' . $child['student_id'], $newPBal]);
                            $pdo->prepare("INSERT INTO transactions (user_id, user_type, amount, type, reference, balance_after) VALUES (?, 'student', ?, 'credit', ?, ?)")
                                ->execute([$sid, $amount, 'Transfer from ' . $parent['parent_id'], $newSBal]);

                            notify($sid, 'student', 'Funds Received', 'Your parent transferred ' . money($amount) . ' to your wallet.');

                            $pdo->commit();
                            $msg = 'Transfer successful! New balances: Parent ' . money($newPBal) . ', Child ' . money($newSBal) . '.';
                        } catch (Throwable $e) {
                            $pdo->rollBack();
                            $err = $e->getMessage();
                        }
                    }
                }
            }
        }
        $pageTitle = 'Transfer Funds';
        render_with_layout('parent/transfer', compact('pageTitle', 'parent', 'children', 'msg', 'err'));
    }

    public function menu(): void
    {
        $parent = $this->loadParent();
        $pdo = db();
        $items = $pdo->query("SELECT * FROM menu_items WHERE is_available=1 ORDER BY category, name")->fetchAll();
        $special = get_active_special();
        $pageTitle = 'Browse Menu';
        render_with_layout('parent/menu', compact('pageTitle', 'items', 'special'));
    }

    public function preferences(): void
    {
        $parent = $this->loadParent();
        $pdo = db();
        $msg = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) { $msg = 'Invalid CSRF token.'; $msgType = 'error'; }
            else {
                $mid = (int)($_POST['menu_item_id'] ?? 0);
                $note = clean_str($_POST['note'] ?? '');
                if ($mid > 0) {
                    $pdo->prepare("INSERT INTO parent_preferences (parent_id, menu_item_id, preference_date, note) VALUES (?,?,CURDATE(),?)")
                        ->execute([$parent['id'], $mid, $note]);
                    $msg = 'Suggestion sent to your child for today.';
                }
            }
        }
        $items = $pdo->query("SELECT * FROM menu_items WHERE is_available=1 ORDER BY name")->fetchAll();
        $recent = $pdo->prepare("SELECT pp.*, mi.name FROM parent_preferences pp JOIN menu_items mi ON mi.id=pp.menu_item_id WHERE pp.parent_id=? ORDER BY pp.id DESC LIMIT 20");
        $recent->execute([$parent['id']]);
        $recent = $recent->fetchAll();
        $pageTitle = 'Meal Preferences';
        render_with_layout('parent/preferences', compact('pageTitle', 'items', 'msg', 'recent'));
    }

    public function childOrders(): void
    {
        $parent = $this->loadParent();
        $children = $this->getChildren((int)$parent['id']);
        $pdo = db();

        $childId = (int)($_GET['child_id'] ?? 0);
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');

        $orders = [];
        if ($childId > 0) {
            $belongs = false;
            foreach ($children as $c) if ((int)$c['id'] === $childId) $belongs = true;
            if ($belongs) {
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE student_id=? AND DATE(created_at) BETWEEN ? AND ? ORDER BY id DESC");
                $stmt->execute([$childId, $from, $to]);
                $orders = $stmt->fetchAll();
            }
        }
        $pageTitle = 'Child Orders';
        render_with_layout('parent/child-orders', compact('pageTitle', 'children', 'childId', 'from', 'to', 'orders'));
    }

    public function orderDetail(): void
    {
        $parent = $this->loadParent();
        $pdo = db();
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT o.*, s.full_name AS student_name, s.student_id AS student_code FROM orders o JOIN students s ON s.id=o.student_id WHERE o.id=?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        if (!$order) { flash_set('error','Order not found.'); redirect(url('index.php?action=parent/child-orders')); }
        // Verify child belongs to this parent
        $child = $pdo->prepare("SELECT id FROM students WHERE id=? AND parent_id=?");
        $child->execute([$order['student_id'], $parent['id']]);
        if (!$child->fetch()) { flash_set('error','Unauthorized.'); redirect(url('index.php?action=parent/child-orders')); }
        $items = $pdo->prepare("SELECT oi.*, mi.name FROM order_items oi JOIN menu_items mi ON mi.id=oi.menu_item_id WHERE order_id=?");
        $items->execute([$id]);
        $items = $items->fetchAll();
        $pageTitle = 'Order #' . $order['order_number'];
        render_with_layout('parent/order-detail', compact('pageTitle', 'order', 'items'));
    }

    public function profile(): void
    {
        $parent = $this->loadParent();
        $pageTitle = 'My Profile';
        render_with_layout('parent/profile', compact('pageTitle', 'parent'));
    }

    public function notifications(): void
    {
        $parent = $this->loadParent();
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? AND user_type='parent' ORDER BY id DESC LIMIT 100");
        $stmt->execute([$parent['id']]);
        $notes = $stmt->fetchAll();
        $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=? AND user_type='parent'")->execute([$parent['id']]);
        $pageTitle = 'Notifications';
        render_with_layout('parent/notifications', compact('pageTitle', 'notes'));
    }
}
