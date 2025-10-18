<?php
// VERIFY DB PASSWORD
// KULLANIM: php verify_db_password.php

$dbPath = __DIR__ . '/db.sqlite';
if (!file_exists($dbPath)) {
    echo "db.sqlite bulunamadı: $dbPath\n";
    exit(1);
}

$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$username = $argv[1] ?? 'admin_general';

$stmt = $db->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Kullanıcı bulunamadı: $username\n";
    exit(1);
}

$hash = $user['password'] ?? '';
echo "Kullanıcı: {$user['username']} (id={$user['id']}, role={$user['role']})\n";
echo "Ham hash (raw, may contain invisible chars):\n";
var_dump($hash);
echo "Length: " . strlen($hash) . "\n";
echo "Hex (bin2hex): " . bin2hex($hash) . "\n";
$last = $hash === '' ? '' : ord($hash[strlen($hash)-1]);
echo "Son byte ord değeri (son byte): " . ($last === '' ? 'none' : $last) . "\n";

$passwordToTest = 'admin123';
echo "\npassword_verify('$passwordToTest', db-hash) => ";
var_export(password_verify($passwordToTest, $hash));
echo "\n\n-- Oluşturulan yeni hash (aynı PHP runtime ile test) --\n";
$new = password_hash($passwordToTest, PASSWORD_DEFAULT);
echo "Yeni hash: $new\n";
echo "password_verify('$passwordToTest', new-hash) => ";
var_export(password_verify($passwordToTest, $new));
echo "\n";