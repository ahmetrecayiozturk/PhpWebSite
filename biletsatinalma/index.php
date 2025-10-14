<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';

$kalkis = $_GET['kalkis'] ?? '';
$varis  = $_GET['varis'] ?? '';

$query = "SELECT s.*, f.name as firma FROM sefers s JOIN firmas f ON s.firma_id=f.id";
$params = [];
if ($kalkis && $varis) {
    $query .= " WHERE s.kalkis=? AND s.varis=?";
    $params = [$kalkis, $varis];
}
$stmt = $db->prepare($query);
$stmt->execute($params);
$sefers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bilet Satın Alma Platformu</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'menu.php'; ?>
    <h2>Sefer Arama</h2>
    <form method="get">
        Kalkış: <input name="kalkis" value="<?= sanitize($kalkis) ?>" required>
        Varış: <input name="varis" value="<?= sanitize($varis) ?>" required>
        <button type="submit">Ara</button>
    </form>
    <h3>Seferler</h3>
    <table>
        <tr><th>Firma</th><th>Kalkış</th><th>Varış</th><th>Tarih</th><th>Saat</th><th>Fiyat</th><th>Detay</th></tr>
        <?php foreach($sefers as $s): ?>
        <tr>
            <td><?= sanitize($s['firma']) ?></td>
            <td><?= sanitize($s['kalkis']) ?></td>
            <td><?= sanitize($s['varis']) ?></td>
            <td><?= sanitize($s['tarih']) ?></td>
            <td><?= sanitize($s['saat']) ?></td>
            <td><?= sanitize($s['fiyat']) ?>₺</td>
            <td><a href="sefer_detay.php?id=<?= $s['id'] ?>">Detay</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>