<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/taiKhoanController.php';

$controller = new TaiKhoanController($conn);
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $controller->dangNhap();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/taiKhoan/login.css">
</head>
<body>
    <div class="form-container">
        <h1>Đăng Nhập</h1>
        <?php if (!empty($error_message)): ?>
            <p style="color: red; text-align: center;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <label for="ten_dang_nhap">Tên đăng nhập:</label>
            <input type="text" id="ten_dang_nhap" name="ten_dang_nhap" required>

            <label for="mat_khau">Mật khẩu:</label>
            <input type="password" id="mat_khau" name="mat_khau" required>

            <button type="submit">Đăng Nhập</button>
        </form>

        <div class="forgot-password">
            <a href="forgotPassword.php">Quên mật khẩu?</a>
        </div>

        <p>Bạn chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
    </div>
</body>
</html>