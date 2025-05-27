<?php
session_start();
require_once 'app/config/connect.php';

// Lấy 10 truyện mới nhất cho slider
$sqlSlider = "SELECT id_truyen, ten_truyen, anh_bia FROM truyen ORDER BY id_truyen DESC LIMIT 10";
$sliderTruyen = $conn->query($sqlSlider);

// Lấy truyện mới nhất cho banner lớn
$sqlBanner = "SELECT id_truyen, ten_truyen, anh_bia, mo_ta FROM truyen ORDER BY id_truyen DESC LIMIT 1";
$bannerTruyen = $conn->query($sqlBanner)->fetch_assoc();

// Lấy danh sách banner truyện nổi bật (slider tự động)
$sqlBannerList = "SELECT id_truyen, ten_truyen, anh_bia, mo_ta FROM truyen WHERE trang_thai = 'Nổi bật' ORDER BY id_truyen DESC LIMIT 5";
$bannerTruyenList = $conn->query($sqlBannerList);

// Lấy top 5 truyện xem nhiều nhất
$sqlTopView = "SELECT t.*, 
                      COUNT(c.id_chuong) as so_chuong,
                      ROUND(AVG(r.so_sao), 1) as diem_danh_gia,
                      COUNT(DISTINCT r.id_nguoidung) as luot_danh_gia
               FROM truyen t
               LEFT JOIN chuong c ON t.id_truyen = c.id_truyen
               LEFT JOIN ratings r ON t.id_truyen = r.id_truyen
               GROUP BY t.id_truyen
               ORDER BY t.luot_xem DESC
               LIMIT 5";
$resultTopView = $conn->query($sqlTopView);

// Lấy top 5 truyện đánh giá cao nhất
$sqlTopRating = "SELECT t.*, 
                       COUNT(c.id_chuong) as so_chuong,
                       ROUND(AVG(r.so_sao), 1) as diem_danh_gia,
                       COUNT(DISTINCT r.id_nguoidung) as luot_danh_gia
                FROM truyen t
                LEFT JOIN chuong c ON t.id_truyen = c.id_truyen
                LEFT JOIN ratings r ON t.id_truyen = r.id_truyen
                GROUP BY t.id_truyen
                HAVING diem_danh_gia IS NOT NULL
                ORDER BY diem_danh_gia DESC, luot_danh_gia DESC
                LIMIT 5";
