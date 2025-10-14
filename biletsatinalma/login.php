<?php
require_once 'config.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $stmt = $db->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $msg = "Kullanıcı adı veya şifre yanlış!";
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