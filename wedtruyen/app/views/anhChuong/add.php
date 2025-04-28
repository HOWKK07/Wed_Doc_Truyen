<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/anhChuongController.php';

if (!isset($_GET['id_chuong']) || empty($_GET['id_chuong'])) {
    die("Lỗi: Không tìm thấy ID chương.");
}

$id_chuong = $_GET['id_chuong'];
$controller = new AnhChuongController($conn);

$so_trang_bat_dau = isset($_GET['so_trang_bat_dau']) ? (int)$_GET['so_trang_bat_dau'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $controller->themNhieuAnh();
    } catch (Exception $e) {
        echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Ảnh</title>
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
        <form action="" method="POST" enctype="multipart/form-data">
            <h1>Thêm Ảnh</h1>
            <input type="hidden" name="id_chuong" value="<?php echo htmlspecialchars($id_chuong); ?>">

            <label for="so_trang">Số trang bắt đầu:</label>
            <input type="number" id="so_trang" name="so_trang" value="<?php echo $so_trang_bat_dau; ?>" required>

            <label for="anh">Chọn ảnh (có thể chọn nhiều ảnh):</label>
            <input type="file" id="anh" name="anh[]" accept="image/*" multiple required>

            <button type="submit">Thêm Ảnh</button>
        </form>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>