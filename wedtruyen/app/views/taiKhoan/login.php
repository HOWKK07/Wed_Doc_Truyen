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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .form-container h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .form-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #0056b3;
        }

        .form-container p {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .form-container p a {
            color: #007bff;
            text-decoration: none;
        }

        .form-container p a:hover {
            text-decoration: underline;
        }

        .forgot-password {
            text-align: center;
            margin-top: 10px;
        }

        .forgot-password a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
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