<?php
session_start();
require_once 'app/config/connect.php';

// Lấy tham số lọc từ URL
$theloai_filter = isset($_GET['theloai']) ? (int)$_GET['theloai'] : 0;
$loaitruyen_filter = isset($_GET['loaitruyen']) ? (int)$_GET['loaitruyen'] : 0;

// Lấy 10 truyện mới nhất cho slider
$sqlSlider = "SELECT id_truyen, ten_truyen, anh_bia FROM truyen ORDER BY id_truyen DESC LIMIT 10";
$sliderTruyen = $conn->query($sqlSlider);

// Lấy truyện mới nhất cho banner lớn
$sqlBanner = "SELECT id_truyen, ten_truyen, anh_bia, mo_ta FROM truyen ORDER BY id_truyen DESC LIMIT 1";
$bannerTruyen = $conn->query($sqlBanner)->fetch_assoc();

// Lấy danh sách banner truyện nổi bật (slider tự động)
$sqlBannerList = "SELECT id_truyen, ten_truyen, anh_bia, mo_ta FROM truyen WHERE trang_thai = 'Nổi bật' ORDER BY id_truyen DESC LIMIT 5";
$bannerTruyenList = $conn->query($sqlBannerList);

// Lấy tên thể loại hoặc loại truyện đang được lọc
$filter_title = "";
if ($theloai_filter > 0) {
    $sql_get_theloai = "SELECT ten_theloai FROM theloai WHERE id_theloai = ?";
    $stmt = $conn->prepare($sql_get_theloai);
    $stmt->bind_param("i", $theloai_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $filter_title = "Thể loại: " . htmlspecialchars($row['ten_theloai']);
    }
} elseif ($loaitruyen_filter > 0) {
    $sql_get_loaitruyen = "SELECT ten_loai_truyen FROM loai_truyen WHERE id_loai_truyen = ?";
    $stmt = $conn->prepare($sql_get_loaitruyen);
    $stmt->bind_param("i", $loaitruyen_filter);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $filter_title = "Loại truyện: " . htmlspecialchars($row['ten_loai_truyen']);
    }
}
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

        <!-- Hiển thị tiêu đề lọc nếu có -->
        <?php if ($filter_title): ?>
            <h2 style="text-align: center; color: #333; margin: 20px 0;">
                <?php echo $filter_title; ?>
                <a href="/Wed_Doc_Truyen/wedtruyen/index.php" style="font-size: 14px; color: #007bff; margin-left: 20px;">(Xem tất cả)</a>
            </h2>
        <?php endif; ?>

        <!-- Chỉ hiển thị slider và banner khi không có lọc -->
        <?php if (!$theloai_filter && !$loaitruyen_filter): ?>
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
        <?php endif; ?>

        <div class="truyen-list">
            <?php
            $q = isset($_GET['q']) ? trim($_GET['q']) : '';
            
            // Xây dựng câu truy vấn với các điều kiện lọc
            $where_conditions = [];
            $params = [];
            $types = "";
            
            if ($q !== '') {
                $where_conditions[] = "t.ten_truyen LIKE ?";
                $params[] = "%{$q}%";
                $types .= "s";
            }
            
            if ($theloai_filter > 0) {
                $where_conditions[] = "tt.id_theloai = ?";
                $params[] = $theloai_filter;
                $types .= "i";
            }
            
            if ($loaitruyen_filter > 0) {
                $where_conditions[] = "t.id_loai_truyen = ?";
                $params[] = $loaitruyen_filter;
                $types .= "i";
            }
            
            $where = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
            
            $sql = "SELECT DISTINCT t.*, 
                          MAX(c.so_chuong) AS max_chapter,
                          ROUND(AVG(dg.so_sao), 1) AS danh_gia
                   FROM truyen t
                   LEFT JOIN chuong c ON t.id_truyen = c.id_truyen
                   LEFT JOIN ratings dg ON t.id_truyen = dg.id_truyen
                   LEFT JOIN truyen_theloai tt ON t.id_truyen = tt.id_truyen
                   $where
                   GROUP BY t.id_truyen
                   ORDER BY t.id_truyen DESC";

            if (!empty($params)) {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $conn->query($sql);
            }

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