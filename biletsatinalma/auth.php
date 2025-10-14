<?php
function is_logged_in() {
    return isset($_SESSION['user_id']);
}
function get_user_role() {
    return $_SESSION['role'] ?? 'guest';
}
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php?msg=Giriş+Yapınız');
        exit;
    }
}
function require_role($role) {
    if (get_user_role() !== $role) {
        header('Location: index.php?msg=Yetki+yok');
        exit;
    }
}
?>