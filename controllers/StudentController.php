<?php
declare(strict_types=1);

class StudentController
{
    private function loadStudent(): array
    {
        require_role(['student']);
        $pdo = db();
        $stmt = $pdo->prepare("SELECT s.*, w.balance, p.id AS parent_pk, p.parent_id AS parent_code, p.full_name AS parent_name, p.phone AS parent_phone
                                FROM students s
                                LEFT JOIN wallets w ON w.student_id = s.id
                                LEFT JOIN parents p ON p.id = s.parent_id
                                WHERE s.id = ? AND s.is_deleted = 0");
        $stmt->execute([$_SESSION['user_id']]);
        $stu = $stmt->fetch();
        if (!$stu) { flash_set('error', 'Student account not found.'); redirect(url('index.php?action=login')); }
        return $stu;
    }

    public function dashboard(): void
    {
        $stu = $this->loadStudent();
        $pdo = db();

        $balance = get_student_balance((int)$stu['id']);
        $monthSpend = (float)$pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE user_id=? AND user_type='student' AND type='debit' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())");
        $ms = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE user_id=? AND user_type='student' AND type='debit' AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())");
        $ms->execute([$stu['id']]);
        $monthSpend = (float)$ms->fetchColumn();

        $orderCount = (int)$pdo->prepare("SELECT COUNT(*) FROM orders WHERE student_id=?");
        $oc = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE student_id=?");
        $oc->execute([$stu['id']]);
        $orderCount = (int)$oc->fetchColumn();

        // Top 3 items
        $top = $pdo->prepare("SELECT mi.name, SUM(oi.quantity) AS qty
                              FROM order_items oi
                              JOIN orders o ON o.id = oi.order_id
                              JOIN menu_items mi ON mi.id = oi.menu_item_id
                              WHERE o.student_id=?
                              GROUP BY mi.id
                              ORDER BY qty DESC LIMIT 3");
        $top->execute([$stu['id']]);
        $topItems = $top->fetchAll();

        // Current active order
        $cur = $pdo->prepare("SELECT * FROM orders WHERE student_id=? AND status IN ('pending','accepted','preparing','ready') ORDER BY id DESC LIMIT 1");
        $cur->execute([$stu['id']]);
        $currentOrder = $cur->fetch() ?: null;

        // Parent suggestions for today
        $sug = $pdo->prepare("SELECT pp.*, mi.name, mi.image, mi.price FROM parent_preferences pp JOIN menu_items mi ON mi.id=pp.menu_item_id WHERE pp.parent_id=? AND pp.preference_date=CURDATE() AND pp.is_seen=0 ORDER BY pp.id DESC");
        $sug->execute([$stu['parent_pk']]);
        $suggestions = $sug->fetchAll();

        $pageTitle = 'Student Dashboard';
        render_with_layout('student/dashboard', compact('pageTitle', 'stu', 'balance', 'monthSpend', 'orderCount', 'topItems', 'currentOrder', 'suggestions'));
    }

    public function menu(): void
    {
        $stu = $this->loadStudent();
        $pdo = db();

        $cat = $_GET['cat'] ?? '';
        $q   = trim($_GET['q'] ?? '');

        $sql = "SELECT * FROM menu_items WHERE is_available=1";
        $params = [];
        if ($cat !== '' && in_array($cat, ['veg','nonveg','snacks','drinks','desserts'], true)) {
            $sql .= " AND category = ?"; $params[] = $cat;
        }
        if ($q !== '') {
            $sql .= " AND (name LIKE ? OR description LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%";
        }
        $sql .= " ORDER BY category, name";
        $stmt = $pdo->prepare($sql); $stmt->execute($params);
        $items = $stmt->fetchAll();

        $special = get_active_special();

        // Current cart count
        $cc = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM carts WHERE student_id=?");
        $cc->execute([$stu['id']]);
        $cartCount = (int)$cc->fetchColumn();

        $pageTitle = 'Menu';
        render_with_layout('student/menu', compact('pageTitle', 'items', 'cat', 'q', 'special', 'cartCount'));
    }

