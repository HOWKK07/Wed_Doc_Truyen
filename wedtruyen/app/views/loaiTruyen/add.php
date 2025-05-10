<?php
require_once '../../config/connect.php';
require_once '../../controllers/loaiTruyenController.php';

$controller = new LoaiTruyenController($conn);

try {
    $controller->themLoaiTruyen(); // Gọi controller để xử lý thêm loại truyện
} catch (Exception $e) {
    echo "<p style='color: red;'>Lỗi: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Loại Truyện</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/loaiTruyen/add.css">
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