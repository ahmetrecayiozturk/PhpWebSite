<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';
require_login();

// Güvenlik/uyumluluk: biletler için aktif (sefer_id, koltuk_no) unique index oluştur (varsa atlar)
try {
    $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS ux_biletler_sefer_koltuk_aktif ON biletler (sefer_id, koltuk_no) WHERE durum='aktif';");
} catch (Exception $e) {
    // Oluşturmada problem olsa da işlemleri engelleme; logla
    error_log("INDEX_CREATE_WARN: " . $e->getMessage());
}

$sefer_id = isset($_GET['sefer_id']) ? intval($_GET['sefer_id']) : null;
if (!$sefer_id) die("Sefer ID eksik!");

$stmt = $db->prepare("SELECT * FROM sefers WHERE id = ?");
$stmt->execute([$sefer_id]);
$sefer = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$sefer) die("Sefer bulunamadı");

// koltuk_sayisi güvence
$sefer['koltuk_sayisi'] = max(0, (int)($sefer['koltuk_sayisi'] ?? 0));
if ($sefer['koltuk_sayisi'] <= 0) die("Koltuk sayısı geçersiz!");

// Dolu koltukları al ve integer olarak normalleştir
$stmt = $db->prepare("SELECT koltuk_no FROM biletler WHERE sefer_id = ? AND durum = 'aktif'");
$stmt->execute([$sefer_id]);
$dolu_koltuklar_raw = $stmt->fetchAll(PDO::FETCH_COLUMN);
$dolu_koltuklar = array_map('intval', $dolu_koltuklar_raw);

