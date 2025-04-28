<?php
session_start(); // Bắt đầu session
require_once 'config/connect.php'; // Kết nối cơ sở dữ liệu
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Truyện</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        .truyen-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .truyen-item {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            width: 200px;
            text-align: center;
            background-color: #f9f9f9;
        }

        .truyen-item img {
            width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .add-box {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 200px;
            height: 200px;
            border: 2px dashed #007bff;
            border-radius: 5px;
            cursor: pointer;
            color: #007bff;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
        }

        .add-box:hover {
            background-color: #e6f7ff;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'views/shares/header.php'; ?>

    <!-- User Info -->


    <!-- Nội dung chính -->
    <div class="content">
        <h1>Danh Sách Truyện</h1>
        <div class="truyen-list">
            <?php
            // Lấy danh sách truyện từ cơ sở dữ liệu
            $sql = "SELECT * FROM truyen";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='truyen-item'>";
                    echo "<img src='uploads/anhbia/" . htmlspecialchars($row['anh_bia']) . "' alt='" . htmlspecialchars($row['ten_truyen']) . "' class='truyen-anhbia'>";
                    echo "<p><strong>" . htmlspecialchars($row['ten_truyen']) . "</strong></p>";
                    echo "<a href='views/truyen/chiTietTruyen.php?id_truyen=" . $row['id_truyen'] . "'>Đọc truyện</a>";
                    echo "</div>";
                }
            } else {
                echo "<a href='views/truyen/add.php' class='add-box'>+</a>";
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'views/shares/footer.php'; ?>
</body>
</html>

<?php
$conn->close(); // Đóng kết nối cơ sở dữ liệu
?>
