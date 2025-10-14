<?php
function sanitize($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
function flash($msg) {
    echo "<div class='flash'>$msg</div>";
}
?>