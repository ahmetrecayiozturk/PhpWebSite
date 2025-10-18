<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'functions.php';

// Sadece admin erişimi
require_role('admin');

// Liste için tüm kullanıcıları al
$users = $db->query("SELECT id, username, credit FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

$selected_user_id = $_GET['user_id'] ?? ($users[0]['id'] ?? null);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        $msg = "Geçersiz istek (CSRF).";
    } else {
        $target_user = intval($_POST['target_user'] ?? 0);
        $amount = str_replace(',', '.', trim($_POST['amount'] ?? '0'));
        if (!is_numeric($amount) || $amount <= 0) {
            $msg = "Lütfen geçerli bir pozitif tutar girin.";
            $selected_user_id = $target_user;
        } else {
            $amount = round((float)$amount, 2);
            try {
                $db->beginTransaction();

                // Hedef kullanıcı var mı?
                $uStmt = $db->prepare("SELECT credit FROM users WHERE id = ?");
                $uStmt->execute([$target_user]);
                $exists = $uStmt->fetchColumn();
                if ($exists === false) {
                    $db->rollBack();
                    $msg = "Seçilen kullanıcı bulunamadı.";
                } else {
                    // bakiye güncelle
                    $upd = $db->prepare("UPDATE users SET credit = credit + ? WHERE id = ?");
                    $upd->execute([$amount, $target_user]);

                    // işlem kaydı
                    $ins = $db->prepare("INSERT INTO transactions (user_id, amount, type, reference) VALUES (?, ?, 'topup', ?)");
                    $ins->execute([$target_user, $amount, 'admin_manual_topup']);

                    $db->commit();
                    header("Location: bakiye_yukle.php?user_id={$target_user}&msg=" . urlencode("Bakiye başarıyla yüklendi."));
                    exit;
                }
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                $msg = "Bakiye yüklenirken hata: " . $e->getMessage();
            }
        }
    }
}

// Güncel bakiye gösterimi için seçili kullanıcıyı al
if ($selected_user_id) {
    $stmt = $db->prepare("SELECT id, username, credit FROM users WHERE id = ?");
    $stmt->execute([$selected_user_id]);
    $selected_user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $selected_user = null;
}
?>
<!DOCTYPE html>
<html>
<head><title>Bakiye Yükle (Admin)</title></head>
<body>
    <?php include 'menu.php'; ?>
    <h2>Bakiye Yükle (Admin)</h2>
    <?php if (isset($_GET['msg'])) flash(urldecode($_GET['msg'])); ?>
    <?php if (isset($msg)) flash($msg); ?>

    <form method="get" style="margin-bottom:1em">
        Hedef Kullanıcı:
        <select name="user_id" onchange="this.form.submit()">
            <?php foreach ($users as $u): ?>
                <option value="<?= $u['id'] ?>" <?= ($u['id'] == $selected_user_id) ? 'selected' : '' ?>><?= sanitize($u['username']) ?> (<?= number_format($u['credit'],2) ?>₺)</option>
            <?php endforeach; ?>
        </select>
        <noscript><button type="submit">Seç</button></noscript>
    </form>

    <?php if ($selected_user): ?>
    <h3>Hesap Bakiyeniz: <?= sanitize(number_format($selected_user['credit'], 2)) ?> ₺ (<?= sanitize($selected_user['username']) ?>)</h3>

    <form method="post">
        Yüklenecek Tutar (₺): <input name="amount" required pattern="^\d+(\.\d{1,2})?$" inputmode="decimal" placeholder="100.00"><br>
        <input type="hidden" name="target_user" value="<?= $selected_user['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <button type="submit">Yükle</button>
    </form>
    <?php else: ?>
        <p>Seçili kullanıcı bulunamadı.</p>
    <?php endif; ?>

    <p>Not: Bu sayfa admin tarafından manuel bakiye yükleme içindir. Gerçek ödemeler için ödeme sağlayıcı entegrasyonu gerekir.</p>
</body>
</html>