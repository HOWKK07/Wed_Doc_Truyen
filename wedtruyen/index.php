<?php
session_start();
require_once 'app/config/connect.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ</title>
     <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/trangChu.css">
</head>
<body>
    <!-- Header -->
    <?php include 'app/views/shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <p style="color: green; text-align: center;">Thêm truyện thành công!</p>
        <?php endif; ?>

        <h1 style="text-align: center;">Danh Sách Truyện</h1>
        <div class="truyen-list">
            <?php
            // Lấy danh sách truyện từ cơ sở dữ liệu
            $sql = "SELECT * FROM truyen";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='truyen-item'>";
                    echo "<a href='/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=" . $row['id_truyen'] . "'>";
                    echo "<img src='/Wed_Doc_Truyen/" . htmlspecialchars($row['anh_bia']) . "' alt='Ảnh bìa'>";
                    echo "</a>";
                    echo "<h3>" . htmlspecialchars($row['ten_truyen']) . "</h3>";
                    echo "</div>";
                }
            } else {
                echo "<p style='text-align: center;'>Không có truyện nào để hiển thị.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'app/views/shares/footer.php'; ?>
</body>
</html>
