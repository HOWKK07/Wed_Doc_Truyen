<?php
require_once '../../config/connect.php';
require_once '../../controllers/theLoaiController.php';

$controller = new TheLoaiController($conn);
$controller->themTheLoai();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Thể Loại</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/theLoai/add.css">
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <form action="" method="POST">
            <h1>Thêm Thể Loại</h1>
            <label for="ten_theloai">Tên Thể Loại:</label>
            <input type="text" id="ten_theloai" name="ten_theloai" required>
            <button type="submit">Thêm</button>
        </form>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>