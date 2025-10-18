<?php
// SET PASSWORD VIA PHP (sağlam/güvenli)
// KULLANIM: php set_password.php admin_general admin123

if ($argc < 3) {
    echo "Kullanım: php set_password.php <username> <new_password>\n";
    exit(1);
}

$username = $argv[1];
$newPassword = $argv[2];

$dbPath = __DIR__ . '/db.sqlite';
if (!file_exists($dbPath)) {
    echo "db.sqlite bulunamadı: $dbPath\n";
    exit(1);
}

$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// hash oluştur
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $db->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->execute([$newHash, $username]);
$cnt = $stmt->rowCount();

if ($cnt > 0) {
    echo "Şifre güncellendi: $username\n";
    echo "Yeni hash uzunluğu: " . strlen($newHash) . "\n";
    echo "password_verify kontrol: ";
    var_export(password_verify($newPassword, $newHash));
    echo "\n";
} else {
    echo "Kullanıcı bulunamadı veya güncellenmedi: $username\n";
}