    public function cart(): void
    {
        $stu = $this->loadStudent();
        $pdo = db();

        $stmt = $pdo->prepare("SELECT c.*, mi.name, mi.price, mi.image, mi.is_available FROM carts c JOIN menu_items mi ON mi.id = c.menu_item_id WHERE c.student_id=? ORDER BY c.added_at DESC");
        $stmt->execute([$stu['id']]);
        $items = $stmt->fetchAll();
        $total = 0;
        foreach ($items as $i) $total += (float)$i['price'] * (int)$i['quantity'];

        $balance = get_student_balance((int)$stu['id']);

        $pageTitle = 'My Cart';
        render_with_layout('student/cart', compact('pageTitle', 'items', 'total', 'balance'));
    }

    public function checkout(): void
    {
        $stu = $this->loadStudent();
        $pdo = db();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // GET — show confirmation page
            $stmt = $pdo->prepare("SELECT c.*, mi.name, mi.price, mi.image, mi.is_available FROM carts c JOIN menu_items mi ON mi.id = c.menu_item_id WHERE c.student_id=? ORDER BY c.added_at DESC");
            $stmt->execute([$stu['id']]);
            $items = $stmt->fetchAll();
            if (empty($items)) { flash_set('error', 'Your cart is empty.'); redirect(url('index.php?action=student/menu')); }
            foreach ($items as $i) {
                if ((int)$i['is_available'] !== 1) {
                    flash_set('error', 'Some items are no longer available. Please review your cart.');
                    redirect(url('index.php?action=student/cart'));
                }
            }
            $total = 0;
            foreach ($items as $i) $total += (float)$i['price'] * (int)$i['quantity'];
            $balance = get_student_balance((int)$stu['id']);
            $pageTitle = 'Confirm Order';
            render_with_layout('student/checkout', compact('pageTitle', 'items', 'total', 'balance', 'stu'));
            return;
        }

        // POST — actually place the order
        if (!csrf_check()) { flash_set('error', 'Invalid CSRF token.'); redirect(url('index.php?action=student/cart')); }

        $stmt = $pdo->prepare("SELECT c.*, mi.price, mi.is_available FROM carts c JOIN menu_items mi ON mi.id = c.menu_item_id WHERE c.student_id=?");
        $stmt->execute([$stu['id']]);
        $cartItems = $stmt->fetchAll();
        if (empty($cartItems)) { flash_set('error', 'Your cart is empty.'); redirect(url('index.php?action=student/menu')); }

        $total = 0;
        foreach ($cartItems as $ci) {
            if ((int)$ci['is_available'] !== 1) {
                flash_set('error', 'Some items are no longer available. Please review your cart.');
                redirect(url('index.php?action=student/cart'));
            }
            $total += (float)$ci['price'] * (int)$ci['quantity'];
        }