$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $koltuk_no = isset($_POST['koltuk_no']) ? intval($_POST['koltuk_no']) : 0;
    $user_id = $_SESSION['user_id'];
    $kupon_kod = trim($_POST['kupon'] ?? '');

    // Basit validasyon
    if ($koltuk_no <= 0 || $koltuk_no > $sefer['koltuk_sayisi']) {
        $msg = "Geçersiz koltuk numarası!";
    } else {
        // Uygulama seviyesi tekrar kontrol (race durumunda DB unique index'e güveniyoruz)
        if (in_array($koltuk_no, $dolu_koltuklar, true)) {
            $msg = "Koltuk dolu!";
        } else {
            // Kupon ve fiyat hesaplama
            $final_price = (float)$sefer['fiyat'];
            $kupon = null;
            if ($kupon_kod !== '') {
                $kstmt = $db->prepare("SELECT * FROM kuponlar WHERE kod = ?");
                $kstmt->execute([$kupon_kod]);
                $kupon = $kstmt->fetch(PDO::FETCH_ASSOC);
                if (!$kupon) {
                    $msg = "Geçersiz kupon kodu!";
                } else {
                    if (!empty($kupon['son_kullanma']) && strtotime($kupon['son_kullanma']) < strtotime(date('Y-m-d'))) {
                        $msg = "Kuponun son kullanma tarihi geçmiş!";
                    } elseif (!is_null($kupon['kullanim_limiti']) && $kupon['kullanim_limiti'] <= 0) {
                        $msg = "Kupon kullanım limiti dolmuş!";
                    } elseif (!is_null($kupon['firma_id']) && $kupon['firma_id'] != $sefer['firma_id']) {
                        $msg = "Bu kupon bu firmada geçerli değil!";
                    } else {
                        $final_price = round($final_price * (100 - intval($kupon['oran'])) / 100, 2);
                    }
                }
            }
        }
    }

    if (!isset($msg)) {
        try {
            // PDO transaction kullan
            $db->beginTransaction();

            // DB seviyesinde tekrar koltuk kontrolü (uygulama + DB kombosu)
            $checkStmt = $db->prepare("SELECT 1 FROM biletler WHERE sefer_id = ? AND koltuk_no = ? AND durum = 'aktif' LIMIT 1");
            $checkStmt->execute([$sefer_id, $koltuk_no]);
            if ($checkStmt->fetch()) {
                // Başka bir işlem araya girmiş
                if ($db->inTransaction()) { try { $db->rollBack(); } catch (Exception $er) { } }
                $msg = "Seçilen koltuk başka kullanıcı tarafından alınmış. Lütfen farklı bir koltuk seçin.";
            } else {
                // Kullanıcı bakiyesi al
                $stmt = $db->prepare("SELECT credit FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $creditVal = $stmt->fetchColumn();
                $credit = $creditVal === false ? 0.0 : (float)$creditVal;

                if ($credit < $final_price) {
                    if ($db->inTransaction()) { try { $db->rollBack(); } catch (Exception $er) { } }
                    $msg = "Yetersiz bakiye!";
                } else {
                    // Bilet ekle
                    $insert = $db->prepare("INSERT INTO biletler (user_id, sefer_id, koltuk_no, fiyat, durum, created_at) VALUES (?, ?, ?, ?, 'aktif', datetime('now'))");
                    $insert->execute([$user_id, $sefer_id, $koltuk_no, $final_price]);

                    // Kullanıcı bakiyesini düş
                    $db->prepare("UPDATE users SET credit = credit - ? WHERE id = ?")->execute([$final_price, $user_id]);

                    // Kupon kullanım limiti varsa azalt
                    if ($kupon && !is_null($kupon['kullanim_limiti'])) {
                        $db->prepare("UPDATE kuponlar SET kullanim_limiti = kullanim_limiti - 1 WHERE id = ?")->execute([$kupon['id']]);
                    }

                    $db->commit();
                    header("Location: biletlerim.php?msg=" . urlencode("Bilet Alındı"));
                    exit;
                }
            }
        } catch (Exception $e) {
            // rollback güvenli çağrı
            if ($db->inTransaction()) {
                try { $db->rollBack(); } catch (Exception $er) { /* ignore */ }
            }
            $errorMsg = $e->getMessage();
            // DB constraint hatası (ör. UNIQUE) kontrolü
            if (stripos($errorMsg, 'constraint') !== false || stripos($errorMsg, 'unique') !== false) {
                $msg = "Seçilen koltuk başka kullanıcı tarafından alındı. Lütfen farklı bir koltuk seçin.";
            } else {
                $msg = "Bilet alınırken hata: " . $errorMsg;
            }
            error_log("BILET_AL_ERROR user={$user_id} sefer={$sefer_id} koltuk={$koltuk_no} error={$errorMsg}");
        }
    }

    // Eğer işlem sonucu dolu_koltuklar değiştiyse tekrar çek (sayfa yeniden render için güncel liste)
    $stmt = $db->prepare("SELECT koltuk_no FROM biletler WHERE sefer_id = ? AND durum = 'aktif'");
    $stmt->execute([$sefer_id]);
    $dolu_koltuklar_raw = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $dolu_koltuklar = array_map('intval', $dolu_koltuklar_raw);
}
?>
<!DOCTYPE html>
<html>
<head><title>Bilet Satın Al</title></head>
<body>
    <?php include 'menu.php'; ?>
    <h2>Bilet Satın Al: <?= sanitize($sefer['kalkis']) ?> - <?= sanitize($sefer['varis']) ?></h2>

    <?php if (isset($msg)) flash($msg); ?>

    <form method="post" novalidate>
        <label>Koltuk No:</label>
        <select name="koltuk_no" required>
            <?php
            // current dolu koltuklar integer array
            for ($i = 1; $i <= $sefer['koltuk_sayisi']; $i++):
                $disabled = in_array($i, $dolu_koltuklar, true) ? 'disabled' : '';
                $label = $i . (in_array($i, $dolu_koltuklar, true) ? ' (Dolu)' : '');
            ?>
                <option value="<?= $i ?>" <?= $disabled ?>><?= $label ?></option>
            <?php endfor; ?>
        </select>
        <br>
        Kupon Kodu (opsiyonel): <input name="kupon" value="<?= isset($kupon_kod) ? sanitize($kupon_kod) : '' ?>">
        <br><br>
        <button type="submit">Satın Al</button>
    </form>
</body>
</html>