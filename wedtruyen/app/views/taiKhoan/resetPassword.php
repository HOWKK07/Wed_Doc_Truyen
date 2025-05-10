<?php
require_once '../../config/connect.php';
require_once '../../controllers/taiKhoanController.php';

$controller = new TaiKhoanController($conn);
$controller->datLaiMatKhau(); // Gọi controller để xử lý đặt lại mật khẩu
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lại Mật Khẩu</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/taiKhoan/resetPassword.css">
</head>
<body>
    <div class="form-container">
        <h1>Đặt Lại Mật Khẩu</h1>
        <form action="" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">

            <label for="mat_khau_moi">Mật khẩu mới:</label>
            <input type="password" id="mat_khau_moi" name="mat_khau_moi" required>

            <button type="submit">Đặt lại mật khẩu</button>
        </form>
    </div>
</body>
</html>