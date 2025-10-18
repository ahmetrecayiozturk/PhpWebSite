<?php
require_once 'config.php';
require_once 'functions.php';

// DEVELOPMENT DEBUG SETTINGS (REMOVE IN PRODUCTION)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
// hata dosyası proje kökünde php_errors.log olacak
ini_set('error_log', __DIR__ . '/php_errors.log');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Log denemesi (IP + kullanıcı)
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    error_log("LOGIN ATTEMPT: username={$username} ip={$ip} at " . date('c'));

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        error_log("LOGIN_FAIL: user not found for username={$username}");
        $msg = "Kullanıcı adı veya şifre yanlış!";
    } else {
        // Log kullanıcı bilgisi (hassas veriyi kırpmaya dikkat)
        $pw_preview = substr($user['password'] ?? '', 0, 30); // tam şifre/hashi yazdırma, sadece önizleme
        error_log("USER_FOUND: id={$user['id']} username={$user['username']} role={$user['role']} password_preview={$pw_preview}");

        $verified = false;
        // Hash kontrolü
        if (!empty($user['password']) && password_verify($password, $user['password'])) {
            $verified = true;
            error_log("PASSWORD_VERIFY: success for username={$username}");
        } elseif (isset($user['password']) && $password === $user['password']) {
            // Legacy düz metin (sadece dev için)
            $verified = true;
            error_log("PLAIN_PASSWORD_MATCH: legacy plain-text matched for username={$username} (will be hashed)");
            // otomatik olarak hash'leyip güncelle
            try {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$newHash, $user['id']]);
                error_log("PASSWORD_HASHED: updated user id={$user['id']}");
            } catch (Exception $e) {
                error_log("PASSWORD_UPDATE_ERROR for user id={$user['id']}: " . $e->getMessage());
            }
        } else {
            error_log("PASSWORD_CHECK_FAILED for username={$username}");
        }

        if ($verified) {
            // oturum güvenliği
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            error_log("LOGIN_SUCCESS: id={$user['id']} username={$user['username']} role={$user['role']}");
            header("Location: index.php");
            exit;
        } else {
            $msg = "Kullanıcı adı veya şifre yanlış!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Giriş Yap</title></head>
<body>
    <?php if (isset($msg)) flash($msg); ?>
    <form method="post">
        Kullanıcı Adı: <input name="username" required><br>
        Şifre: <input type="password" name="password" required><br>
        <button type="submit">Giriş Yap</button>
    </form>
</body>
</html>