<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/chapterController.php';

// Kiểm tra id_truyen
if (!isset($_GET['id_truyen']) || empty($_GET['id_truyen'])) {
    die("Lỗi: Không tìm thấy ID truyện.");
}

$id_truyen = (int)$_GET['id_truyen'];

// Lấy tên truyện từ database
$ten_truyen = '';
$stmt = $conn->prepare("SELECT ten_truyen FROM truyen WHERE id_truyen = ?");
$stmt->bind_param("i", $id_truyen);
$stmt->execute();
$stmt->bind_result($ten_truyen);
$stmt->fetch();
$stmt->close();

$controller = new ChapterController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $controller->themChapter();
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
    <title>Thêm Chapter</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/chapter/addChapter.css">
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <form action="" method="POST">
            <h1>Thêm Chapter</h1>
            <p><strong>Truyện:</strong> <?php echo htmlspecialchars($ten_truyen); ?></p>
            <input type="hidden" name="id_truyen" value="<?php echo $id_truyen; ?>">
            <label for="so_chuong">Số chương:</label>
            <input type="number" id="so_chuong" name="so_chuong" required>

            <label for="tieu_de">Tiêu đề:</label>
            <input type="text" id="tieu_de" name="tieu_de" required>

            <button type="submit">Thêm Chapter</button>
        </form>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>