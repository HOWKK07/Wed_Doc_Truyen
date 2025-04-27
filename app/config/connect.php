<?php
// connect.php

$host = "localhost";      // máy chủ
$username = "root";       // tài khoản mặc định của MySQL trong XAMPP
$password = "";           // mật khẩu thường để trống
$database = "wedtruyen";  // tên database bạn tạo

// Kết nối
$conn = new mysqli($host, $username, $password, $database);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4"); // Để gõ tiếng Việt ngon
?>
