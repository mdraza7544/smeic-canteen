<?php
declare(strict_types=1);

class AdminController
{
    public function dashboard(): void
    {
        require_role(['admin']);
        $pdo = db();

        $totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE is_deleted=0")->fetchColumn();
        $ordersToday    = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()")->fetchColumn();
        $revenueAllTime = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status IN ('picked_up','ready','preparing','accepted')")->fetchColumn();
        $revenueMonth   = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status IN ('picked_up','ready','preparing','accepted') AND MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetchColumn();
        $pendingOrders  = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();

        // Weekly orders (last 7 days)
        $weeklyLabels = [];
        $weeklyCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $weeklyLabels[] = date('D', strtotime($d));
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=?");
            $stmt->execute([$d]);
            $weeklyCounts[] = (int)$stmt->fetchColumn();
        }

        // Monthly revenue trend (last 6 months)
        $revLabels = [];
        $revValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $revLabels[] = date('M', strtotime($month . '-01'));
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status IN ('picked_up','ready','preparing','accepted') AND DATE_FORMAT(created_at,'%Y-%m')=?");
            $stmt->execute([$month]);
            $revValues[] = (float)$stmt->fetchColumn();
        }

        // Recent transactions
        $recentTx = $pdo->query("SELECT t.*, COALESCE(p.full_name, s.full_name) AS user_name
                                  FROM transactions t
                                  LEFT JOIN parents p ON p.id = t.user_id AND t.user_type='parent'
                                  LEFT JOIN students s ON s.id = t.user_id AND t.user_type='student'
                                  ORDER BY t.created_at DESC LIMIT 10")->fetchAll();

        $pageTitle = 'Admin Dashboard';
        $extraJs = "
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const weeklyCtx = document.getElementById('weeklyChart');
            if (weeklyCtx) {
                new Chart(weeklyCtx, {
                    type: 'bar',
                    data: {
                        labels: " . json_encode($weeklyLabels) . ",
                        datasets: [{ label: 'Orders', data: " . json_encode($weeklyCounts) . ", backgroundColor: '#6366f1', borderRadius: 6 }]
                    },
                    options: { responsive: true, plugins: { legend: { display: false } } }
                });
            }
            const revCtx = document.getElementById('revenueChart');
            if (revCtx) {
                new Chart(revCtx, {
                    type: 'line',
                    data: {
                        labels: " . json_encode($revLabels) . ",
                        datasets: [{ label: 'Revenue (₹)', data: " . json_encode($revValues) . ", borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', tension: 0.3, fill: true }]
                    },
                    options: { responsive: true }
                });
            }
        });
        </script>
        ";
        render_with_layout('admin/dashboard', compact('pageTitle', 'totalStudents', 'ordersToday', 'revenueAllTime', 'revenueMonth', 'pendingOrders', 'recentTx', 'extraJs'));
    }

    public function students(): void
    {
        require_role(['admin']);
        $pdo = db();
        $q = trim($_GET['q'] ?? '');
        if ($q !== '') {
            $stmt = $pdo->prepare("SELECT s.*, p.full_name AS parent_name, p.parent_id AS parent_code, w.balance
                                    FROM students s LEFT JOIN parents p ON p.id = s.parent_id
                                    LEFT JOIN wallets w ON w.student_id = s.id
                                    WHERE s.is_deleted=0 AND (s.full_name LIKE ? OR s.class_name LIKE ? OR s.roll_no LIKE ? OR s.student_id LIKE ?)
                                    ORDER BY s.id DESC");
            $like = "%$q%";
            $stmt->execute([$like, $like, $like, $like]);
        } else {
            $stmt = $pdo->query("SELECT s.*, p.full_name AS parent_name, p.parent_id AS parent_code, w.balance
                                  FROM students s LEFT JOIN parents p ON p.id = s.parent_id
                                  LEFT JOIN wallets w ON w.student_id = s.id
                                  WHERE s.is_deleted=0 ORDER BY s.id DESC");
        }
        $students = $stmt->fetchAll();
        $parents  = $pdo->query("SELECT id, parent_id, full_name FROM parents ORDER BY full_name")->fetchAll();

        // Create / edit form prefill
        $edit = null;
        if (!empty($_GET['edit'])) {
            $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
            $stmt->execute([(int)$_GET['edit']]);
            $edit = $stmt->fetch() ?: null;
        }

        $pageTitle = 'Manage Students';
        render_with_layout('admin/students', compact('pageTitle', 'students', 'parents', 'q', 'edit'));
    }

    public function studentSave(): void
    {
        require_role(['admin']);
        if (!csrf_check()) { flash_set('error', 'Invalid CSRF token.'); redirect(url('index.php?action=admin/students')); }

        $id = (int)($_POST['id'] ?? 0);
        $name    = clean_str($_POST['full_name'] ?? '');
        $class   = clean_str($_POST['class_name'] ?? '');
        $roll    = clean_str($_POST['roll_no'] ?? '');
        $address = clean_str($_POST['address'] ?? '');
        $parent  = (int)($_POST['parent_id'] ?? 0);
        $score   = (int)($_POST['behavioral_score'] ?? 100);

        if ($name === '') {
            flash_set('error', 'Student name is required.');
            redirect(url('index.php?action=admin/students'));
        }

        $pdo = db();
        if ($id > 0) {
            $pdo->prepare("UPDATE students SET full_name=?, class_name=?, roll_no=?, address=?, parent_id=?, behavioral_score=? WHERE id=?")
                ->execute([$name, $class, $roll, $address, $parent ?: null, $score, $id]);
            audit($_SESSION['user_id'], 'student.update', "Updated student id=$id ($name)");
            flash_set('success', 'Student updated.');
        } else {
            $sid = generate_id('S', 'students', 'student_id');
            $pwd = random_password(8);
            $hash = password_hash($pwd, PASSWORD_BCRYPT);
            $pdo->beginTransaction();
            try {
                $pdo->prepare("INSERT INTO students (student_id, full_name, class_name, roll_no, address, parent_id, behavioral_score, password, force_password_change) VALUES (?,?,?,?,?,?,?,?,1)")
                    ->execute([$sid, $name, $class, $roll, $address, $parent ?: null, $score, $hash]);
                $newId = (int)$pdo->lastInsertId();
                $pdo->prepare("INSERT INTO wallets (student_id, balance) VALUES (?, 0)")->execute([$newId]);
                if ($parent) {
                    $pdo->prepare("INSERT INTO student_parent_links (student_id, parent_id, relationship) VALUES (?,?, 'parent')")
                        ->execute([$newId, $parent]);
                }
                $pdo->prepare("INSERT INTO behavioral_scores (student_id, score, reason, updated_by) VALUES (?, ?, 'Initial score', ?)")
                    ->execute([$newId, $score, $_SESSION['user_id']]);
                $pdo->commit();
                audit($_SESSION['user_id'], 'student.create', "Created student $sid ($name). Default password: $pwd");
                flash_set('success', "Student created. ID: $sid. Default password: $pwd");
            } catch (Throwable $e) {
                $pdo->rollBack();
                app_log('student create failed: ' . $e->getMessage());
                flash_set('error', 'Failed to create student.');
            }
        }
        redirect(url('index.php?action=admin/students'));
    }

    public function studentDelete(): void
    {
        require_role(['admin']);
        if (!csrf_check()) { flash_set('error', 'Invalid CSRF token.'); redirect(url('index.php?action=admin/students')); }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            db()->prepare("UPDATE students SET is_deleted=1 WHERE id = ?")->execute([$id]);
            audit($_SESSION['user_id'], 'student.delete', "Soft-deleted student id=$id");
            flash_set('success', 'Student removed.');
        }
        redirect(url('index.php?action=admin/students'));
    }

    public function studentReset(): void
    {
        require_role(['admin']);
        if (!csrf_check()) { flash_set('error', 'Invalid CSRF token.'); redirect(url('index.php?action=admin/students')); }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pwd = random_password(8);
            $hash = password_hash($pwd, PASSWORD_BCRYPT);
            db()->prepare("UPDATE students SET password=?, force_password_change=1 WHERE id=?")->execute([$hash, $id]);
            audit($_SESSION['user_id'], 'student.reset', "Reset password for student id=$id. New pwd: $pwd");
            flash_set('success', "New password for student: $pwd (please share securely)");
        }
        redirect(url('index.php?action=admin/students'));
    }

    public function parents(): void
    {
        require_role(['admin']);
        $pdo = db();
        $q = trim($_GET['q'] ?? '');
        if ($q !== '') {
            $stmt = $pdo->prepare("SELECT p.*, s.full_name AS child_name FROM parents p LEFT JOIN students s ON s.parent_id = p.id AND s.is_deleted=0 WHERE p.full_name LIKE ? OR p.parent_id LIKE ? OR p.email LIKE ? ORDER BY p.id DESC");
            $like = "%$q%"; $stmt->execute([$like, $like, $like]);
        } else {
            $stmt = $pdo->query("SELECT p.*, s.full_name AS child_name FROM parents p LEFT JOIN students s ON s.parent_id = p.id AND s.is_deleted=0 ORDER BY p.id DESC");
        }
        $parents = $stmt->fetchAll();

        $edit = null;
        if (!empty($_GET['edit'])) {
            $stmt = $pdo->prepare("SELECT * FROM parents WHERE id = ?");
            $stmt->execute([(int)$_GET['edit']]);
            $edit = $stmt->fetch() ?: null;
        }

        $pageTitle = 'Manage Parents';
        render_with_layout('admin/parents', compact('pageTitle', 'parents', 'q', 'edit'));
    }

    public function parentSave(): void
    {
        require_role(['admin']);
        if (!csrf_check()) { flash_set('error', 'Invalid CSRF token.'); redirect(url('index.php?action=admin/parents')); }
        $id    = (int)($_POST['id'] ?? 0);
        $name  = clean_str($_POST['full_name'] ?? '');
        $email = clean_str($_POST['email'] ?? '');
        $phone = clean_str($_POST['phone'] ?? '');
        $addr  = clean_str($_POST['address'] ?? '');
        if ($name === '' || $email === '') {
            flash_set('error', 'Name and email are required.');
            redirect(url('index.php?action=admin/parents'));
        }
        $pdo = db();
        if ($id > 0) {
            $pdo->prepare("UPDATE parents SET full_name=?, email=?, phone=?, address=? WHERE id=?")
                ->execute([$name, $email, $phone, $addr, $id]);
            $pdo->prepare("UPDATE users SET email=? WHERE ref_id=? AND role='parent'")->execute([$email, $id]);
            audit($_SESSION['user_id'], 'parent.update', "Updated parent id=$id");
            flash_set('success', 'Parent updated.');
        } else {
            // Check email uniqueness
            $stmt = $pdo->prepare("SELECT id FROM parents WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                flash_set('error', 'Email already in use.');
                redirect(url('index.php?action=admin/parents'));
            }
            $pid = generate_id('P', 'parents', 'parent_id');
            $pwd = random_password(8);
            $hash = password_hash($pwd, PASSWORD_BCRYPT);
            $pdo->beginTransaction();
            try {
                $pdo->prepare("INSERT INTO parents (parent_id, full_name, email, phone, address, password, force_password_change) VALUES (?,?,?,?,?,?,1)")
                    ->execute([$pid, $name, $email, $phone, $addr, $hash]);
                $newId = (int)$pdo->lastInsertId();
                $pdo->prepare("INSERT INTO users (role, ref_id, email, password, force_password_change, is_active) VALUES ('parent',?,?,?,1,1)")
                    ->execute([$newId, $email, $hash]);
                $pdo->commit();
                audit($_SESSION['user_id'], 'parent.create', "Created parent $pid. Default pwd: $pwd");
                flash_set('success', "Parent created. ID: $pid. Default password: $pwd");
            } catch (Throwable $e) {
                $pdo->rollBack();
                app_log('parent create failed: ' . $e->getMessage());
                flash_set('error', 'Failed to create parent.');
            }
        }
        redirect(url('index.php?action=admin/parents'));
    }

    public function topup(): void
    {
        require_role(['admin']);
        $pdo = db();
        $parents  = $pdo->query("SELECT id, parent_id, full_name, wallet_balance FROM parents ORDER BY full_name")->fetchAll();
        $students = $pdo->query("SELECT s.id, s.student_id, s.full_name, w.balance FROM students s LEFT JOIN wallets w ON w.student_id=s.id WHERE s.is_deleted=0 ORDER BY s.full_name")->fetchAll();

        $msg = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) { $msg = 'Invalid CSRF token.'; }
            else {
                $target = $_POST['target'] ?? '';
                $amount = (float)($_POST['amount'] ?? 0);
                $note   = clean_str($_POST['note'] ?? 'Admin top-up');
                if ($amount <= 0) {
                    $msg = 'Amount must be greater than zero.';
                } elseif ($target === 'parent') {
                    $pid = (int)($_POST['parent_id'] ?? 0);
                    if ($pid <= 0) { $msg = 'Select a parent.'; }
                    else {
                        $pdo->beginTransaction();
                        try {
                            $pdo->prepare("UPDATE parents SET wallet_balance = wallet_balance + ? WHERE id = ?")
                                ->execute([$amount, $pid]);
                            $bal = get_parent_balance($pid);
                            $pdo->prepare("INSERT INTO transactions (user_id, user_type, amount, type, reference, balance_after) VALUES (?, 'parent', ?, 'credit', ?, ?)")
                                ->execute([$pid, $amount, $note, $bal]);
                            $pdo->commit();
                            audit($_SESSION['user_id'], 'topup.parent', "Topped up parent $pid by $amount");
                            notify($pid, 'parent', 'Wallet Credited', 'An admin credited ' . money($amount) . ' to your wallet. New balance: ' . money($bal) . '.');
                            $msg = 'Parent wallet topped up. New balance: ' . money($bal);
                        } catch (Throwable $e) {
                            $pdo->rollBack();
                            $msg = 'Top-up failed.';
                            app_log('parent topup: ' . $e->getMessage());
                        }
                    }
                } elseif ($target === 'student') {
                    $sid = (int)($_POST['student_id'] ?? 0);
                    if ($sid <= 0) { $msg = 'Select a student.'; }
                    else {
                        $pdo->beginTransaction();
                        try {
                            $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE student_id = ?")
                                ->execute([$amount, $sid]);
                            $bal = get_student_balance($sid);
                            $pdo->prepare("INSERT INTO transactions (user_id, user_type, amount, type, reference, balance_after) VALUES (?, 'student', ?, 'credit', ?, ?)")
                                ->execute([$sid, $amount, $note, $bal]);
                            $pdo->commit();
                            audit($_SESSION['user_id'], 'topup.student', "Topped up student $sid by $amount");
                            notify($sid, 'student', 'Wallet Credited', 'An admin credited ' . money($amount) . ' to your wallet. New balance: ' . money($bal) . '.');
                            $msg = 'Student wallet topped up. New balance: ' . money($bal);
                        } catch (Throwable $e) {
                            $pdo->rollBack();
                            $msg = 'Top-up failed.';
                            app_log('student topup: ' . $e->getMessage());
                        }
                    }
                } else {
                    $msg = 'Choose a target wallet.';
                }
            }
        }

        $pageTitle = 'Wallet Top-up';
        render_with_layout('admin/topup', compact('pageTitle', 'parents', 'students', 'msg'));
    }

    public function orders(): void
    {
        require_role(['admin']);
        $pdo = db();
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');
        $status = $_GET['status'] ?? '';
        $student = trim($_GET['student'] ?? '');

        $sql = "SELECT o.*, s.full_name AS student_name, s.student_id AS student_code
                FROM orders o JOIN students s ON s.id = o.student_id
                WHERE DATE(o.created_at) BETWEEN ? AND ?";
        $params = [$from, $to];
        if ($status !== '') { $sql .= " AND o.status = ?"; $params[] = $status; }
        if ($student !== '') { $sql .= " AND (s.full_name LIKE ? OR s.student_id LIKE ?)"; $params[] = "%$student%"; $params[] = "%$student%"; }
        $sql .= " ORDER BY o.id DESC LIMIT 200";
        $stmt = $pdo->prepare($sql); $stmt->execute($params);
        $orders = $stmt->fetchAll();

        $pageTitle = 'All Orders';
        render_with_layout('admin/orders', compact('pageTitle', 'orders', 'from', 'to', 'status', 'student'));
    }

    public function transactions(): void
    {
        require_role(['admin']);
        $pdo = db();
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');
        $type = $_GET['type'] ?? '';
        $ut   = $_GET['ut'] ?? '';

        $sql = "SELECT t.*, COALESCE(p.full_name, s.full_name, a.email) AS user_name
                FROM transactions t
                LEFT JOIN parents p ON p.id = t.user_id AND t.user_type='parent'
                LEFT JOIN students s ON s.id = t.user_id AND t.user_type='student'
                LEFT JOIN users a ON a.id = t.user_id AND t.user_type='admin'
                WHERE DATE(t.created_at) BETWEEN ? AND ?";
        $params = [$from, $to];
        if ($type !== '') { $sql .= " AND t.type = ?"; $params[] = $type; }
        if ($ut !== '')   { $sql .= " AND t.user_type = ?"; $params[] = $ut; }
        $sql .= " ORDER BY t.id DESC LIMIT 500";
        $stmt = $pdo->prepare($sql); $stmt->execute($params);
        $tx = $stmt->fetchAll();

        $pageTitle = 'Transactions';
        render_with_layout('admin/transactions', compact('pageTitle', 'tx', 'from', 'to', 'type', 'ut'));
    }

    public function menu(): void
    {
        require_role(['admin']);
        $pdo = db();
        $items = $pdo->query("SELECT * FROM menu_items ORDER BY category, name")->fetchAll();
        $pageTitle = 'Menu (View Only)';
        render_with_layout('admin/menu', compact('pageTitle', 'items'));
    }

    public function reports(): void
    {
        require_role(['admin']);
        $pdo = db();
        $ordersCount = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $revenue     = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status IN ('picked_up','ready','preparing','accepted')")->fetchColumn();
        $txCount     = (int)$pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
        $studentsCount = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE is_deleted=0")->fetchColumn();

        $pageTitle = 'Reports';
        render_with_layout('admin/reports', compact('pageTitle', 'ordersCount', 'revenue', 'txCount', 'studentsCount'));
    }

    public function audit(): void
    {
        require_role(['admin']);
        $pdo = db();
        $logs = $pdo->query("SELECT a.*, u.email AS admin_email FROM audit_logs a LEFT JOIN users u ON u.id = a.admin_id ORDER BY a.id DESC LIMIT 200")->fetchAll();
        $pageTitle = 'Audit Log';
        render_with_layout('admin/audit', compact('pageTitle', 'logs'));
    }

    public function export(): void
    {
        require_role(['admin']);
        $kind = $_GET['kind'] ?? 'orders';
        $fmt  = $_GET['format'] ?? 'csv';
        $pdo  = db();

        $filename = $kind . '_' . date('Ymd_His') . '.' . ($fmt === 'pdf' ? 'pdf' : 'csv');

        if ($kind === 'orders') {
            $rows = $pdo->query("SELECT o.order_number, s.student_id, s.full_name, o.total_amount, o.status, o.created_at FROM orders o JOIN students s ON s.id=o.student_id ORDER BY o.id DESC")->fetchAll();
            $headers = ['Order #', 'Student ID', 'Student', 'Amount', 'Status', 'Created'];
        } elseif ($kind === 'transactions') {
            $rows = $pdo->query("SELECT t.id, t.user_type, t.amount, t.type, t.reference, t.balance_after, t.created_at FROM transactions t ORDER BY t.id DESC")->fetchAll();
            $headers = ['ID', 'User Type', 'Amount', 'Type', 'Reference', 'Balance After', 'Created'];
        } elseif ($kind === 'menu') {
            $rows = $pdo->query("SELECT id, name, category, price, is_available FROM menu_items ORDER BY category, name")->fetchAll();
            $headers = ['ID', 'Name', 'Category', 'Price', 'Available'];
        } else {
            $rows = [];
            $headers = [];
        }

        if ($fmt === 'pdf') {
            $this->exportPdf($filename, ucfirst($kind), $headers, $rows);
            return;
        }
        // CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        foreach ($rows as $r) {
            $line = [];
            foreach ($headers as $h) {
                $idx = array_search($h, array_keys($r));
                // Simpler: take values in order
                $line[] = is_array($r) ? current($r) : $r;
            }
            fputcsv($out, array_values($r));
        }
        fclose($out);
        exit;
    }

    private function exportPdf(string $filename, string $title, array $headers, array $rows): void
    {
        // Build HTML
        $html = '<h1 style="font-family:sans-serif">' . e($title) . ' Report</h1>';
        $html .= '<p style="font-family:sans-serif;color:#555">Generated: ' . date('d M Y, h:i A') . '</p>';
        $html .= '<table border="1" cellspacing="0" cellpadding="6" style="border-collapse:collapse;font-family:sans-serif;font-size:12px;width:100%">';
        $html .= '<thead><tr style="background:#4f46e5;color:#fff">';
        foreach ($headers as $h) $html .= '<th>' . e($h) . '</th>';
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $r) {
            $html .= '<tr>';
            foreach (array_values($r) as $v) $html .= '<td>' . e((string)$v) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        if (class_exists('\\Dompdf\\Dompdf\\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream($filename);
            exit;
        }
        // Fallback: serve as HTML
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . str_replace('.pdf', '.html', $filename));
        echo '<!doctype html><html><head><meta charset="utf-8"><title>' . e($title) . '</title></head><body>' . $html . '<p style="font-family:sans-serif;color:#888;margin-top:1em">PDF generator (dompdf) not installed — showing HTML version. Run <code>composer install</code> to enable PDF.</p></body></html>';
        exit;
    }
}
