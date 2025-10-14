<?php
echo '<nav>';
if (is_logged_in()) {
    echo "<a href='index.php'>Ana Sayfa</a> | ";
    echo "<a href='biletlerim.php'>Biletlerim</a> | ";
    if (get_user_role() == 'firma_admin') echo "<a href='firma_panel.php'>Firma Paneli</a> | ";
    if (get_user_role() == 'admin') echo "<a href='admin_panel.php'>Admin Paneli</a> | ";
    echo "<a href='logout.php'>Çıkış</a>";
} else {
    echo "<a href='index.php'>Ana Sayfa</a> | <a href='login.php'>Giriş</a> | <a href='register.php'>Kayıt Ol</a>";
}
echo '</nav>';
?>