<?php
session_start();
require_once '../../config/connect.php';

// Lấy danh sách truyện xếp hạng theo lượt xem
$sql_luot_xem = "SELECT t.*, 
                        COUNT(c.id_chuong) as so_chuong,
                        ROUND(AVG(r.so_sao), 1) as diem_danh_gia,
                        COUNT(DISTINCT r.id_nguoidung) as luot_danh_gia
                 FROM truyen t
                 LEFT JOIN chuong c ON t.id_truyen = c.id_truyen
                 LEFT JOIN ratings r ON t.id_truyen = r.id_truyen
                 GROUP BY t.id_truyen
                 ORDER BY t.luot_xem DESC
                 LIMIT 10";

$result_luot_xem = $conn->query($sql_luot_xem);

// Lấy danh sách truyện xếp hạng theo đánh giá
$sql_danh_gia = "SELECT t.*, 
                        COUNT(c.id_chuong) as so_chuong,
                        ROUND(AVG(r.so_sao), 1) as diem_danh_gia,
                        COUNT(DISTINCT r.id_nguoidung) as luot_danh_gia
                 FROM truyen t
                 LEFT JOIN chuong c ON t.id_truyen = c.id_truyen
                 LEFT JOIN ratings r ON t.id_truyen = r.id_truyen
                 GROUP BY t.id_truyen
                 HAVING diem_danh_gia IS NOT NULL
                 ORDER BY diem_danh_gia DESC, luot_danh_gia DESC
                 LIMIT 10";

$result_danh_gia = $conn->query($sql_danh_gia);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xếp Hạng Truyện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/trangChu.css">
    <style>
        .ranking-section {
            margin: 2rem 0;
            padding: 1rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .ranking-title {
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #007bff;
        }

        .ranking-list {
            list-style: none;
            padding: 0;
        }

        .ranking-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }

        .ranking-item:hover {
            background-color: #f8f9fa;
        }

        .rank-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #007bff;
            width: 40px;
            text-align: center;
        }

        .rank-number.top-1 { color: #ffd700; }
        .rank-number.top-2 { color: #c0c0c0; }
        .rank-number.top-3 { color: #cd7f32; }

        .novel-cover {
            width: 60px;
            height: 80px;
            object-fit: cover;
            margin: 0 1rem;
            border-radius: 4px;
        }

        .novel-info {
            flex-grow: 1;
        }

        .novel-title {
            font-weight: bold;
            color: #333;
            text-decoration: none;
            margin-bottom: 0.25rem;
        }

        .novel-stats {
            font-size: 0.9rem;
            color: #666;
        }

        .rating-stars {
            color: #ffd700;
            margin-right: 0.5rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .rankings-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <div class="container">
        <div class="rankings-container">
            <!-- Xếp hạng theo lượt xem -->
            <div class="ranking-section">
                <h2 class="ranking-title">Top Lượt Xem</h2>
                <ul class="ranking-list">
                    <?php 
                    $rank = 1;
                    while ($truyen = $result_luot_xem->fetch_assoc()): 
                    ?>
                        <li class="ranking-item">
                            <span class="rank-number <?php echo $rank <= 3 ? 'top-' . $rank : ''; ?>"><?php echo $rank; ?></span>
                            <img src="/Wed_Doc_Truyen/<?php echo $truyen['anh_bia'] ?: 'assets/images/default-cover.jpg'; ?>" alt="<?php echo htmlspecialchars($truyen['ten_truyen']); ?>" class="novel-cover">
                            <div class="novel-info">
                                <a href="chiTietTruyen.php?id_truyen=<?php echo $truyen['id_truyen']; ?>" class="novel-title">
                                    <?php echo htmlspecialchars($truyen['ten_truyen']); ?>
                                </a>
                                <div class="novel-stats">
                                    <div>👁 <?php echo number_format($truyen['luot_xem']); ?> lượt xem</div>
                                    <div>📚 <?php echo $truyen['so_chuong']; ?> chương</div>
                                    <?php if ($truyen['diem_danh_gia']): ?>
                                        <div class="rating-stars">⭐ <?php echo $truyen['diem_danh_gia']; ?>/5 (<?php echo $truyen['luot_danh_gia']; ?> đánh giá)</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php 
                    $rank++;
                    endwhile; 
                    ?>
                </ul>
            </div>

            <!-- Xếp hạng theo đánh giá -->
            <div class="ranking-section">
                <h2 class="ranking-title">Top Đánh Giá</h2>
                <ul class="ranking-list">
                    <?php 
                    $rank = 1;
                    while ($truyen = $result_danh_gia->fetch_assoc()): 
                    ?>
                        <li class="ranking-item">
                            <span class="rank-number <?php echo $rank <= 3 ? 'top-' . $rank : ''; ?>"><?php echo $rank; ?></span>
                            <img src="/Wed_Doc_Truyen/<?php echo $truyen['anh_bia'] ?: 'assets/images/default-cover.jpg'; ?>" alt="<?php echo htmlspecialchars($truyen['ten_truyen']); ?>" class="novel-cover">
                            <div class="novel-info">
                                <a href="chiTietTruyen.php?id_truyen=<?php echo $truyen['id_truyen']; ?>" class="novel-title">
                                    <?php echo htmlspecialchars($truyen['ten_truyen']); ?>
                                </a>
                                <div class="novel-stats">
                                    <div class="rating-stars">⭐ <?php echo $truyen['diem_danh_gia']; ?>/5 (<?php echo $truyen['luot_danh_gia']; ?> đánh giá)</div>
                                    <div>👁 <?php echo number_format($truyen['luot_xem']); ?> lượt xem</div>
                                    <div>📚 <?php echo $truyen['so_chuong']; ?> chương</div>
                                </div>
                            </div>
                        </li>
                    <?php 
                    $rank++;
                    endwhile; 
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 