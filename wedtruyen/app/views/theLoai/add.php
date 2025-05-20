<?php
require_once '../../config/connect.php';
require_once '../../controllers/theLoaiController.php';

$controller = new TheLoaiController($conn);
$controller->themTheLoai();
?>
<?php include __DIR__ . '/../shares/header.php'; ?> <!-- Đặt dòng này ngay trước <!DOCTYPE html> -->

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Thể Loại</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/theLoai/add.css">
</head>
<body>
    <!-- Nội dung chính -->
    <div class="content">
        <form action="" method="POST">
            <h2>Thêm Thể Loại Mới</h2>
            <label>Tên thể loại:</label>
            <input type="text" name="ten_theloai" required>
            <br>
            <button type="submit">Thêm thể loại</button>
        </form>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../shares/footer.php'; ?>
</body>
</html>