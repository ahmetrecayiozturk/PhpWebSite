<?php
require_once 'config.php';
require_once 'auth.php';
require_role('admin');
require_once 'functions.php';

// Ekle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $kod = trim($_POST['kod']);
    $oran = intval($_POST['oran']);
    $kullanim = $_POST['kullanim_limiti'] === '' ? null : intval($_POST['kullanim_limiti']);
    $son = $_POST['son_kullanma'] === '' ? null : $_POST['son_kullanma'];
    $firma_id = $_POST['firma_id'] === '' ? null : intval($_POST['firma_id']);
    try {
        $stmt = $db->prepare("INSERT INTO kuponlar (kod, oran, firma_id, kullanim_limiti, son_kullanma) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$kod, $oran, $firma_id, $kullanim, $son]);
        header('Location: admin_coupons.php');
        exit;
    } catch (Exception $e) {
        $msg = "Kupon eklenemedi: " . $e->getMessage();
    }
}

// Sil
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->prepare("DELETE FROM kuponlar WHERE id=?")->execute([$id]);
    header('Location: admin_coupons.php');
    exit;
}

$kuponlar = $db->query("SELECT * FROM kuponlar")->fetchAll(PDO::FETCH_ASSOC);
$firmalar = $db->query("SELECT * FROM firmas")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><title>Kupon Yönetimi</title></head>
<body>
    <h2>Kupon Yönetimi</h2>
    <?php if (isset($msg)) flash($msg); ?>

    <h3>Yeni Kupon</h3>
    <form method="post">
        Kod: <input name="kod" required>
        Oran(%): <input type="number" name="oran" value="10" required>
        Firma (opsiyonel): 
        <select name="firma_id">
            <option value="">Tüm Firmalar</option>
            <?php foreach ($firmalar as $f): ?>
                <option value="<?= $f['id'] ?>"><?= sanitize($f['name']) ?></option>
            <?php endforeach; ?>
        </select>
        Kullanım Limiti (boş = sınırsız): <input name="kullanim_limiti" type="number" min="0">
        Son Kullanma (YYYY-MM-DD): <input name="son_kullanma" type="date">
        <button type="submit" name="add_coupon">Ekle</button>
    </form>

    <h3>Mevcut Kuponlar</h3>
    <table border="1">
        <tr><th>ID</th><th>Kod</th><th>Oran</th><th>Firma</th><th>Limit</th><th>Son</th><th>Aksiyon</th></tr>
        <?php foreach ($kuponlar as $k): 
            $firmaName = '';
            foreach ($firmalar as $f) if ($f['id'] == $k['firma_id']) $firmaName = $f['name'];
        ?>
        <tr>
            <td><?= sanitize($k['id']) ?></td>
            <td><?= sanitize($k['kod']) ?></td>
            <td><?= sanitize($k['oran']) ?>%</td>
            <td><?= sanitize($firmaName) ?></td>
            <td><?= sanitize($k['kullanim_limiti']) ?></td>
            <td><?= sanitize($k['son_kullanma']) ?></td>
            <td><a href="?delete=<?= $k['id'] ?>" onclick="return confirm('Silinsin mi?')">Sil</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>