        $pdo->beginTransaction();
        try {
            // Lock wallet row
            $wstmt = $pdo->prepare("SELECT balance FROM wallets WHERE student_id=? FOR UPDATE");
            $wstmt->execute([$stu['id']]);
            $bal = (float)$wstmt->fetchColumn();
            if ($bal < $total) {
                throw new RuntimeException('Insufficient wallet balance. Please ask parent to top-up.');
            }
            // Deduct
            $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE student_id = ?")
                ->execute([$total, $stu['id']]);
            $newBal = $bal - $total;
            // Create order
            $orderNo = 'ORD' . date('Ymd') . strtoupper(bin2hex(random_bytes(3)));
            $pickupToken = generate_pickup_token();
            $pdo->prepare("INSERT INTO orders (student_id, order_number, total_amount, status, pickup_token, token_generated_at) VALUES (?,?,?, 'pending', ?, NOW())")
                ->execute([$stu['id'], $orderNo, $total, $pickupToken]);
            $orderId = (int)$pdo->lastInsertId();
            // Items
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?,?,?,?)");
            foreach ($cartItems as $ci) {
                $itemStmt->execute([$orderId, $ci['menu_item_id'], $ci['quantity'], $ci['price']]);
            }
            // Transaction
            $pdo->prepare("INSERT INTO transactions (user_id, user_type, amount, type, reference, balance_after) VALUES (?, 'student', ?, 'debit', ?, ?)")
                ->execute([$stu['id'], $total, 'Order ' . $orderNo, $newBal]);
            // Clear cart
            $pdo->prepare("DELETE FROM carts WHERE student_id=?")->execute([$stu['id']]);
            // Mark suggestions as seen
            if (!empty($stu['parent_pk'])) {
                $pdo->prepare("UPDATE parent_preferences SET is_seen=1 WHERE parent_id=? AND is_seen=0")
                    ->execute([$stu['parent_pk']]);
            }
            // Notify canteen (broadcast to all canteen users)
            $canStmt = $pdo->query("SELECT id FROM users WHERE role='canteen' AND is_active=1");
            foreach ($canStmt->fetchAll() as $c) {
                notify((int)$c['id'], 'canteen', 'New Order', $orderNo . ' placed by ' . $stu['full_name']);
            }
            // Notify parent
            if (!empty($stu['parent_pk'])) {
                notify((int)$stu['parent_pk'], 'parent', 'Child placed an order', $stu['full_name'] . ' placed order ' . $orderNo . ' for ' . money($total) . '.');
            }
            $pdo->commit();
            flash_set('success', 'Order placed successfully!');
            redirect(url('index.php?action=student/order-tracking&id=' . $orderId));
        } catch (Throwable $e) {
            $pdo->rollBack();
            flash_set('error', $e->getMessage());
            redirect(url('index.php?action=student/cart'));
        }
    }

    /**
     * Direct POST entry for the "Place Order" button on the confirmation page.
     * Same as the POST branch of checkout() — kept for backward compatibility.
     */
    public function checkoutConfirm(): void
    {
        $this->checkout();
    }

    public function orders(): void
    {
        $stu = $this->loadStudent();
        $pdo = db();
        $stmt = $pdo->prepare("SELECT o.* FROM orders o WHERE o.student_id=? ORDER BY o.id DESC");
        $stmt->execute([$stu['id']]);
        $orders = $stmt->fetchAll();

        $pageTitle = 'My Orders';
        render_with_layout('student/orders', compact('pageTitle', 'orders'));
    }

    public function orderTracking(): void
    {
        $stu = $this->loadStudent();
        $pdo = db();
        $id = (int)($_GET['id'] ?? 0);
        $order = null;
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id=? AND student_id=?");
            $stmt->execute([$id, $stu['id']]);
            $order = $stmt->fetch() ?: null;
        }
        if (!$order) {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE student_id=? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$stu['id']]);
            $order = $stmt->fetch() ?: null;
        }
        $items = [];
        if ($order) {
            $s = $pdo->prepare("SELECT oi.*, mi.name, mi.image FROM order_items oi JOIN menu_items mi ON mi.id=oi.menu_item_id WHERE order_id=?");
            $s->execute([$order['id']]);
            $items = $s->fetchAll();
        }
        $pageTitle = 'Order Tracking';
        render_with_layout('student/order-tracking', compact('pageTitle', 'order', 'items'));
    }

    public function markPickedUp(): void
    {
        require_role(['student']);
        if (!csrf_check()) { flash_set('error', 'Invalid CSRF token.'); redirect(url('index.php?action=student/orders')); }
        $pdo = db();
        $id = (int)($_POST['order_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id=? AND student_id=?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        $o = $stmt->fetch();
        if (!$o) { flash_set('error', 'Order not found.'); redirect(url('index.php?action=student/orders')); }
        if ($o['status'] !== 'ready') { flash_set('error', 'Order is not ready for pickup.'); redirect(url('index.php?action=student/orders')); }
        $pdo->prepare("UPDATE orders SET status='picked_up', picked_up_at=NOW(), updated_at=NOW() WHERE id=?")
            ->execute([$id]);
        flash_set('success', 'Thanks! Order marked as picked up.');
        redirect(url('index.php?action=student/order-tracking&id=' . $id));
    }

    public function transactions(): void
    {
        $stu = $this->loadStudent();
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id=? AND user_type='student' ORDER BY id DESC LIMIT 200");
        $stmt->execute([$stu['id']]);
        $tx = $stmt->fetchAll();
        $pageTitle = 'My Transactions';
        render_with_layout('student/transactions', compact('pageTitle', 'tx'));
    }

    public function notifications(): void
    {
        $stu = $this->loadStudent();
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? AND user_type='student' ORDER BY id DESC LIMIT 100");
        $stmt->execute([$stu['id']]);
        $notes = $stmt->fetchAll();
        // Mark all as read
        $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=? AND user_type='student'")
            ->execute([$stu['id']]);
        $pageTitle = 'Notifications';
        render_with_layout('student/notifications', compact('pageTitle', 'notes'));
    }

    public function profile(): void
    {
        $stu = $this->loadStudent();
        $pageTitle = 'My Profile';
        render_with_layout('student/profile', compact('pageTitle', 'stu'));
    }

    public function qr(): void
    {
        $stu = $this->loadStudent();
        $type = $_GET['type'] ?? 'id';
        $id   = (int)($_GET['id'] ?? 0);
        $token = '';
        $order = null;
        $tokenExpired = false;
        $remainingTime = '';

        if ($type === 'order') {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id=? AND student_id=?");
            $stmt->execute([$id, $stu['id']]);
            $order = $stmt->fetch();
            if (!$order) { flash_set('error', 'Order not found.'); redirect(url('index.php?action=student/orders')); }
            $token = $order['pickup_token'];
            $tokenExpired = is_token_expired($order['token_generated_at'] ?? $order['created_at']);
            if (!$tokenExpired && $order['status'] === 'ready') {
                $generated = strtotime($order['token_generated_at'] ?? $order['created_at']);
                $expiresAt = $generated + (TOKEN_EXPIRY_HOURS * 3600);
                $remaining = $expiresAt - time();
                $hours = (int)floor($remaining / 3600);
                $minutes = (int)floor(($remaining % 3600) / 60);
                $remainingTime = $hours . 'h ' . $minutes . 'm';
            }
        } else {
            $token = $stu['student_id'];
        }

        $pageTitle = 'My Token';
        render_with_layout('student/token', compact('pageTitle', 'stu', 'type', 'token', 'order', 'id', 'tokenExpired', 'remainingTime'));
    }

    public function feedback(): void
    {
        $stu = $this->loadStudent();
        $pdo = db();
        $msg = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) { $msg = 'Invalid CSRF token.'; }
            else {
                $oid = (int)($_POST['order_id'] ?? 0);
                $rating = (int)($_POST['rating'] ?? 0);
                $comment = clean_str($_POST['comment'] ?? '');
                if ($rating < 1 || $rating > 5) { $msg = 'Rating must be 1-5.'; }
                else {
                    $pdo->prepare("INSERT INTO feedback (student_id, order_id, rating, comment) VALUES (?,?,?,?)")
                        ->execute([$stu['id'], $oid ?: null, $rating, $comment]);
                    $msg = 'Thank you for your feedback!';
                }
            }
        }
        $orders = $pdo->prepare("SELECT id, order_number, created_at FROM orders WHERE student_id=? AND status='picked_up' ORDER BY id DESC LIMIT 20");
        $orders->execute([$stu['id']]);
        $orders = $orders->fetchAll();
        $pageTitle = 'Feedback';
        render_with_layout('student/feedback', compact('pageTitle', 'orders', 'msg'));
    }
}
