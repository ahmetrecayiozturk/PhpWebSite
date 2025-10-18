<?php
// auth.php - oturum/rol yardımcıları

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_role() {
    return $_SESSION['role'] ?? 'guest';
}

function is_admin() {
    return get_user_role() === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php?msg=' . urlencode('Giriş yapınız'));
        exit;
    }
}

/**
 * require_role
 * - $role string veya array olabilir. Eğer kullanıcı yetkili değilse index.php'ye yönlendirir.
 */
function require_role($role) {
    require_login();
    $userRole = get_user_role();

    if (is_array($role)) {
        $ok = in_array($userRole, $role, true);
    } else {
        $ok = ($userRole === $role);
    }

    if (!$ok) {
        header('Location: index.php?msg=' . urlencode('Yetkiniz yok'));
        exit;
    }
}
?>