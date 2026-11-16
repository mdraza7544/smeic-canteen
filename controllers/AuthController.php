<?php
declare(strict_types=1);

class AuthController
{
    public function login(): void
    {
        if (!empty($_SESSION['user_id']) && empty($_SESSION['force_change'])) {
            $this->redirectByRole($_SESSION['role']);
        }

        $role = $_GET['role'] ?? $_POST['role'] ?? '';
        $error = '';
        $identifier = '';
        $needsHashReset = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) { $error = 'Invalid CSRF token. Please reload the page.'; }
            else {
                $role = $_POST['role'] ?? '';
                $identifier = trim((string)($_POST['identifier'] ?? ''));
                $password = (string)($_POST['password'] ?? '');

                if (!in_array($role, ['admin', 'student', 'parent', 'canteen'], true)) {
                    $error = 'Please select a valid role.';
                } elseif ($identifier === '' || $password === '') {
                    $error = 'Please provide both login ID and password.';
                } else {
                    [$ok, $user] = $this->authenticate($role, $identifier, $password);
                    if (!$ok) {
                        $error = 'Invalid login credentials.';
                        // Detect un-seeded hash state and offer the reset script
                        if ($this->isSeedHashState()) {
                            $needsHashReset = true;
                        }
                    } else {
                        // Successful login: regenerate session
                        session_regenerate_id(true);
                        $_SESSION['user_id']  = $user['user_id'];
                        $_SESSION['role']     = $user['role'];
                        $_SESSION['name']     = $user['name'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['force_change'] = $user['force_change'] ?? 0;

                        if ((int)$user['force_change'] === 1) {
                            flash_set('info', 'Please change your temporary password to continue.');
                            redirect(url('index.php?action=change-password'));
                        }
                        flash_set('success', 'Welcome back, ' . $user['name'] . '!');
                        $this->redirectByRole($user['role']);
                    }
                }
            }
        }

