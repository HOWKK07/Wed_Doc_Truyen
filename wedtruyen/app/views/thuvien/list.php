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
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/thuVien/list.css">
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