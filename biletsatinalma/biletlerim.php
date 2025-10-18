<?php
// biletlerim.php (düzeltilmiş)
// include yolları __DIR__ ile yapıldı, böylece dosya hangi dizinden çalışırsa çalışsın doğru functions.php dahil edilir.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_login();

$user_id = $_SESSION['user_id'];

$stmt = $db->prepare(
    "SELECT b.*, s.kalkis, s.varis, s.tarih, s.saat FROM biletler b 
     JOIN sefers s ON b.sefer_id=s.id WHERE b.user_id=? ORDER BY b.created_at DESC"
);
$stmt->execute([$user_id]);
$biletler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Biletlerim</title></head>
<body>
    <?php include __DIR__ . '/menu.php'; ?>
    <h2>Biletlerim</h2>

    <?php if (isset($_GET['msg'])) { echo "<div class='flash'>" . htmlspecialchars(urldecode($_GET['msg']), ENT_QUOTES, 'UTF-8') . "</div>"; } ?>

    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Kalkış</th><th>Varış</th><th>Tarih</th><th>Saat</th><th>Koltuk</th><th>Fiyat</th><th>Durum</th><th>İşlem</th>
        </tr>
        <?php if (!$biletler): ?>
            <tr><td colspan="8">Henüz biletiniz yok.</td></tr>
        <?php else: ?>
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
                    <?php if ($b['durum'] === 'aktif'): ?>
                        <a href="bilet_iptal.php?id=<?= (int)$b['id'] ?>">İptal Et</a>
                        |
                        <a href="pdf_uret.php?id=<?= (int)$b['id'] ?>">PDF İndir</a>
                    <?php else: ?>
                        İptal Edildi
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</body>
</html>