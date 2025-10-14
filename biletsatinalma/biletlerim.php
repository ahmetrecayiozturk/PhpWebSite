<?php
require_once 'config.php';
require_once 'auth.php';
require_login();

$user_id = $_SESSION['user_id'];
$stmt = $db->prepare(
    "SELECT b.*, s.kalkis, s.varis, s.tarih, s.saat FROM biletler b 
     JOIN sefers s ON b.sefer_id=s.id WHERE b.user_id=?"
);
$stmt->execute([$user_id]);
$biletler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><title>Biletlerim</title></head>
<body>
    <h2>Biletlerim</h2>
    <table>
        <tr>
            <th>Kalkış</th><th>Varış</th><th>Tarih</th><th>Saat</th><th>Koltuk</th><th>Fiyat</th><th>Durum</th><th>İşlem</th>
        </tr>
        <?php foreach ($biletler as $b): ?>
        <tr>
            <td><?= sanitize($b['kalkis']) ?></td>
            <td><?= sanitize($b['varis']) ?></td>
            <td><?= sanitize($b['tarih']) ?></td>
            <td><?= sanitize($b['saat']) ?></td>
            <td><?= sanitize($b['koltuk_no']) ?></td>
            <td><?= sanitize($b['fiyat']) ?>₺</td>
            <td><?= sanitize($b['durum']) ?></td>
            <td>
                <?php if ($b['durum'] == 'aktif'): ?>
                    <a href="bilet_iptal.php?id=<?= $b['id'] ?>">İptal Et</a> | 
                    <a href="pdf_uret.php?id=<?= $b['id'] ?>">PDF İndir</a>
                <?php else: ?>
                    İptal Edildi
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>