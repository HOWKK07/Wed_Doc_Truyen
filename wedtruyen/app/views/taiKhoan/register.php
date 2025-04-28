<?php
require_once '../../config/connect.php';
require_once '../../controllers/taiKhoanController.php';

$controller = new TaiKhoanController($conn);
$controller->dangKy(); // Gọi trực tiếp controller để xử lý đăng ký
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
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
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #218838;
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
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Đăng Ký</h1>
        <form action="http://localhost/Wed_Doc_Truyen/app/views/taiKhoan/register.php" method="POST">
            <label for="ten_dang_nhap">Tên đăng nhập:</label>
            <input type="text" id="ten_dang_nhap" name="ten_dang_nhap" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="mat_khau">Mật khẩu:</label>
            <input type="password" id="mat_khau" name="mat_khau" required>

            <button type="submit">Đăng Ký</button>
        </form>

        <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
    </div>
</body>
</html>