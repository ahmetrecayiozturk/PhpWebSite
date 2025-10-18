<?php
// (bilet_al.php içindeki POST işleme kısmı içinde kullan)
// kredi kontrol ve transaction (SQLite uyumlu)

try {
    // SQLite için kilit almak istersen BEGIN IMMEDIATE kullan
    // Bu, başka bir writer'ın aynı anda yazmasını engeller.
    $db->beginTransaction(); // veya $db->exec('BEGIN IMMEDIATE');

    // NOT: SQLite 'FOR UPDATE' desteklemediği için onu kaldırdık
    $stmt = $db->prepare("SELECT credit FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $credit = $stmt->fetchColumn();

    if ($credit < $final_price) {
        $db->rollBack();
        $msg = "Yetersiz bakiye!";
    } else {
        $stmt = $db->prepare("INSERT INTO biletler (user_id, sefer_id, koltuk_no, fiyat, durum, created_at) VALUES (?, ?, ?, ?, 'aktif', datetime('now'))");
        $stmt->execute([$user_id, $sefer_id, $koltuk_no, $final_price]);

        $db->prepare("UPDATE users SET credit = credit - ? WHERE id = ?")->execute([$final_price, $user_id]);

        // kupon kullanım limiti azalt
        if ($kupon) {
            if (!is_null($kupon['kullanim_limiti'])) {
                $db->prepare("UPDATE kuponlar SET kullanim_limiti = kullanim_limiti - 1 WHERE id = ?")->execute([$kupon['id']]);
            }
        }

        $db->commit();
        header("Location: biletlerim.php?msg=" . urlencode("Bilet Alındı"));
        exit;
    }
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    $msg = "Bilet alınırken hata: " . $e->getMessage();
}
?>