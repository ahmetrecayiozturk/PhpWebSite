<?php
require_once 'config.php';
require_once 'auth.php';
require_role('admin');
require_once 'functions.php';

// Firma ekle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_firma'])) {
    $name = trim($_POST['firma_name']);
    if ($name !== '') {
        $stmt = $db->prepare("INSERT INTO firmas (name) VALUES (?)");
        $stmt->execute([$name]);
        header('Location: admin_panel.php');
        exit;
    }
}

// Firma Admin oluşturma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_firma_admin'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email']);
    $firma_id = intval($_POST['firma_id']);
    if ($username && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $db->prepare("INSERT INTO users (username, password, email, role, firma_id) VALUES (?, ?, ?, 'firma_admin', ?)");
            $stmt->execute([$username, $hash, $email, $firma_id]);
            header('Location: admin_panel.php');
            exit;
        } catch (Exception $e) {
            $msg = "Kullanıcı oluşturulamadı: " . $e->getMessage();
        }
    } else {
        $msg = "Username ve password gerekli.";
    }
}

// Firmalar ve firma_admin listesi
$firmalar = $db->query("SELECT * FROM firmas")->fetchAll(PDO::FETCH_ASSOC);
$firma_admins = $db->query("SELECT id, username, email, firma_id FROM users WHERE role='firma_admin'")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><title>Admin Paneli</title></head>
<body>
    <h2>Admin Paneli</h2>
    <?php if (isset($msg)) flash($msg); ?>

    <h3>Firma Ekle</h3>
    <form method="post">
        Firma Adı: <input name="firma_name" required>
        <button type="submit" name="add_firma">Ekle</button>
    </form>

    <h3>Firma Admin Oluştur</h3>
    <form method="post">
        Kullanıcı Adı: <input name="username" required><br>
        Şifre: <input type="password" name="password" required><br>
        E-posta: <input name="email"><br>
        Firma: 
        <select name="firma_id" required>
            <?php foreach ($firmalar as $f): ?>
                <option value="<?= $f['id'] ?>"><?= sanitize($f['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="add_firma_admin">Oluştur</button>
    </form>

    <h3>Firma Adminleri</h3>
    <table border="1">
        <tr><th>ID</th><th>Username</th><th>Email</th><th>Firma</th></tr>
        <?php foreach ($firma_admins as $fa): 
            $firmaName = '';
            foreach ($firmalar as $f) if ($f['id'] == $fa['firma_id']) $firmaName = $f['name'];
        ?>
        <tr>
            <td><?= sanitize($fa['id']) ?></td>
            <td><?= sanitize($fa['username']) ?></td>
            <td><?= sanitize($fa['email']) ?></td>
            <td><?= sanitize($firmaName) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>