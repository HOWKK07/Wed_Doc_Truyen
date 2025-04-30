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
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .content {
            padding: 20px;
        }

        .truyen-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .truyen-item {
            width: 200px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
            cursor: pointer;
        }

        .truyen-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .truyen-item h3 {
            font-size: 16px;
            margin: 10px 0;
            color: #333;
        }
    </style>
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