        $pageTitle = 'Login';
        require dirname(__DIR__) . '/views/auth/login.php';
    }

    /**
     * Detect the "just imported database.sql" state where the password hashes
     * are placeholders. Used to offer a one-click fix from the login page.
     */
    private function isSeedHashState(): bool
    {
        try {
            $pdo = db();
            // The seed hash from database.sql starts with $2y$10$5nZ1RkjA3L
            $row = $pdo->query("SELECT password FROM users WHERE email='admin@canteen.com' LIMIT 1")->fetch();
            if ($row && str_starts_with((string)$row['password'], '$2y$10$5nZ1RkjA3L')) {
                return true;
            }
        } catch (Throwable $e) {
            // ignore
        }
        return false;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        session_start();
        session_regenerate_id(true);
        flash_set('info', 'You have been logged out.');
        redirect(url('index.php?action=login'));
    }

    public function changePassword(): void
    {
        require_login();
        $error = '';
        $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) { $error = 'Invalid CSRF token.'; }
            else {
                $current = (string)($_POST['current_password'] ?? '');
                $new     = (string)($_POST['new_password'] ?? '');
                $confirm = (string)($_POST['confirm_password'] ?? '');
                if (strlen($new) < 6) {
                    $error = 'New password must be at least 6 characters.';
                } elseif ($new !== $confirm) {
                    $error = 'New passwords do not match.';
                } else {
                    [$ok, $msg, $hashed] = $this->changePasswordForCurrentUser($current, $new);
                    if ($ok) {
                        // Mark force change off
                        $this->markForceChangeOff();
                        $_SESSION['force_change'] = 0;
                        $success = 'Password changed successfully.';
                        flash_set('success', $success);
                        $this->redirectByRole($_SESSION['role']);
                    } else {
                        $error = $msg;
                    }
                }
            }
        }
        $pageTitle = 'Change Password';
        render_with_layout('auth/change-password', [
            'pageTitle' => $pageTitle,
            'error'     => $error,
            'success'   => $success,
            'forced'    => !empty($_SESSION['force_change']),
        ], 'blank');
    }

    public function forgotPassword(): void
    {
        $error = '';
        $sent = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_check()) { $error = 'Invalid CSRF token.'; }
            else {
                $email = trim((string)($_POST['email'] ?? ''));
                if ($email === '') {
                    $error = 'Please provide an email address.';
                } else {
                    $sent = $this->requestReset($email);
                }
            }
        }
        $pageTitle = 'Forgot Password';
        render_with_layout('auth/forgot-password', [
            'pageTitle' => $pageTitle,
            'error'     => $error,
            'sent'      => $sent,
        ], 'blank');
    }

    public function resetPassword(): void
    {
        $token = $_GET['token'] ?? $_POST['token'] ?? '';
        $error = '';
        if ($token === '') {
            $error = 'Invalid reset link.';
        } else {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
            $stmt->execute([$token]);
            $reset = $stmt->fetch();
            if (!$reset) {
                $error = 'This reset link is invalid or has expired.';
            } else {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (!csrf_check()) { $error = 'Invalid CSRF token.'; }
                    else {
                        $new = (string)($_POST['new_password'] ?? '');
                        $confirm = (string)($_POST['confirm_password'] ?? '');
                        if (strlen($new) < 6) {
                            $error = 'New password must be at least 6 characters.';
                        } elseif ($new !== $confirm) {
                            $error = 'Passwords do not match.';
                        } else {
                            $hash = password_hash($new, PASSWORD_BCRYPT);
                            $u = $pdo->prepare("SELECT id, role, ref_id FROM users WHERE email = ?");
                            $u->execute([$reset['email']]);
                            $urow = $u->fetch();
                            if (!$urow) {
                                $error = 'Account not found.';
                            } else {
                                $pdo->prepare("UPDATE users SET password = ?, force_password_change = 0 WHERE id = ?")
                                    ->execute([$hash, $urow['id']]);
                                if ($urow['role'] === 'parent') {
                                    $pdo->prepare("UPDATE parents SET password = ?, force_password_change = 0 WHERE id = ?")
                                        ->execute([$hash, $urow['ref_id']]);
                                } elseif ($urow['role'] === 'canteen') {
                                    $pdo->prepare("UPDATE canteen_staff SET password = ?, force_password_change = 0 WHERE id = ?")
                                        ->execute([$hash, $urow['ref_id']]);
                                }
                                // Invalidate tokens
                                $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$reset['email']]);
                                flash_set('success', 'Password reset successful. Please log in.');
                                redirect(url('index.php?action=login'));
                            }
                        }
                    }
                }
            }
        }
        $pageTitle = 'Reset Password';
        render_with_layout('auth/reset-password', [
            'pageTitle' => $pageTitle,
            'error'     => $error,
            'token'     => $token,
        ], 'blank');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------
    private function authenticate(string $role, string $identifier, string $password): array
    {
        $pdo = db();
        if ($role === 'admin' || $role === 'canteen') {
            $stmt = $pdo->prepare("SELECT u.*, cs.full_name AS cs_name FROM users u LEFT JOIN canteen_staff cs ON cs.id = u.ref_id WHERE u.email = ? AND u.role = ? AND u.is_active = 1");
            $stmt->execute([$identifier, $role]);
            $u = $stmt->fetch();
            if (!$u) return [false, null];
            if (!password_verify($password, $u['password'])) return [false, null];
            $name = $u['cs_name'] ?? ($u['email']);
            return [true, [
                'user_id'  => (int)$u['id'],
                'role'     => $u['role'],
                'name'     => $name,
                'username' => $u['email'],
                'force_change' => (int)$u['force_password_change'],
            ]];
        }
        if ($role === 'parent') {
            // Parent can log in with parent_id (P10001) or email
            $col = preg_match('/^P\d+$/i', $identifier) ? 'parent_id' : 'email';
            $stmt = $pdo->prepare("SELECT * FROM parents WHERE $col = ?");
            $stmt->execute([$identifier]);
            $p = $stmt->fetch();
            if (!$p) return [false, null];
            if (!password_verify($password, $p['password'])) return [false, null];
            return [true, [
                'user_id'  => (int)$p['id'],
                'role'     => 'parent',
                'name'     => $p['full_name'],
                'username' => $p['parent_id'],
                'force_change' => (int)$p['force_password_change'],
            ]];
        }
        if ($role === 'student') {
            $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? AND is_deleted = 0");
            $stmt->execute([$identifier]);
            $s = $stmt->fetch();
            if (!$s) return [false, null];
            if (!password_verify($password, $s['password'])) return [false, null];
            return [true, [
                'user_id'  => (int)$s['id'],
                'role'     => 'student',
                'name'     => $s['full_name'],
                'username' => $s['student_id'],
                'force_change' => (int)$s['force_password_change'],
            ]];
        }
        return [false, null];
    }

    private function changePasswordForCurrentUser(string $current, string $new): array
    {
        $pdo = db();
        $role = $_SESSION['role'] ?? '';
        $uid  = (int)($_SESSION['user_id'] ?? 0);
        if ($role === 'admin' || $role === 'canteen') {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$uid]);
            $u = $stmt->fetch();
            if (!$u || !password_verify($current, $u['password'])) return [false, 'Current password is incorrect.', null];
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET password = ?, force_password_change = 0 WHERE id = ?")->execute([$hash, $uid]);
            if ($role === 'canteen') {
                $pdo->prepare("UPDATE canteen_staff SET password = ?, force_password_change = 0 WHERE id = ?")->execute([$hash, (int)$u['ref_id']]);
            }
            return [true, 'ok', $hash];
        }
        if ($role === 'parent') {
            $stmt = $pdo->prepare("SELECT * FROM parents WHERE id = ?");
            $stmt->execute([$uid]);
            $p = $stmt->fetch();
            if (!$p || !password_verify($current, $p['password'])) return [false, 'Current password is incorrect.', null];
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE parents SET password = ?, force_password_change = 0 WHERE id = ?")->execute([$hash, $uid]);
            $pdo->prepare("UPDATE users SET password = ?, force_password_change = 0 WHERE ref_id = ? AND role = 'parent'")->execute([$hash, $uid]);
            return [true, 'ok', $hash];
        }
        if ($role === 'student') {
            $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
            $stmt->execute([$uid]);
            $s = $stmt->fetch();
            if (!$s || !password_verify($current, $s['password'])) return [false, 'Current password is incorrect.', null];
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE students SET password = ?, force_password_change = 0 WHERE id = ?")->execute([$hash, $uid]);
            return [true, 'ok', $hash];
        }
        return [false, 'Unknown role.', null];
    }

    private function markForceChangeOff(): void
    {
        $pdo = db();
        $role = $_SESSION['role'] ?? '';
        $uid  = (int)($_SESSION['user_id'] ?? 0);
        if ($role === 'admin' || $role === 'canteen') {
            $pdo->prepare("UPDATE users SET force_password_change = 0 WHERE id = ?")->execute([$uid]);
            if ($role === 'canteen') {
                $stmt = $pdo->prepare("SELECT ref_id FROM users WHERE id = ?");
                $stmt->execute([$uid]);
                $r = $stmt->fetch();
                if ($r) $pdo->prepare("UPDATE canteen_staff SET force_password_change = 0 WHERE id = ?")->execute([(int)$r['ref_id']]);
            }
        } elseif ($role === 'parent') {
            $pdo->prepare("UPDATE parents SET force_password_change = 0 WHERE id = ?")->execute([$uid]);
            $pdo->prepare("UPDATE users SET force_password_change = 0 WHERE ref_id = ? AND role = 'parent'")->execute([$uid]);
        } elseif ($role === 'student') {
            $pdo->prepare("UPDATE students SET force_password_change = 0 WHERE id = ?")->execute([$uid]);
        }
    }

    private function requestReset(string $email): bool
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if (!$u) {
            // Don't reveal existence
            return true;
        }
        $token = bin2hex(random_bytes(24));
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)")
            ->execute([$email, $token, $expires]);

        $link = url('index.php?action=reset-password&token=' . $token);
        $body = "Hello,\n\nUse the following link to reset your SMEIC account password. It expires in 1 hour.\n\n$link\n\nIf you did not request this, please ignore this email.";
        // Phpmailer with logging fallback
        $this->sendMail($email, 'SMEIC Password Reset', $body);
        return true;
    }

    private function sendMail(string $to, string $subject, string $body): void
    {
        // Log to file as a stub
        if (!is_dir(LOG_PATH)) { @mkdir(LOG_PATH, 0777, true); }
        $logLine = "[" . date('Y-m-d H:i:s') . "] To: $to | Subject: $subject | Body: $body" . PHP_EOL;
        @file_put_contents(LOG_PATH . 'mail.log', $logLine, FILE_APPEND);
        // Try PHPMailer if available
        if (class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')) {
            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                // $mail->isSMTP(); $mail->Host = 'localhost'; etc.
                // $mail->send();
            } catch (Throwable $e) {
                app_log('mail send failed: ' . $e->getMessage());
            }
        }
    }

    private function redirectByRole(?string $role): void
    {
        switch ($role) {
            case 'admin':   redirect(url('index.php?action=admin/dashboard')); break;
            case 'student': redirect(url('index.php?action=student/dashboard')); break;
            case 'parent':  redirect(url('index.php?action=parent/dashboard')); break;
            case 'canteen': redirect(url('index.php?action=canteen/dashboard')); break;
            default:        redirect(url('index.php?action=login'));
        }
    }
}
