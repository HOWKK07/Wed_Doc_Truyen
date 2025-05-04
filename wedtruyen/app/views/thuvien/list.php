<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/thuVienController.php';

// Kiểm tra người dùng đã đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: ../taiKhoan/login.php");
    exit();
}

$id_nguoidung = $_SESSION['user']['id_nguoidung'];
$thuVienController = new ThuVienController($conn);
$truyenList = $thuVienController->layThuVien($id_nguoidung);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện của tôi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .truyen-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .truyen-item {
            position: relative;
            width: 200px;
            text-align: center;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .truyen-item img {
            width: 100%;
            height: 270px;
            object-fit: cover;
        }

        .truyen-item h3 {
            font-size: 16px;
            margin: 10px 0;
            color: #333;
        }

        .delete-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            line-height: 25px;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }
    </style>
    <script>
        function removeFromLibrary(idTruyen) {
            if (confirm('Bạn có chắc chắn muốn xóa truyện này khỏi thư viện?')) {
                fetch('../thuvien/delete.php?id_truyen=' + idTruyen, {
                    method: 'GET'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Xóa thành công!');
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                });
            }
        }
    </script>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="container">
        <h1>Thư viện của tôi</h1>
        <div class="truyen-list">
            <?php if ($truyenList->num_rows > 0): ?>
                <?php while ($row = $truyenList->fetch_assoc()): ?>
                    <div class="truyen-item" data-id="<?php echo htmlspecialchars($row['id_truyen']); ?>">
                        <!-- Nút X để xóa -->
                        <button class="delete-btn" onclick="removeFromLibrary(<?php echo htmlspecialchars($row['id_truyen']); ?>)">×</button>
                        <a href="../truyen/chiTietTruyen.php?id_truyen=<?php echo htmlspecialchars($row['id_truyen']); ?>">
                            <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($row['anh_bia']); ?>" alt="<?php echo htmlspecialchars($row['ten_truyen']); ?>">
                        </a>
                        <h3><?php echo htmlspecialchars($row['ten_truyen']); ?></h3>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #555;">Bạn chưa thêm truyện nào vào thư viện.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>

    <?php
    // Close database resources
    $conn->close();
    ?>
</body>
</html>