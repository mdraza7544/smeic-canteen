<?php
declare(strict_types=1);

class CanteenController
{
    private function loadCurrentStaff(): array
    {
        require_role(['canteen']);
        $pdo = db();
        $stmt = $pdo->prepare("SELECT u.id AS user_id, u.email AS user_email, u.ref_id, cs.*
                                FROM users u
                                JOIN canteen_staff cs ON cs.id = u.ref_id
                                WHERE u.id = ? AND u.role = 'canteen' AND u.is_active = 1
                                LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $staff = $stmt->fetch();
        if (!$staff) {
            $fallback = $pdo->prepare("SELECT * FROM canteen_staff WHERE id = ? LIMIT 1");
            $fallback->execute([(int)($_SESSION['user_id'] ?? 0)]);
            $staff = $fallback->fetch() ?: null;
        }
        if (!$staff) {
            flash_set('error', 'Canteen account not found.');
            redirect(url('index.php?action=login'));
        }
        return $staff;
    }

    private function requireManager(): array
    {
        $staff = $this->loadCurrentStaff();
        if (($staff['role'] ?? 'staff') !== 'manager') {
            flash_set('error', 'Unauthorized access.');
            redirect(url('index.php?action=canteen/profile'));
        }
        return $staff;
    }

    /** Check if current staff member has a specific permission */
    private function requirePermission(string $permission): array
    {
        $staff = $this->loadCurrentStaff();
        if (($staff['role'] ?? 'staff') !== 'manager' && empty($staff[$permission])) {
            flash_set('error', 'You do not have permission to perform this action.');
            redirect(url('index.php?action=canteen/orders'));
        }
        return $staff;
    }

    public function dashboard(): void
    {
        require_role(['canteen']);
        $pdo = db();
        $todayEarnings = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status IN ('picked_up','ready','preparing','accepted') AND DATE(created_at)=CURDATE()")->fetchColumn();
        $todayOrders   = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()")->fetchColumn();
        $todayDone     = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='picked_up' AND DATE(created_at)=CURDATE()")->fetchColumn();
        $pending       = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending','accepted','preparing')")->fetchColumn();

        // Monthly bar
        $months = []; $vals = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = date('Y-m', strtotime("-$i months"));
            $months[] = date('M', strtotime($m . '-01'));
            $s = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status IN ('picked_up','ready','preparing','accepted') AND DATE_FORMAT(created_at,'%Y-%m')=?");
            $s->execute([$m]);
            $vals[] = (float)$s->fetchColumn();
        }

        // Top sellers this week
        $top = $pdo->query("SELECT mi.name, SUM(oi.quantity) AS qty
                            FROM order_items oi
                            JOIN orders o ON o.id=oi.order_id
                            JOIN menu_items mi ON mi.id=oi.menu_item_id
                            WHERE o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                            GROUP BY mi.id ORDER BY qty DESC LIMIT 5")->fetchAll();

        $pageTitle = 'Canteen Dashboard';
        $extraJs = "
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Chart(document.getElementById('monthlyChart'), {
                type: 'bar',
                data: { labels: " . json_encode($months) . ", datasets: [{ label: 'Income (₹)', data: " . json_encode($vals) . ", backgroundColor: '#10b981', borderRadius: 6 }] },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        });
        </script>
        ";
        render_with_layout('canteen/dashboard', compact('pageTitle', 'todayEarnings', 'todayOrders', 'todayDone', 'pending', 'top', 'extraJs'));
    }

    public function orders(): void
    {
        $staff = $this->requirePermission('can_view_pending_orders');
        $pdo = db();
        $status = $_GET['status'] ?? '';
        $sql = "SELECT o.*, s.full_name AS student_name, s.student_id AS student_code
                FROM orders o JOIN students s ON s.id=o.student_id
                WHERE 1=1";
        $params = [];
        if ($status !== '' && in_array($status, ['pending','accepted','preparing','ready','picked_up','cancelled'], true)) {
            $sql .= " AND o.status = ?"; $params[] = $status;
        }
        $sql .= " ORDER BY FIELD(o.status,'pending','accepted','preparing','ready','picked_up','cancelled'), o.id DESC LIMIT 200";
        $stmt = $pdo->prepare($sql); $stmt->execute($params);
        $orders = $stmt->fetchAll();

        // Fetch items for each
        $oid = array_column($orders, 'id');
        $items = [];
        if ($oid) {
            $in = implode(',', array_map('intval', $oid));
            $rows = $pdo->query("SELECT oi.*, mi.name FROM order_items oi JOIN menu_items mi ON mi.id=oi.menu_item_id WHERE oi.order_id IN ($in)")->fetchAll();
            foreach ($rows as $r) $items[$r['order_id']][] = $r;
        }

        $pageTitle = 'Order Management';
        render_with_layout('canteen/orders', compact('pageTitle', 'orders', 'items', 'status'));
    }

    public function acceptOrder(): void
    {
        require_role(['canteen']);
        if (!csrf_check()) { flash_set('error', 'Invalid CSRF token.'); redirect(url('index.php?action=canteen/orders')); }
        $id = (int)($_POST['id'] ?? 0);
        $time = (int)($_POST['prep_time'] ?? 10);
        if ($time < 1) $time = 10;
        $pdo = db();
        $pdo->prepare("UPDATE orders SET status='accepted', prepared_time_minutes=?, updated_at=NOW() WHERE id=? AND status='pending'")
            ->execute([$time, $id]);
        if ($pdo->lastInsertId() || $pdo->query("SELECT ROW_COUNT()")->fetchColumn() > 0 || true) {
            // Notify student
            $s = $pdo->prepare("SELECT student_id, order_number FROM orders WHERE id=?");
            $s->execute([$id]);
            $o = $s->fetch();
            if ($o) notify((int)$o['student_id'], 'student', 'Order Accepted', 'Your order ' . $o['order_number'] . ' has been accepted. Prep time: ' . $time . ' min.');
        }
        flash_set('success', 'Order accepted.');
        redirect(url('index.php?action=canteen/orders'));
    }

    public function markReady(): void
    {
        require_role(['canteen']);
        if (!csrf_check()) { flash_set('error', 'Invalid CSRF token.'); redirect(url('index.php?action=canteen/orders')); }
        $id = (int)($_POST['id'] ?? 0);
        $pdo = db();
        $pdo->prepare("UPDATE orders SET status='ready', ready_at=NOW(), updated_at=NOW() WHERE id=? AND status IN ('accepted','preparing')")
            ->execute([$id]);
        $s = $pdo->prepare("SELECT student_id, order_number FROM orders WHERE id=?");
        $s->execute([$id]);
        $o = $s->fetch();
        if ($o) notify((int)$o['student_id'], 'student', 'Order Ready', 'Your order ' . $o['order_number'] . ' is ready for pickup!');
        flash_set('success', 'Order marked ready.');
        redirect(url('index.php?action=canteen/orders'));
    }

    public function markPickedUp(): void
    {
        $staff = $this->requirePermission('can_complete_orders');
        if (!csrf_check()) { flash_set('error', 'Invalid CSRF token.'); redirect(url('index.php?action=canteen/orders')); }
        $id = (int)($_POST['id'] ?? 0);
        $pdo = db();
        $pdo->prepare("UPDATE orders SET status='picked_up', picked_up_at=NOW(), updated_at=NOW() WHERE id=? AND status='ready'")
            ->execute([$id]);
        $s = $pdo->prepare("SELECT student_id, order_number FROM orders WHERE id=?");
        $s->execute([$id]);
        $o = $s->fetch();
        if ($o) notify((int)$o['student_id'], 'student', 'Order Picked Up', 'Your order ' . $o['order_number'] . ' has been picked up. Enjoy!');
        flash_set('success', 'Order marked picked up.');
        redirect(url('index.php?action=canteen/orders'));
    }

    public function scanPickup(): void
    {
        $staff = $this->requirePermission('can_verify_tokens');
        $msg = ''; $err = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) { $err = 'Invalid CSRF token.'; }
            else {
                // Rate limiting check
                $rateLimit = check_token_rate_limit();
                if (!$rateLimit['allowed']) {
                    $err = 'Too many verification attempts. Please wait 15 minutes before trying again.';
                    app_log('Token verification rate limit exceeded by staff_id=' . ($staff['id'] ?? 'unknown'));
                } else {
                    $token = trim($_POST['token'] ?? '');
                    if (!preg_match('/^\d{6}$/', $token)) {
                        $err = 'Token must be a 6-digit number.';
                        increment_token_rate_limit();
                    } else {
                        $pdo = db();
                        $stmt = $pdo->prepare("SELECT * FROM orders WHERE pickup_token = ?");
                        $stmt->execute([$token]);
                        $o = $stmt->fetch();
                        if (!$o) {
                            $err = 'Invalid token.';
                            increment_token_rate_limit();
                            log_token_verification(0, $token, false, $staff['id'] ?? null);
                        } elseif ($o['status'] === 'picked_up') {
                            $msg = 'Order already picked up.';
                            reset_token_rate_limit();
                        } elseif (is_token_expired($o['token_generated_at'] ?? $o['created_at'])) {
                            $err = 'This token has expired. The student needs a new order.';
                            log_token_verification((int)$o['id'], $token, false, $staff['id'] ?? null);
                        } elseif ($o['status'] !== 'ready') {
                            $err = 'Order is not ready yet (status: ' . $o['status'] . ').';
                            increment_token_rate_limit();
                            log_token_verification((int)$o['id'], $token, false, $staff['id'] ?? null);
                        } else {
                            $pdo->prepare("UPDATE orders SET status='picked_up', picked_up_at=NOW(), updated_at=NOW() WHERE id=?")->execute([$o['id']]);
                            notify((int)$o['student_id'], 'student', 'Order Picked Up', 'Your order ' . $o['order_number'] . ' has been picked up. Enjoy!');
                            $msg = 'Order ' . $o['order_number'] . ' marked as picked up.';
                            reset_token_rate_limit();
                            log_token_verification((int)$o['id'], $token, true, $staff['id'] ?? null);
                            app_log('Token verified successfully: order=' . $o['order_number'] . ' staff_id=' . ($staff['id'] ?? 'unknown'));
                        }
                    }
                }
            }
        }
        $pageTitle = 'Token Verification';
        render_with_layout('canteen/scan', compact('pageTitle', 'msg', 'err'));
    }

    public function staffManagement(): void
    {
        $manager = $this->requireManager();
        $pdo = db();
        $msg = '';
        $err = '';
        $editing = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) {
                $err = 'Invalid CSRF token.';
            } else {
                $action = $_POST['staff_action'] ?? 'save';
                $id = (int)($_POST['id'] ?? 0);
                if ($action === 'delete') {
                    if ($id <= 0) {
                        $err = 'Select a staff member first.';
                    } else {
                        $stmt = $pdo->prepare("SELECT * FROM canteen_staff WHERE id = ?");
                        $stmt->execute([$id]);
                        $row = $stmt->fetch();
                        if (!$row || ($row['role'] ?? '') === 'manager') {
                            $err = 'Manager account cannot be deleted.';
                        } else {
                            $pdo->prepare("DELETE FROM canteen_staff WHERE id = ?")->execute([$id]);
                            $pdo->prepare("DELETE FROM staff_activity_log WHERE staff_id = ?")->execute([$id]);
                            audit((int)$manager['user_id'], 'staff.delete', 'Deleted canteen staff id=' . $id);
                            $msg = 'Staff member deleted.';
                        }
                    }
                } elseif ($action === 'toggle') {
                    $toggleId = (int)($_POST['id'] ?? 0);
                    if ($toggleId <= 0) {
                        $err = 'Select a staff member first.';
                    } else {
                        $stmt = $pdo->prepare("SELECT * FROM canteen_staff WHERE id = ?");
                        $stmt->execute([$toggleId]);
                        $row = $stmt->fetch();
                        if (!$row || ($row['role'] ?? '') === 'manager') {
                            $err = 'Manager account cannot be disabled.';
                        } else {
                            $newState = (int)!((int)$row['is_active']);
                            $pdo->prepare("UPDATE canteen_staff SET is_active = ? WHERE id = ?")->execute([$newState, $toggleId]);
                            $actionName = $newState ? 'Enabled' : 'Disabled';
                            $pdo->prepare("INSERT INTO staff_activity_log (staff_id, action, details) VALUES (?, ?, ?)")
                                ->execute([$toggleId, 'toggle', $actionName . ' staff account']);
                            audit((int)$manager['user_id'], 'staff.toggle', $actionName . ' staff id=' . $toggleId);
                            $msg = 'Staff member ' . strtolower($actionName) . '.';
                        }
                    }
                } else {
                    $name = clean_str($_POST['full_name'] ?? '');
                    $phone = clean_str($_POST['phone'] ?? '');
                    $email = clean_str($_POST['email'] ?? '');
                    $username = clean_str($_POST['username'] ?? '');
                    $password = (string)($_POST['password'] ?? '');
                    $role = 'staff';
                    $canVerify = isset($_POST['can_verify_tokens']) ? 1 : 0;
                    $canComplete = isset($_POST['can_complete_orders']) ? 1 : 0;
                    $canViewPending = isset($_POST['can_view_pending_orders']) ? 1 : 0;

                    if ($name === '' || $email === '' || $username === '') {
                        $err = 'Name, email, and username are required.';
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $err = 'Enter a valid email address.';
                    } else {
                        $count = (int)$pdo->query("SELECT COUNT(*) FROM canteen_staff WHERE role='staff'")->fetchColumn();
                        if ($id <= 0 && $count >= 3) {
                            $err = 'Maximum staff members reached.';
                        } else {
                            $pdo->beginTransaction();
                            try {
                                if ($id > 0) {
                                    $stmt = $pdo->prepare("SELECT * FROM canteen_staff WHERE id = ?");
                                    $stmt->execute([$id]);
                                    $editing = $stmt->fetch();
                                    if (!$editing || ($editing['role'] ?? '') === 'manager') {
                                        throw new RuntimeException('Manager account cannot be modified here.');
                                    }
                                    $pdo->prepare("UPDATE canteen_staff SET full_name=?, phone=?, email=?, username=?, role=?, can_verify_tokens=?, can_complete_orders=?, can_view_pending_orders=? WHERE id=?")
                                        ->execute([$name, $phone, $email, $username, $role, $canVerify, $canComplete, $canViewPending, $id]);
                                    if ($password !== '') {
                                        $hash = password_hash($password, PASSWORD_BCRYPT);
                                        $pdo->prepare("UPDATE canteen_staff SET password=? WHERE id=?")->execute([$hash, $id]);
                                    }
                                    $pdo->prepare("INSERT INTO staff_activity_log (staff_id, action, details) VALUES (?, ?, ?)")
                                        ->execute([$id, 'update', 'Updated staff member']);
                                    audit((int)$manager['user_id'], 'staff.update', 'Updated canteen staff id=' . $id);
                                    $msg = 'Staff member updated.';
                                } else {
                                    $existing = $pdo->prepare("SELECT id FROM canteen_staff WHERE email = ? OR username = ?");
                                    $existing->execute([$email, $username]);
                                    if ($existing->fetch()) {
                                        throw new RuntimeException('Email or username already exists.');
                                    }
                                    $hash = password_hash($password !== '' ? $password : random_password(10), PASSWORD_BCRYPT);
                                    $pdo->prepare("INSERT INTO canteen_staff (full_name, email, phone, username, password, force_password_change, is_active, role, can_verify_tokens, can_complete_orders, can_view_pending_orders) VALUES (?,?,?,?,?,1,1,?,?,?,?)")
                                        ->execute([$name, $email, $phone, $username, $hash, $role, $canVerify, $canComplete, $canViewPending]);
                                    $newId = (int)$pdo->lastInsertId();
                                    $pdo->prepare("INSERT INTO staff_activity_log (staff_id, action, details) VALUES (?, ?, ?)")
                                        ->execute([$newId, 'create', 'Created staff member']);
                                    audit((int)$manager['user_id'], 'staff.create', 'Created canteen staff ' . $username);
                                    $msg = 'Staff member added.';
                                }
                                $pdo->commit();
                            } catch (Throwable $e) {
                                $pdo->rollBack();
                                $err = $e->getMessage();
                            }
                        }
                    }
                }
            }
        }

        $staff = $pdo->query("SELECT * FROM canteen_staff ORDER BY role DESC, id ASC")->fetchAll();
        if (!empty($_GET['edit'])) {
            $stmt = $pdo->prepare("SELECT * FROM canteen_staff WHERE id = ?");
            $stmt->execute([(int)$_GET['edit']]);
            $editing = $stmt->fetch() ?: null;
            if ($editing && ($editing['role'] ?? '') === 'manager') {
                $editing = null;
                $err = 'Manager account cannot be edited here.';
            }
        }

        $pageTitle = 'Staff Management';
        render_with_layout('canteen/staff', compact('pageTitle', 'staff', 'editing', 'msg', 'err', 'manager'));
    }

    public function cancelOrder(): void
    {
        require_role(['canteen']);
        if (!csrf_check()) { flash_set('error', 'Invalid CSRF token.'); redirect(url('index.php?action=canteen/orders')); }
        $id = (int)($_POST['id'] ?? 0);
        $pdo = db();
        // Refund the student
        $pdo->beginTransaction();
        try {
            $s = $pdo->prepare("SELECT * FROM orders WHERE id=? FOR UPDATE");
            $s->execute([$id]);
            $o = $s->fetch();
            if ($o && in_array($o['status'], ['pending','accepted','preparing','ready'], true)) {
                $pdo->prepare("UPDATE orders SET status='cancelled', cancelled_at=NOW(), updated_at=NOW() WHERE id=?")->execute([$id]);
                // Refund to student wallet
                $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE student_id = ?")
                    ->execute([$o['total_amount'], $o['student_id']]);
                $newBal = get_student_balance((int)$o['student_id']);
                $pdo->prepare("INSERT INTO transactions (user_id, user_type, amount, type, reference, balance_after) VALUES (?, 'student', ?, 'credit', ?, ?)")
                    ->execute([$o['student_id'], $o['total_amount'], 'Refund for cancelled order ' . $o['order_number'], $newBal]);
                notify((int)$o['student_id'], 'student', 'Order Cancelled', 'Your order ' . $o['order_number'] . ' was cancelled. Amount refunded to wallet.');
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            app_log('cancel order: ' . $e->getMessage());
        }
        flash_set('success', 'Order cancelled and refund issued.');
        redirect(url('index.php?action=canteen/orders'));
    }

    public function menu(): void
    {
        require_role(['canteen']);
        $pdo = db();
        $items = $pdo->query("SELECT * FROM menu_items ORDER BY category, name")->fetchAll();
        $edit = null;
        if (!empty($_GET['edit'])) {
            $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
            $stmt->execute([(int)$_GET['edit']]);
            $edit = $stmt->fetch() ?: null;
        }
        $pageTitle = 'Menu Management';
        render_with_layout('canteen/menu', compact('pageTitle', 'items', 'edit'));
    }

    public function menuSave(): void
    {
        require_role(['canteen']);
        if (!csrf_check()) { flash_set('error', 'Invalid CSRF token.'); redirect(url('index.php?action=canteen/menu')); }
        $pdo = db();
        $id = (int)($_POST['id'] ?? 0);
        $name = clean_str($_POST['name'] ?? '');
        $desc = clean_str($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $cat = $_POST['category'] ?? 'veg';
        $avail = isset($_POST['is_available']) ? 1 : 0;
        if (!in_array($cat, ['veg','nonveg','snacks','drinks','desserts'], true)) $cat = 'veg';
        if ($name === '' || $price <= 0) {
            flash_set('error', 'Name and positive price are required.');
            redirect(url('index.php?action=canteen/menu'));
        }

        $imageName = $_POST['existing_image'] ?? null;

        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $f = $_FILES['image'];
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
            $mime = mime_content_type($f['tmp_name']);
            if (!isset($allowed[$mime])) { flash_set('error', 'Unsupported image type.'); redirect(url('index.php?action=canteen/menu')); }
            if ($f['size'] > 4 * 1024 * 1024) { flash_set('error', 'Image too large (max 4MB).'); redirect(url('index.php?action=canteen/menu')); }
            $info = getimagesize($f['tmp_name']);
            if (!$info) { flash_set('error', 'Invalid image.'); redirect(url('index.php?action=canteen/menu')); }
            $imageName = uniqid('menu_', true) . '.' . $allowed[$mime];
            if (!is_dir(MENU_UPLOAD_PATH)) { @mkdir(MENU_UPLOAD_PATH, 0777, true); }
            if (!move_uploaded_file($f['tmp_name'], MENU_UPLOAD_PATH . $imageName)) {
                $imageName = $_POST['existing_image'] ?? null;
            }
        }

        if ($id > 0) {
            $pdo->prepare("UPDATE menu_items SET name=?, description=?, price=?, category=?, image=?, is_available=? WHERE id=?")
                ->execute([$name, $desc, $price, $cat, $imageName, $avail, $id]);
            flash_set('success', 'Item updated.');
        } else {
            $pdo->prepare("INSERT INTO menu_items (name, description, price, category, image, is_available) VALUES (?,?,?,?,?,?)")
                ->execute([$name, $desc, $price, $cat, $imageName, $avail]);
            flash_set('success', 'Item created.');
        }
        redirect(url('index.php?action=canteen/menu'));
    }

    public function menuDelete(): void
    {
        require_role(['canteen']);
        if (!csrf_check()) { flash_set('error', 'Invalid CSRF token.'); redirect(url('index.php?action=canteen/menu')); }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            db()->prepare("DELETE FROM menu_items WHERE id=?")->execute([$id]);
            flash_set('success', 'Item deleted.');
        }
        redirect(url('index.php?action=canteen/menu'));
    }

    public function dailySpecial(): void
    {
        require_role(['canteen']);
        $pdo = db();
        $msg = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) { $msg = 'Invalid CSRF token.'; }
            else {
                $mid = (int)($_POST['menu_item_id'] ?? 0);
                $expires = $_POST['expires_at'] ?? date('Y-m-d H:i:s', strtotime('+1 day'));
                if ($mid > 0) {
                    $pdo->prepare("UPDATE daily_specials SET is_active=0 WHERE is_active=1")->execute();
                    $pdo->prepare("INSERT INTO daily_specials (menu_item_id, special_date, expires_at, is_active, created_by) VALUES (?, CURDATE(), ?, 1, ?)")
                        ->execute([$mid, $expires, $_SESSION['user_id']]);
                    $msg = 'Today\'s special set.';
                }
            }
        }
        $items = $pdo->query("SELECT * FROM menu_items WHERE is_available=1 ORDER BY name")->fetchAll();
        $current = get_active_special();
        $pageTitle = 'Daily Specials';
        render_with_layout('canteen/daily-special', compact('pageTitle', 'items', 'current', 'msg'));
    }

    public function earnings(): void
    {
        require_role(['canteen']);
        $pdo = db();
        $today = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status IN ('picked_up','ready','preparing','accepted') AND DATE(created_at)=CURDATE()")->fetchColumn();
        $week = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status IN ('picked_up','ready','preparing','accepted') AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
        $month = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status IN ('picked_up','ready','preparing','accepted') AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetchColumn();
        $rows = $pdo->query("SELECT DATE(created_at) AS d, COUNT(*) AS orders, SUM(total_amount) AS amount FROM orders WHERE status IN ('picked_up','ready','preparing','accepted') GROUP BY DATE(created_at) ORDER BY DATE(created_at) DESC LIMIT 30")->fetchAll();
        $pageTitle = 'Earnings';
        render_with_layout('canteen/earnings', compact('pageTitle', 'today', 'week', 'month', 'rows'));
    }

    public function profile(): void
    {
        require_role(['canteen']);
        $st = $this->loadCurrentStaff();
        $pageTitle = 'Canteen Profile';
        render_with_layout('canteen/profile', compact('pageTitle', 'st'));
    }
}
