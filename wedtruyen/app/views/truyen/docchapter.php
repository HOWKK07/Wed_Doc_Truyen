<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/chapterController.php';
require_once '../../models/anhChuongModel.php';

// Kiểm tra tham số id_chuong
if (!isset($_GET['id_chuong'])) {
    die("Lỗi: Không tìm thấy ID chương.");
}

$id_chuong = $_GET['id_chuong']; // Lấy ID chương từ URL

$chapterController = new ChapterController($conn);
$anhChuongModel = new AnhChuongModel($conn);

// Lấy thông tin chương
$chuong = $chapterController->layThongTinChapter($id_chuong);

// Lấy danh sách ảnh của chương
$anh_chuongs = $anhChuongModel->layDanhSachAnh($id_chuong);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($chuong['tieu_de']); ?></title>
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

        .chapter-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .chapter-images {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .chapter-images img {
            width: 100%;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
        <h1 class="chapter-title"><?php echo htmlspecialchars($chuong['tieu_de']); ?></h1>
        <div class="chapter-images">
            <?php while ($anh = $anh_chuongs->fetch_assoc()): ?>
                <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($anh['duong_dan_anh']); ?>?t=<?php echo time(); ?>" alt="Trang <?php echo $anh['so_trang']; ?>">
            <?php endwhile; ?>
        </div>
        <a href="../truyen/chiTietTruyen.php?id_truyen=<?php echo $chuong['id_truyen']; ?>" class="back-link">Quay lại danh sách chương</a>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>