<?php
require_once '../../config/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $mat_khau_moi = password_hash($_POST['mat_khau_moi'], PASSWORD_BCRYPT);

    // Kiểm tra token và cập nhật mật khẩu
    $sql = "UPDATE tai_khoan SET mat_khau = ?, reset_token = NULL WHERE reset_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $mat_khau_moi, $token);

    if ($stmt->execute()) {
        echo "Mật khẩu đã được đặt lại thành công. <a href='login.php'>Đăng nhập</a>";
    } else {
        echo "Token không hợp lệ hoặc đã hết hạn.";
    }
}
?>