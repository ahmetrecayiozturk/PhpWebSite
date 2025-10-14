<?php
require_once 'config.php';
require_once 'auth.php';
require_login();

$id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT b.*, s.tarih, s.saat FROM biletler b JOIN sefers s ON b.sefer_id = s.id WHERE b.id=? AND b.user_id=?");
$stmt->execute([$id, $user_id]);
$bilet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bilet || $bilet['durum'] != 'aktif') die("Bilet bulunamadı veya zaten iptal!");

$sefer_datetime = strtotime($bilet['tarih'] . ' ' . $bilet['saat']);
if ($sefer_datetime - time() < 3600) {
    die("Kalkışa 1 saatten az kaldığı için iptal edilemez!");
}

// Bilet iptal & kredi iade
$db->prepare("UPDATE biletler SET durum='iptal' WHERE id=?")->execute([$id]);
$db->prepare("UPDATE users SET credit=credit+? WHERE id=?")->execute([$bilet['fiyat'], $user_id]);

header("Location: biletlerim.php?msg=Bilet+iptal+edildi");
exit;
?>