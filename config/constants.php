<?php
// Application-wide constants. Loaded once by front controller.

// Application name
define('APP_NAME', 'SMEIC - University Canteen');

// Base URL — adjust if running under a sub-path other than /SMEIC/public
define('BASE_URL', 'http://localhost/SMEIC/public');

// File system path to the public directory
define('PUBLIC_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

// Upload path for menu images
define('UPLOAD_PATH', PUBLIC_PATH . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR);
define('MENU_UPLOAD_PATH', UPLOAD_PATH . 'menu' . DIRECTORY_SEPARATOR);
define('MENU_UPLOAD_URL', BASE_URL . '/assets/uploads/menu/');

// Exports / logs
define('EXPORT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR);
define('LOG_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR);

// 3-minute pickup reminder window (in seconds)
define('PICKUP_REMINDER_AFTER', 3 * 60);
define('PICKUP_REMINDER_MAX', 3);

// Low-balance threshold for students
define('LOW_BALANCE_THRESHOLD', 50);

// Session name
define('SESSION_NAME', 'SMEIC_SESSID');
define('SESSION_LIFETIME', 60 * 60 * 4); // 4 hours

// Currency
define('CURRENCY', '₹');

// Token settings
define('TOKEN_LENGTH', 6);
define('TOKEN_EXPIRY_HOURS', 24);

// Rate limiting for token verification
define('TOKEN_VERIFY_MAX_ATTEMPTS', 5);
define('TOKEN_VERIFY_LOCKOUT_SECONDS', 900); // 15 minutes

// Timezone
date_default_timezone_set('Asia/Kolkata');
