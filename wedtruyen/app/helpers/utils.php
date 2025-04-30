<?php
function checkAdminAccess() {
    if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] !== 'admin') {
        die("Bạn không có quyền truy cập trang này.");
    }
}

function safeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit();
}