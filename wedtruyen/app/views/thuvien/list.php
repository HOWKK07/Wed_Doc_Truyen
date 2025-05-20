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
                    <div class="history-card">
                        <div class="card-img">
                            <!-- Nút X để xóa -->
                            <button class="delete-btn" onclick="removeFromLibrary(<?php echo htmlspecialchars($row['id_truyen']); ?>)">×</button>
                            <a href="../truyen/chiTietTruyen.php?id_truyen=<?php echo $row['id_truyen']; ?>">
                                <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($row['anh_bia']); ?>" alt="<?php echo htmlspecialchars($row['ten_truyen']); ?>">
                                <div class="chapter-badge">
                                    <?php echo $row['max_so_chuong']; ?>
                                </div>
                                <?php if ($row['trang_thai'] == 'Hoàn thành'): ?>
                                    <div class="status-badge">HOÀN TẤT</div>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="card-info">
                            <div class="card-title"><?php echo htmlspecialchars($row['ten_truyen']); ?></div>
                            <div class="card-desc">
                                <?php if ($row['id_chuong']): ?>
                                    <span>Bạn đã đọc đến chương <?php echo htmlspecialchars($row['so_chuong']); ?>: <?php echo htmlspecialchars($row['tieu_de']); ?></span><br>
                                    <span class="read-time">Lúc <?php echo date('d/m/Y H:i', strtotime($row['thoi_gian_doc'])); ?></span>
                                <?php else: ?>
                                    <span>Bạn chưa đọc truyện này</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($row['id_chuong']): ?>
                                <a class="continue-btn" href="../chapter/docChapter.php?id_chuong=<?php echo $row['id_chuong']; ?>">&#187;Xem tiếp</a>
                            <?php endif; ?>
                        </div>
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