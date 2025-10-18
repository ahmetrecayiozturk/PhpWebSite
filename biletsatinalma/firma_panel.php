<?php
require_once 'config.php';
require_once 'auth.php';
require_role('firma_admin');
require_once 'functions.php';

$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT firma_id FROM users WHERE id=?");
$stmt->execute([$user_id]);
$firma_id = $stmt->fetchColumn();

if (!$firma_id) {
    die("Firma bilgisi yok.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ekle'])) {
    $stmt = $db->prepare("INSERT INTO sefers (firma_id, kalkis, varis, tarih, saat, fiyat, koltuk_sayisi) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $firma_id,
        $_POST['kalkis'],
        $_POST['varis'],
        $_POST['tarih'],
        $_POST['saat'],
        $_POST['fiyat'],
        $_POST['koltuk_sayisi']
    ]);
    header('Location: firma_panel.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM sefers WHERE firma_id = ?");
$stmt->execute([$firma_id]);
$sefers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><title>Firma Paneli</title></head>
<body>
    <h2>Firma Seferleri</h2>
    <form method="post">
        Kalkış: <input name="kalkis" required>
        Varış: <input name="varis" required>
        Tarih: <input name="tarih" type="date" required>
        Saat: <input name="saat" type="time" required>
        Fiyat: <input name="fiyat" type="number" step="0.01" required>
        Koltuk: <input name="koltuk_sayisi" type="number" required>
        <button type="submit" name="ekle">Sefer Ekle</button>
    </form>

    <h3>Mevcut Seferler</h3>
    <table border="1">
        <tr><th>ID</th><th>Kalkış</th><th>Varış</th><th>Tarih</th><th>Saat</th><th>Fiyat</th></tr>
        <?php foreach ($sefers as $s): ?>
        <tr>
            <td><?= sanitize($s['id']) ?></td>
            <td><?= sanitize($s['kalkis']) ?></td>
            <td><?= sanitize($s['varis']) ?></td>
            <td><?= sanitize($s['tarih']) ?></td>
            <td><?= sanitize($s['saat']) ?></td>
            <td><?= sanitize($s['fiyat']) ?>₺</td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>