<?php
// Front controller / router for SMEIC University Canteen
declare(strict_types=1);

// Ensure no stray notices leak as output
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/logs/php_error.log');

// Load configuration
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

// Composer autoload (if installed). Fall back to a stub if vendor missing.
if (is_file(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// Session guard for force_password_change redirect
if (!empty($_SESSION['user_id']) && !empty($_SESSION['force_change']) && (int)$_SESSION['force_change'] === 1) {
    $action = $_GET['action'] ?? '';
    if (!in_array($action, ['change-password', 'logout', 'login'], true)) {
        redirect(url('index.php?action=change-password'));
    }
}

// Determine action (default: home -> redirect to login or dashboard)
$action = $_GET['action'] ?? 'home';

// Route to controllers
switch ($action) {
    // ----- Auth -----
    case 'login':
        require_once dirname(__DIR__) . '/controllers/AuthController.php';
        (new AuthController())->login();
        break;
    case 'logout':
        require_once dirname(__DIR__) . '/controllers/AuthController.php';
        (new AuthController())->logout();
        break;
    case 'forgot-password':
        require_once dirname(__DIR__) . '/controllers/AuthController.php';
        (new AuthController())->forgotPassword();
        break;
    case 'reset-password':
        require_once dirname(__DIR__) . '/controllers/AuthController.php';
        (new AuthController())->resetPassword();
        break;
    case 'change-password':
        require_once dirname(__DIR__) . '/controllers/AuthController.php';
        (new AuthController())->changePassword();
        break;

    // ----- Admin -----
    case 'admin':
    case 'admin/dashboard':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->dashboard();
        break;
    case 'admin/students':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->students();
        break;
    case 'admin/student-save':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->studentSave();
        break;
    case 'admin/student-delete':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->studentDelete();
        break;
    case 'admin/student-reset':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->studentReset();
        break;
    case 'admin/parents':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->parents();
        break;
    case 'admin/parent-save':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->parentSave();
        break;
    case 'admin/topup':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->topup();
        break;
    case 'admin/orders':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->orders();
        break;
    case 'admin/transactions':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->transactions();
        break;
    case 'admin/menu':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->menu();
        break;
    case 'admin/reports':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->reports();
        break;
    case 'admin/audit':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->audit();
        break;
    case 'admin/export':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->export();
        break;

    // ----- Student -----
    case 'student':
    case 'student/dashboard':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->dashboard();
        break;
    case 'student/menu':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->menu();
        break;
    case 'student/cart':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->cart();
        break;
    case 'student/checkout':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->checkout();
        break;
    case 'student/checkout-confirm':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->checkoutConfirm();
        break;
    case 'student/orders':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->orders();
        break;
    case 'student/order-tracking':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->orderTracking();
        break;
    case 'student/pickup':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->markPickedUp();
        break;
    case 'student/transactions':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->transactions();
        break;
    case 'student/notifications':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->notifications();
        break;
    case 'student/profile':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->profile();
        break;
    case 'student/token':
    case 'student/qr':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->qr();
        break;
    case 'student/feedback':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->feedback();
        break;

    // ----- Parent -----
    case 'parent':
    case 'parent/dashboard':
        require_once dirname(__DIR__) . '/controllers/ParentController.php';
        (new ParentController())->dashboard();
        break;
    case 'parent/transfer':
        require_once dirname(__DIR__) . '/controllers/ParentController.php';
        (new ParentController())->transfer();
        break;
    case 'parent/menu':
        require_once dirname(__DIR__) . '/controllers/ParentController.php';
        (new ParentController())->menu();
        break;
    case 'parent/preferences':
        require_once dirname(__DIR__) . '/controllers/ParentController.php';
        (new ParentController())->preferences();
        break;
    case 'parent/child-orders':
        require_once dirname(__DIR__) . '/controllers/ParentController.php';
        (new ParentController())->childOrders();
        break;
    case 'parent/profile':
        require_once dirname(__DIR__) . '/controllers/ParentController.php';
        (new ParentController())->profile();
        break;
    case 'parent/notifications':
        require_once dirname(__DIR__) . '/controllers/ParentController.php';
        (new ParentController())->notifications();
        break;
    case 'parent/order-detail':
        require_once dirname(__DIR__) . '/controllers/ParentController.php';
        (new ParentController())->orderDetail();
        break;

    case 'student/settings/profile':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->profile();
        break;
    case 'student/settings/password':
        require_once dirname(__DIR__) . '/controllers/AuthController.php';
        (new AuthController())->changePassword();
        break;
    case 'student/settings/token':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->qr();
        break;
    case 'student/settings/transactions':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->transactions();
        break;
    case 'student/settings/track-order':
        require_once dirname(__DIR__) . '/controllers/StudentController.php';
        (new StudentController())->orderTracking();
        break;

    case 'parent/settings/profile':
        require_once dirname(__DIR__) . '/controllers/ParentController.php';
        (new ParentController())->profile();
        break;
    case 'parent/settings/notifications':
        require_once dirname(__DIR__) . '/controllers/ParentController.php';
        (new ParentController())->notifications();
        break;
    case 'parent/settings/password':
        require_once dirname(__DIR__) . '/controllers/AuthController.php';
        (new AuthController())->changePassword();
        break;

    case 'canteen/settings/profile':
        require_once dirname(__DIR__) . '/controllers/CanteenController.php';
        (new CanteenController())->profile();
        break;
    case 'canteen/settings/staff':
        require_once dirname(__DIR__) . '/controllers/CanteenController.php';
        (new CanteenController())->staffManagement();
        break;
    case 'canteen/settings/password':
        require_once dirname(__DIR__) . '/controllers/AuthController.php';
        (new AuthController())->changePassword();
        break;

    case 'admin/settings/topup':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->topup();
        break;
    case 'admin/settings/transactions':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->transactions();
        break;
    case 'admin/settings/reports':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->reports();
        break;
    case 'admin/settings/audit':
        require_once dirname(__DIR__) . '/controllers/AdminController.php';
        (new AdminController())->audit();
        break;
    case 'admin/settings/password':
        require_once dirname(__DIR__) . '/controllers/AuthController.php';
        (new AuthController())->changePassword();
        break;

    // ----- Canteen -----
    case 'canteen':
    case 'canteen/dashboard':
        require_once dirname(__DIR__) . '/controllers/CanteenController.php';
        (new CanteenController())->dashboard();
        break;
    case 'canteen/orders':
        require_once dirname(__DIR__) . '/controllers/CanteenController.php';
        (new CanteenController())->orders();
        break;
    case 'canteen/mark-ready':
        require_once dirname(__DIR__) . '/controllers/CanteenController.php';
        (new CanteenController())->markReady();
        break;
    case 'canteen/mark-pickedup':
        require_once dirname(__DIR__) . '/controllers/CanteenController.php';
        (new CanteenController())->markPickedUp();
        break;
    case 'canteen/scan-pickup':
    case 'canteen/token-verification':
        require_once dirname(__DIR__) . '/controllers/CanteenController.php';
        (new CanteenController())->scanPickup();
        break;
    case 'canteen/accept-order':
        require_once dirname(__DIR__) . '/controllers/CanteenController.php';
        (new CanteenController())->acceptOrder();
        break;
    case 'canteen/cancel-order':
        require_once dirname(__DIR__) . '/controllers/CanteenController.php';
        (new CanteenController())->cancelOrder();
        break;
    case 'canteen/menu':
        require_once dirname(__DIR__) . '/controllers/CanteenController.php';
        (new CanteenController())->menu();
        break;
    case 'canteen/menu-save':
        require_once dirname(__DIR__) . '/controllers/CanteenController.php';
        (new CanteenController())->menuSave();
        break;
    case 'canteen/menu-delete':
        require_once dirname(__DIR__) . '/controllers/CanteenController.php';
        (new CanteenController())->menuDelete();
        break;
    case 'canteen/daily-special':
        require_once dirname(__DIR__) . '/controllers/CanteenController.php';
        (new CanteenController())->dailySpecial();
        break;
    case 'canteen/earnings':
        require_once dirname(__DIR__) . '/controllers/CanteenController.php';
        (new CanteenController())->earnings();
        break;
    case 'canteen/profile':
        require_once dirname(__DIR__) . '/controllers/CanteenController.php';
        (new CanteenController())->profile();
        break;

    // ----- Home -----
    case 'home':
    default:
        if (!empty($_SESSION['user_id'])) {
            $r = $_SESSION['role'] ?? '';
            switch ($r) {
                case 'admin':   redirect(url('index.php?action=admin/dashboard')); break;
                case 'student': redirect(url('index.php?action=student/dashboard')); break;
                case 'parent':  redirect(url('index.php?action=parent/dashboard')); break;
                case 'canteen': redirect(url('index.php?action=canteen/dashboard')); break;
                default:        redirect(url('index.php?action=login'));
            }
        } else {
            view('home', [
                'pageTitle' => APP_NAME,
            ]);
        }
        break;
}
