<?php
session_start();
require_once '../../config/connect.php'; // Kết nối cơ sở dữ liệu
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Truyện</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column; /* Đảm bảo nội dung chính hiển thị theo chiều dọc */
        }

        .content {
            flex: 1;
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

        form input, form select, form textarea {
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
        <h1>Thêm Truyện</h1>

        <!-- Form thêm truyện -->
        <form action="processAdd.php" method="POST" enctype="multipart/form-data">
            <label for="ten_truyen">Tên truyện</label>
            <input type="text" id="ten_truyen" name="ten_truyen" required>

            <label for="tac_gia">Tác giả</label>
            <input type="text" id="tac_gia" name="tac_gia" required>

            <label for="the_loai">Thể loại</label>
            <input type="text" id="the_loai" name="the_loai" required>

            <label for="loai_truyen">Loại truyện</label>
            <input type="text" id="loai_truyen" name="loai_truyen" required>

            <label for="anh_bia">Ảnh bìa</label>
            <input type="file" id="anh_bia" name="anh_bia" accept="image/*" required>

            <label for="mo_ta">Mô tả</label>
            <textarea id="mo_ta" name="mo_ta" rows="5" required></textarea>

            <button type="submit">Thêm Truyện</button>
        </form>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>

<?php
$conn->close(); // Đóng kết nối cơ sở dữ liệu
?>