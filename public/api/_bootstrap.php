<?php
// Shared bootstrap for /api endpoints. Loads config, starts session, returns
// JSON for any uncaught errors. Each endpoint should still validate auth/CSRF.
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    require_once dirname(__DIR__, 2) . '/config/constants.php';
    require_once dirname(__DIR__, 2) . '/config/database.php';
    require_once dirname(__DIR__, 2) . '/config/functions.php';
}

// Force JSON output even on uncaught exceptions
ini_set('display_errors', '0');
set_exception_handler(function (Throwable $e) {
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }
    error_log('[api] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode(['ok' => false, 'error' => 'Server error']);
    exit;
});

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
