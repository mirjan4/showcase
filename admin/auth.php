<?php
// ========================================================
// Admin Authentication & Session Management
// ========================================================

if (session_status() !== PHP_SESSION_ACTIVE) {
    // Set secure cookie flags
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Check if administrator is logged in.
 */
function is_admin_logged_in(): bool {
    return !empty($_SESSION['admin_id']);
}

/**
 * Require administrator authentication for protected routes.
 */
function require_admin(): void {
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Generate or get existing CSRF token.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output hidden HTML input field with CSRF token.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify CSRF token from POST request.
 */
function verify_csrf(): void {
    $submitted = $_POST['csrf_token'] ?? $_POST['csrf'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    if (empty($submitted) || !hash_equals($sessionToken, $submitted)) {
        http_response_code(403);
        exit('Invalid or expired security token (CSRF). Please refresh the page and try again.');
    }
}

/**
 * Check rate limit for IP address (Lockout disabled for development).
 */
function check_login_rate_limit(PDO $pdo, string $ip): array {
    return [
        'allowed'   => true,
        'remaining' => 999
    ];
}

/**
 * Record a failed login attempt (No-op when strict lockout is disabled).
 */
function record_failed_login(PDO $pdo, string $ip): void {
    // Disabled
}

/**
 * Clear failed login attempts after successful authentication.
 */
function clear_failed_logins(PDO $pdo, string $ip): void {
    try {
        $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
        $stmt->execute([$ip]);
    } catch (Exception $e) {}
}

/**
 * Get client IP address safely.
 */
function get_client_ip(): string {
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}
