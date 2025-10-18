<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';

$id = $_GET['id'] ?? null;
if (!$id) die("Sefer ID gereklidir");

$stmt = $db->prepare("SELECT s.*, f.name as firma FROM sefers s JOIN firmas f ON s.firma_id=f.id WHERE s.id=?");
$stmt->execute([$id]);
$sefer = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$sefer) die("Sefer bulunamadı");
?>
<!DOCTYPE html>
<html>
<head><title>Sefer Detayları</title></head>
<body>
    <?php include 'menu.php'; ?>
    <h2><?= sanitize($sefer['firma']) ?> - Sefer Detayları</h2>
    <ul>
        <li>Kalkış: <?= sanitize($sefer['kalkis']) ?></li>
        <li>Varış: <?= sanitize($sefer['varis']) ?></li>
        <li>Tarih: <?= sanitize($sefer['tarih']) ?></li>
        <li>Saat: <?= sanitize($sefer['saat']) ?></li>
        <li>Fiyat: <?= sanitize($sefer['fiyat']) ?>₺</li>
        <li>Koltuk Sayısı: <?= sanitize($sefer['koltuk_sayisi']) ?></li>
    </ul>
    <?php if (!is_logged_in()): ?>
        <a href="login.php">Bilet satın almak için Giriş Yapın</a>
    <?php else: ?>
        <!-- Sadece yolcu rolüne izin vermek istersen get_user_role() == 'user' kontrolü ekle -->
        <a href="bilet_al.php?sefer_id=<?= $sefer['id'] ?>">Bilet Satın Al</a>
    <?php endif; ?>
</body>
</html>