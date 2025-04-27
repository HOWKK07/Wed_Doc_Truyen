<?php
session_start(); // Bắt đầu session
session_destroy(); // Hủy session
header("Location: /Wed_Doc_Truyen/app/index.php"); // Chuyển hướng về trang chủ
exit();
?>