$resultTopRating = $conn->query($sqlTopRating);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/trangChu.css">
    <style>
        .rankings-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }

        .ranking-section {
            background: #fff;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .ranking-title {
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #007bff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .view-all {
            font-size: 0.9rem;
            color: #007bff;
            text-decoration: none;
        }

        .ranking-list {
            list-style: none;
            padding: 0;
        }

        .ranking-item {
            display: flex;
            align-items: center;
            padding: 0.8rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }

        .ranking-item:hover {
            background-color: #f8f9fa;
        }

        .rank-number {
            font-size: 1.2rem;
            font-weight: bold;
            color: #007bff;
            width: 30px;
            text-align: center;
        }

        .rank-number.top-1 { color: #ffd700; }
        .rank-number.top-2 { color: #c0c0c0; }
        .rank-number.top-3 { color: #cd7f32; }

        .novel-cover {
            width: 50px;
            height: 70px;
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
            display: block;
        }

        .novel-stats {
            font-size: 0.8rem;
            color: #666;
        }

        .rating-stars {
            color: #ffd700;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include 'app/views/shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <?php if (!empty($_GET['success'])): ?>
            <p class="success-message">Thêm truyện thành công!</p>
        <?php endif; ?>

        <!-- Component Truyện Đề Cử -->
        <?php include 'components/recommended-novels.php'; ?>

        <!-- Slider truyện mới thêm -->
        <div class="truyen-slider-row">
            <div class="slider-wrapper">
                <?php if ($sliderTruyen && $sliderTruyen->num_rows > 0): ?>
                    <?php while($row = $sliderTruyen->fetch_assoc()): ?>
                        <div class="slider-item">
                            <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=<?= (int)$row['id_truyen'] ?>">
                                <img src="/Wed_Doc_Truyen/<?= $row['anh_bia'] ?: 'assets/images/default-cover.jpg' ?>" alt="<?= htmlspecialchars($row['ten_truyen']) ?>">
                                <div class="slider-title"><?= htmlspecialchars($row['ten_truyen']) ?></div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Xếp hạng truyện -->
        <div class="rankings-container">
            <!-- Top lượt xem -->
            <div class="ranking-section">
                <div class="ranking-title">
                    <h3>Top Lượt Xem</h3>
                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/xepHangTruyen.php" class="view-all">Xem tất cả</a>
                </div>
                <ul class="ranking-list">
                    <?php 
                    $rank = 1;
                    while ($truyen = $resultTopView->fetch_assoc()): 
                    ?>
                        <li class="ranking-item">
                            <span class="rank-number <?php echo $rank <= 3 ? 'top-' . $rank : ''; ?>"><?php echo $rank; ?></span>
                            <img src="/Wed_Doc_Truyen/<?php echo $truyen['anh_bia'] ?: 'assets/images/default-cover.jpg'; ?>" alt="<?php echo htmlspecialchars($truyen['ten_truyen']); ?>" class="novel-cover">
                            <div class="novel-info">
                                <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=<?php echo $truyen['id_truyen']; ?>" class="novel-title">
                                    <?php echo htmlspecialchars($truyen['ten_truyen']); ?>
                                </a>
                                <div class="novel-stats">
                                    <div>👁 <?php echo number_format($truyen['luot_xem']); ?> lượt xem</div>
                                    <?php if ($truyen['diem_danh_gia']): ?>
                                        <div class="rating-stars">⭐ <?php echo $truyen['diem_danh_gia']; ?>/5</div>
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

            <!-- Top đánh giá -->
            <div class="ranking-section">
                <div class="ranking-title">
                    <h3>Top Đánh Giá</h3>
                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/xepHangTruyen.php" class="view-all">Xem tất cả</a>
                </div>
                <ul class="ranking-list">
                    <?php 
                    $rank = 1;
                    while ($truyen = $resultTopRating->fetch_assoc()): 
                    ?>
                        <li class="ranking-item">
                            <span class="rank-number <?php echo $rank <= 3 ? 'top-' . $rank : ''; ?>"><?php echo $rank; ?></span>
                            <img src="/Wed_Doc_Truyen/<?php echo $truyen['anh_bia'] ?: 'assets/images/default-cover.jpg'; ?>" alt="<?php echo htmlspecialchars($truyen['ten_truyen']); ?>" class="novel-cover">
                            <div class="novel-info">
                                <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=<?php echo $truyen['id_truyen']; ?>" class="novel-title">
                                    <?php echo htmlspecialchars($truyen['ten_truyen']); ?>
                                </a>
                                <div class="novel-stats">
                                    <div class="rating-stars">⭐ <?php echo $truyen['diem_danh_gia']; ?>/5 (<?php echo $truyen['luot_danh_gia']; ?> đánh giá)</div>
                                    <div>👁 <?php echo number_format($truyen['luot_xem']); ?> lượt xem</div>
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

        <!-- Banner truyện mới nhất -->
        <?php if ($bannerTruyen): ?>
        <div class="banner-truyen">
            <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=<?= (int)$bannerTruyen['id_truyen'] ?>">
                <img class="banner-img" src="/Wed_Doc_Truyen/<?= $bannerTruyen['anh_bia'] ?: 'assets/images/default-cover.jpg' ?>" alt="<?= htmlspecialchars($bannerTruyen['ten_truyen']) ?>">
                <div class="banner-info">
                    <h2><?= htmlspecialchars($bannerTruyen['ten_truyen']) ?></h2>
                    <p><?= htmlspecialchars(mb_strimwidth($bannerTruyen['mo_ta'], 0, 120, "...")) ?></p>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <!-- Banner truyện mới nổi bật (slider tự động) -->
        <?php if ($bannerTruyenList && $bannerTruyenList->num_rows > 0): ?>
        <div class="banner-truyen-slider">
            <?php $i = 0; foreach ($bannerTruyenList as $banner): ?>
            <div class="banner-truyen-slide<?= $i === 0 ? ' active' : '' ?>">
                <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=<?= (int)$banner['id_truyen'] ?>">
                    <img class="banner-img" src="/Wed_Doc_Truyen/<?= $banner['anh_bia'] ?: 'assets/images/default-cover.jpg' ?>" alt="<?= htmlspecialchars($banner['ten_truyen']) ?>">
                    <div class="banner-info">
                        <h2><?= htmlspecialchars($banner['ten_truyen']) ?></h2>
                        <p><?= htmlspecialchars(mb_strimwidth($banner['mo_ta'], 0, 120, "...")) ?></p>
                    </div>
                </a>
            </div>
            <?php $i++; endforeach; ?>
        </div>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.banner-truyen-slide');
        let current = 0;
        if (slides.length > 1) {
            setInterval(() => {
                slides[current].classList.remove('active');
                current = (current + 1) % slides.length;
                slides[current].classList.add('active');
            }, 3000);
        }
    });
    </script>
</body>
</html>