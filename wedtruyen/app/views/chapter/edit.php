<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/chapterController.php';

// Kiểm tra tham số `id_chuong`
if (!isset($_GET['id_chuong']) || empty($_GET['id_chuong'])) {
    die("Lỗi: Không tìm thấy ID chương.");
}

$id_chuong = $_GET['id_chuong'];
$id_truyen = $_GET['id_truyen'] ?? null;

$controller = new ChapterController($conn);
$chapter = $controller->layThongTinChapter($id_chuong);

if (!$chapter) {
    die("Lỗi: Không tìm thấy thông tin chương.");
}

// Xử lý cập nhật chapter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $controller->suaChapter();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Chapter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
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
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <form action="" method="POST">
            <h1>Sửa Chapter</h1>
            <?php if (isset($error_message)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <input type="hidden" name="id_chuong" value="<?php echo htmlspecialchars($chapter['id_chuong']); ?>">
            <input type="hidden" name="id_truyen" value="<?php echo htmlspecialchars($id_truyen); ?>">

            <label for="so_chuong">Số chương:</label>
            <input type="number" id="so_chuong" name="so_chuong" value="<?php echo htmlspecialchars($chapter['so_chuong']); ?>" required>

            <label for="tieu_de">Tiêu đề:</label>
            <input type="text" id="tieu_de" name="tieu_de" value="<?php echo htmlspecialchars($chapter['tieu_de']); ?>" required>

            <button type="submit">Cập Nhật</button>
        </form>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>