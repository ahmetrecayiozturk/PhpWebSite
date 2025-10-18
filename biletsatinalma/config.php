<?php
// config.php
$db = new PDO('sqlite:' . __DIR__ . '/db.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// session başlat (güvenli şekilde)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Otomatik admin oluştur: eğer hiçbir admin yoksa oluştur
try {
    $check = $db->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
    $check->execute(['admin']);
    $count = (int) $check->fetchColumn();

    if ($count === 0) {
        $adminUser  = 'admin_general';
        $adminPass  = 'q'; // başlangıç şifresi (tes­t için). Prod da değiştirin.
        $adminEmail = 'admin@site.com';
        $adminCredit = 0;
        $hash = password_hash($adminPass, PASSWORD_DEFAULT);

        try {
            $insert = $db->prepare("INSERT INTO users (username, password, email, role, credit) VALUES (?, ?, ?, ?, ?)");
            $insert->execute([$adminUser, $hash, $adminEmail, 'admin', $adminCredit]);
        } catch (PDOException $e) {
            // UNIQUE hatası veya başka hata; eğer kullanıcı varsa rolü admin yap
            if ($e->getCode() === '23000') {
                try {
                    $update = $db->prepare("UPDATE users SET password = ?, email = ?, role = 'admin', credit = ? WHERE username = ?");
                    $update->execute([$hash, $adminEmail, $adminCredit, $adminUser]);
                } catch (Exception $ee) {
                    error_log("Admin güncelleme hatası: " . $ee->getMessage());
                }
            } else {
                error_log("Admin oluşturma hatası: " . $e->getMessage());
            }
        }
    }
} catch (Exception $e) {
    error_log("Admin kontrol hatası: " . $e->getMessage());
}
?>