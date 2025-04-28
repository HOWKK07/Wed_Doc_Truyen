<?php
session_start();
require_once '../../config/connect.php';
require_once '../../models/anhChuongModel.php';

if (!isset($_GET['id_chuong']) || empty($_GET['id_chuong'])) {
    die("Lỗi: Không tìm thấy ID chương.");
}

$id_chuong = $_GET['id_chuong'];
$model = new AnhChuongModel($conn);
$anh_chuongs = $model->layDanhSachAnh($id_chuong);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Ảnh</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .image-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .image-item {
            width: 150px;
            text-align: center;
        }

        .image-item img {
            width: 100%;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .image-item .actions {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
        }

        .image-item .actions a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
        }

        .image-item .actions .edit-btn {
            background-color: #ffc107;
            color: black;
        }

        .image-item .actions .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .image-item .actions a:hover {
            opacity: 0.8;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            color: #007bff;
            font-size: 16px;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="container">
        <h1>Danh Sách Ảnh</h1>
        <div class="image-list">
            <?php 
            $so_trang_lon_nhat = 0; // Biến để lưu số trang lớn nhất
            $index = 1; // Bắt đầu đếm từ 1
            while ($anh = $anh_chuongs->fetch_assoc()): ?>
                <div class="image-item">
                    <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($anh['duong_dan_anh']); ?>?t=<?php echo time(); ?>" alt="Trang <?php echo $index; ?>">
                    <p>Trang: <?php echo $index; ?></p>
                    <div class="actions">
                        <a href="delete.php?id_anh=<?php echo $anh['id_anh']; ?>&id_chuong=<?php echo $id_chuong; ?>" 
                           class="delete-btn" 
                           style="background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none;" 
                           onclick="return confirm('Bạn có chắc chắn muốn xóa ảnh này?');">
                            Xóa
                        </a>
                    </div>
                </div>
            <?php 
            $so_trang_lon_nhat = $index; // Cập nhật số trang lớn nhất
            $index++; // Tăng số trang sau mỗi ảnh
            endwhile; ?>
        </div>

        <!-- Hiển thị nút Thêm Ảnh -->
        <div class="add-image">
            <a href="add.php?id_chuong=<?php echo $id_chuong; ?>&so_trang_bat_dau=<?php echo $so_trang_lon_nhat + 1; ?>" 
               class="add-image-btn" 
               style="background-color: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 20px;">
                Thêm Ảnh
            </a>
        </div>

        <a href="../chapter/docchapter.php?id_chuong=<?php echo $id_chuong; ?>" class="back-link">Quay lại chương</a>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>