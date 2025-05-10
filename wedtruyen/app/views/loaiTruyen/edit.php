<?php
require_once '../../config/connect.php';
require_once '../../controllers/loaiTruyenController.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $controller = new LoaiTruyenController($conn);
    $loaiTruyen = $controller->layLoaiTruyenTheoId($id);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->suaLoaiTruyen($id);
    }
} else {
    echo "Lỗi: ID không hợp lệ.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Loại Truyện</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/loaiTruyen/add.css">
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <form action="" method="POST">
            <h1>Sửa Loại Truyện</h1>
            <label for="ten_loaitruyen">Tên Loại Truyện:</label>
            <input type="text" id="ten_loaitruyen" name="ten_loaitruyen" 
                   value="<?php echo isset($loaiTruyen['ten_loai_truyen']) ? htmlspecialchars($loaiTruyen['ten_loai_truyen']) : ''; ?>" 
                   required>
            <button type="submit">Cập Nhật</button>
        </form>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>