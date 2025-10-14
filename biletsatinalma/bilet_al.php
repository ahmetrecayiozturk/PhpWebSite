<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';
require_login();

$sefer_id = $_GET['sefer_id'] ?? null;
if (!$sefer_id) die("Sefer ID eksik!");

$stmt = $db->prepare("SELECT * FROM sefers WHERE id=?");
$stmt->execute([$sefer_id]);
$sefer = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $koltuk_no = intval($_POST['koltuk_no']);
    $user_id = $_SESSION['user_id'];
    $stmt = $db->prepare("SELECT * FROM biletler WHERE sefer_id=? AND koltuk_no=? AND durum='aktif'");
    $stmt->execute([$sefer_id, $koltuk_no]);
    if ($stmt->fetch()) {
        $msg = "Koltuk dolu!";
    } else {
        $stmt = $db->prepare("SELECT credit FROM users WHERE id=?");
        $stmt->execute([$user_id]);
        $credit = $stmt->fetchColumn();
        if ($credit < $sefer['fiyat']) {
            $msg = "Yetersiz bakiye!";
        } else {
            // Bilet kaydet
            $stmt = $db->prepare("INSERT INTO biletler (user_id, sefer_id, koltuk_no, fiyat, durum, created_at) VALUES (?, ?, ?, ?, 'aktif', datetime('now'))");
            $stmt->execute([$user_id, $sefer_id, $koltuk_no, $sefer['fiyat']]);
            // Bakiye düş
            $db->prepare("UPDATE users SET credit=credit-? WHERE id=?")->execute([$sefer['fiyat'], $user_id]);
            header("Location: biletlerim.php?msg=Bilet+Alındı");
            exit;
        }
    }
}
$stmt = $db->prepare("SELECT koltuk_no FROM biletler WHERE sefer_id=? AND durum='aktif'");
$stmt->execute([$sefer_id]);
$dolu_koltuklar = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html>
<head><title>Bilet Satın Al</title></head>
<body>
    <h2>Bilet Satın Al: <?= sanitize($sefer['kalkis']) ?> - <?= sanitize($sefer['varis']) ?></h2>
    <?php if (isset($msg)) flash($msg); ?>
    <form method="post">
        <label>Koltuk No:</label>
        <select name="koltuk_no">
            <?php for ($i=1; $i<=$sefer['koltuk_sayisi']; $i++): ?>
                <option value="<?= $i ?>" <?= in_array($i, $dolu_koltuklar) ? 'disabled' : '' ?>><?= $i ?> <?= in_array($i, $dolu_koltuklar) ? '(Dolu)' : '' ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit">Satın Al</button>
    </form>
</body>
</html>