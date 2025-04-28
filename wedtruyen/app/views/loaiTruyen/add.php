<?php
require_once '../../config/connect.php';
require_once '../../controllers/loaiTruyenController.php';

$controller = new LoaiTruyenController($conn);
$controller->themLoaiTruyen(); // Gọi controller để xử lý thêm loại truyện

if ($_FILES["anh_bia"]["error"] !== UPLOAD_ERR_OK) {
    echo "Lỗi tải lên: " . $_FILES["anh_bia"]["error"];
    return;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Loại Truyện</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .content {
            padding: 20px;
        }

        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        form h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        form button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <form action="" method="POST">
            <h1>Thêm Loại Truyện</h1>
            <label for="ten_loai_truyen">Tên Loại Truyện:</label>
            <input type="text" id="ten_loai_truyen" name="ten_loai_truyen" required>
            <button type="submit">Thêm</button>
        </form>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>