<?php
function sanitize($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
function flash($msg) {
    echo "<div class='flash'>" . sanitize($msg) . "</div>";
}

/** CSRF yardımcıları */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Ek yardımcı: firma_admin kontrolü
 */
function is_firma_admin_of($user_role, $user_id, $firma_id, $db) {
    if ($user_role !== 'firma_admin') return false;
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE id=? AND role='firma_admin' AND firma_id=?");
    $stmt->execute([$user_id, $firma_id]);
    return (int)$stmt->fetchColumn() > 0;
}
?>