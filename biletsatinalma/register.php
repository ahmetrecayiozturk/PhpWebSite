<?php
require_once 'config.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = trim($_POST['email']);
    $role = 'user';
    try {
        $stmt = $db->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, $email, $role]);
        header("Location: login.php?msg=Kaydınız+oluşturuldu");
        exit;
    } catch (Exception $e) {
        $msg = "Kullanıcı adı zaten alınmış!";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Kayıt Ol</title></head>
<body>
    <?php if (isset($msg)) flash($msg); ?>
    <form method="post">
        Kullanıcı Adı: <input name="username" required><br>
        E-posta: <input name="email" required><br>
        Şifre: <input type="password" name="password" required><br>
        <button type="submit">Kaydol</button>
    </form>
</body>
</html>