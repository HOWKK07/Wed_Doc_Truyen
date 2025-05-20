<?php
session_start();
require_once '../../config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: ../taiKhoan/login.php");
    exit();
}

$id_nguoidung = $_SESSION['user']['id_nguoidung'];

// Lấy lịch sử đọc: mỗi truyện chỉ lấy chương mới nhất đã đọc + chương mới nhất hiện tại
$sql = "SELECT 
            t.id_truyen, t.ten_truyen, t.anh_bia, t.trang_thai,
            c.id_chuong, c.so_chuong, c.tieu_de,
            ls.thoi_gian_doc,
            (SELECT MAX(so_chuong) FROM chuong WHERE id_truyen = t.id_truyen) AS max_so_chuong
        FROM lich_su_doc ls
        JOIN chuong c ON ls.id_chuong = c.id_chuong
        JOIN truyen t ON c.id_truyen = t.id_truyen
        INNER JOIN (
            SELECT t2.id_truyen, MAX(ls2.thoi_gian_doc) AS max_time
            FROM lich_su_doc ls2
            JOIN chuong c2 ON ls2.id_chuong = c2.id_chuong
            JOIN truyen t2 ON c2.id_truyen = t2.id_truyen
            WHERE ls2.id_nguoidung = ?
            GROUP BY t2.id_truyen
        ) newest ON t.id_truyen = newest.id_truyen AND ls.thoi_gian_doc = newest.max_time
        WHERE ls.id_nguoidung = ?
        ORDER BY ls.thoi_gian_doc DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_nguoidung, $id_nguoidung);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử đọc</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/truyen/lichSuDoc.css">
</head>
<body>
    <?php include '../shares/header.php'; ?>
    <div class="container">
        <h1>Lịch sử đọc</h1>
        <div class="history-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="history-card">
                        <div class="card-img">
                            <a href="chiTietTruyen.php?id_truyen=<?php echo $row['id_truyen']; ?>">
                                <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($row['anh_bia']); ?>" alt="<?php echo htmlspecialchars($row['ten_truyen']); ?>">
                                <div class="chapter-badge">
                                    <?php echo $row['max_so_chuong']; ?>
                                </div>
                                <?php if ($row['trang_thai'] == 'Hoàn thành' || $row['trang_thai'] == 'Hoàn thành'): ?>
                                    <div class="status-badge">HOÀN TẤT</div>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="card-info">
                            <div class="card-title"><?php echo htmlspecialchars($row['ten_truyen']); ?></div>
                            <div class="card-desc">
                                <span>Bạn đã đọc đến chương <?php echo htmlspecialchars($row['so_chuong']); ?>: <?php echo htmlspecialchars($row['tieu_de']); ?></span><br>
                                <span class="read-time">Lúc <?php echo date('d/m/Y H:i', strtotime($row['thoi_gian_doc'])); ?></span>
                            </div>
                            <a class="continue-btn" href="../chapter/docChapter.php?id_chuong=<?php echo $row['id_chuong']; ?>">&#187;Xem tiếp</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-history">Bạn chưa đọc chương nào.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php include '../shares/footer.php'; ?>
</body>
</html>