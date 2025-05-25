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