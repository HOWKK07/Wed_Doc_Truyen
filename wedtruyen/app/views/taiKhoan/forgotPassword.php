<?php
require_once '../../config/connect.php';
require_once '../../controllers/taiKhoanController.php';

$controller = new TaiKhoanController($conn);
$controller->quenMatKhau(); // Gọi trực tiếp controller để xử lý quên mật khẩu
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/taiKhoan/forgotPassword.css">
</head>
<body>
    <div class="form-container">
        <h1>Quên Mật Khẩu</h1>
        <form action="processForgotPassword.php" method="POST">
            <label for="email">Nhập email của bạn:</label>
            <input type="email" id="email" name="email" required>

            <button type="submit">Gửi yêu cầu</button>
        </form>

        <p>Quay lại <a href="login.php">Đăng nhập</a></p>
    </div>
</body>
</html>