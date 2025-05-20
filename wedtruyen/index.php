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
        <?php if (!empty($_GET['success'])): ?>
            <p class="success-message">Thêm truyện thành công!</p>
        <?php endif; ?>

   
        <div class="truyen-list">
            <?php
            $q = isset($_GET['q']) ? trim($_GET['q']) : '';
            $where = '';
            if ($q !== '') {
                $where = "WHERE t.ten_truyen LIKE '%" . $conn->real_escape_string($q) . "%'";
            }
            $sql = "SELECT t.*, 
                          MAX(c.so_chuong) AS max_chapter,
                          ROUND(AVG(dg.so_sao), 1) AS danh_gia
                   FROM truyen t
                   LEFT JOIN chuong c ON t.id_truyen = c.id_truyen
                   LEFT JOIN ratings dg ON t.id_truyen = dg.id_truyen
                   $where
                   GROUP BY t.id_truyen
                   ORDER BY t.id_truyen DESC";

            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    $id_truyen = (int)$row['id_truyen'];
                    $ten_truyen = htmlspecialchars($row['ten_truyen']);
                    $anh_bia = htmlspecialchars($row['anh_bia']);
                    $max_chapter = $row['max_chapter'] ?? 0;
                    $trang_thai = $row['trang_thai'];
                    $danh_gia = $row['danh_gia'] ?? 0;
                    $luot_xem = (int)($row['luot_xem'] ?? 0);
            ?>
                <div class="truyen-item">
                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=<?= $id_truyen ?>">
                        <div class="truyen-thumb-wrapper">
                            <img src="/Wed_Doc_Truyen/<?= $anh_bia ?: 'assets/images/default-cover.jpg' ?>" alt="Ảnh bìa <?= $ten_truyen ?>" style="width:100%;height:100%;object-fit:cover;">
                            <div class="chapter-badge"><?= $max_chapter ?></div>
                            <div class="rating-badge">⭐ <?= $danh_gia > 0 ? $danh_gia : '0' ?></div>
                            <?php if (!empty($trang_thai)): ?>
                                <div class="status-badge" data-status="<?= strtolower($trang_thai) ?>">
                                    <?= strtolower($trang_thai) === 'hoàn thành' ? 'HOÀN TẤT' : strtoupper($trang_thai) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="truyen-title">
                            <h3><?= $ten_truyen ?></h3>
                            <div class="truyen-meta">
                                <span class="truyen-views">👁 <?= number_format($luot_xem) ?> lượt xem</span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php
                endwhile;
            else:
                echo "<p class='no-data'>Không có truyện nào để hiển thị.</p>";
            endif;
            ?>
        </div>
    </div>
    <!-- Footer -->
    <?php include 'app/views/shares/footer.php'; ?>
</body>
</html>