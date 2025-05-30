<?php
session_start();
require_once 'app/config/connect.php';

// Lấy tham số lọc từ URL
$theloai_filter = isset($_GET['theloai']) ? (int)$_GET['theloai'] : 0;
$loaitruyen_filter = isset($_GET['loaitruyen']) ? (int)$_GET['loaitruyen'] : 0;

// Lấy 10 truyện mới nhất cho slider
$sqlSlider = "SELECT id_truyen, ten_truyen, anh_bia FROM truyen ORDER BY id_truyen DESC LIMIT 10";
$sliderTruyen = $conn->query($sqlSlider);

// Lấy truyện nổi bật cho banner
$sqlBanner = "SELECT id_truyen, ten_truyen, anh_bia, mo_ta FROM truyen ORDER BY luot_xem DESC LIMIT 3";
$bannerTruyenList = $conn->query($sqlBanner);

// Lấy thống kê
$sqlStats = "SELECT 
    (SELECT COUNT(*) FROM truyen) as total_truyen,
    (SELECT COUNT(*) FROM chuong) as total_chuong,
    (SELECT COUNT(*) FROM nguoidung) as total_nguoidung,
    (SELECT SUM(luot_xem) FROM truyen) as total_luotxem";
$stats = $conn->query($sqlStats)->fetch_assoc();

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

// Lấy danh sách thể loại để hiển thị filter
$sqlTheLoai = "SELECT * FROM theloai ORDER BY ten_theloai ASC LIMIT 8";
$theLoaiList = $conn->query($sqlTheLoai);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Đọc Truyện - Trang Chủ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/trangChu.css">
</head>
<body>
    <!-- Header -->
    <?php include 'app/views/shares/header.php'; ?>

    <div class="main-container">
        <!-- Success Message -->
        <?php if (!empty($_GET['success'])): ?>
            <div class="success-message" id="successMessage">
                <i class="fas fa-check-circle"></i>
                <span>Thêm truyện thành công!</span>
            </div>
        <?php endif; ?>



        <!-- Featured Banner (chỉ hiển thị khi không có filter) -->
        <?php if (!$theloai_filter && !$loaitruyen_filter && $bannerTruyenList && $bannerTruyenList->num_rows > 0): ?>
        <div class="featured-banner">
            <?php 
            $banners = [];
            while($banner = $bannerTruyenList->fetch_assoc()) {
                $banners[] = $banner;
            }
            foreach($banners as $index => $banner): 
            ?>
            <div class="banner-slide <?= $index === 0 ? 'active' : '' ?>">
                <img src="/Wed_Doc_Truyen/<?= $banner['anh_bia'] ?: 'assets/images/default-cover.jpg' ?>" alt="<?= htmlspecialchars($banner['ten_truyen']) ?>">
                <div class="banner-content">
                    <h2 class="banner-title"><?= htmlspecialchars($banner['ten_truyen']) ?></h2>
                    <p class="banner-description"><?= htmlspecialchars(mb_strimwidth($banner['mo_ta'] ?? '', 0, 200, "...")) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="banner-indicators">
                <?php for($i = 0; $i < count($banners); $i++): ?>
                <div class="indicator <?= $i === 0 ? 'active' : '' ?>" onclick="showSlide(<?= $i ?>)"></div>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Access Slider (chỉ hiển thị khi không có filter) -->
        <?php if (!$theloai_filter && !$loaitruyen_filter): ?>
        <section class="quick-access">
            <h2 class="section-title">
                <div class="section-icon">
                    <i class="fas fa-fire"></i>
                </div>
                Truyện Mới Cập Nhật
            </h2>
            
            <div class="quick-slider">
                <?php 
                mysqli_data_seek($sliderTruyen, 0);
                if ($sliderTruyen && $sliderTruyen->num_rows > 0): 
                    while($row = $sliderTruyen->fetch_assoc()): 
                ?>
                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=<?= (int)$row['id_truyen'] ?>" class="quick-item">
                        <div class="quick-thumb">
                            <img src="/Wed_Doc_Truyen/<?= $row['anh_bia'] ?: 'assets/images/default-cover.jpg' ?>" alt="<?= htmlspecialchars($row['ten_truyen']) ?>">
                        </div>
                        <div class="quick-title"><?= htmlspecialchars($row['ten_truyen']) ?></div>
                    </a>
                <?php 
                    endwhile;
                endif; 
                ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-title">Lọc theo thể loại:</div>
            <div class="filter-chips">
                <a href="/Wed_Doc_Truyen/wedtruyen/index.php" class="filter-chip <?= !$theloai_filter && !$loaitruyen_filter ? 'active' : '' ?>">Tất cả</a>
                <?php if ($theLoaiList && $theLoaiList->num_rows > 0): ?>
                    <?php while($theloai = $theLoaiList->fetch_assoc()): ?>
                        <a href="/Wed_Doc_Truyen/wedtruyen/index.php?theloai=<?= $theloai['id_theloai'] ?>" 
                           class="filter-chip <?= $theloai_filter == $theloai['id_theloai'] ? 'active' : '' ?>">
                            <?= htmlspecialchars($theloai['ten_theloai']) ?>
                        </a>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Story Grid -->
        <section>
            <h2 class="section-title">
                <div class="section-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <?= $filter_title ?: 'Tất Cả Truyện' ?>
            </h2>
            
            <div class="story-grid">
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
                       ORDER BY t.id_truyen DESC
                       LIMIT 24";

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
                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=<?= $id_truyen ?>" class="story-card">
                        <div class="story-thumb">
                            <img src="/Wed_Doc_Truyen/<?= $anh_bia ?: 'assets/images/default-cover.jpg' ?>" alt="Ảnh bìa <?= $ten_truyen ?>" loading="lazy">
                            <div class="story-badges">
                                <?php if ($danh_gia > 0): ?>
                                <div class="badge rating-badge">⭐ <?= $danh_gia ?></div>
                                <?php endif; ?>
                                <?php if (!empty($trang_thai)): ?>
                                <div class="badge status-badge">
                                    <?= strtolower($trang_thai) === 'hoàn thành' ? 'Hoàn thành' : $trang_thai ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="chapter-counter"><?= $max_chapter ?></div>
                        </div>
                        <div class="story-info">
                            <h3 class="story-title"><?= $ten_truyen ?></h3>
                            <div class="story-meta">
                                <span class="story-views">
                                    <i class="fas fa-eye"></i>
                                    <?= number_format($luot_xem) ?> lượt xem
                                </span>
                            </div>
                        </div>
                    </a>
                <?php
                    endwhile;
                else:
                ?>
                    <div class="no-data">
                        <div class="no-data-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="no-data-text">Không có truyện nào để hiển thị</div>
                        <div class="no-data-subtitle">Hãy thử tìm kiếm với từ khóa khác hoặc chọn thể loại khác</div>
                    </div>
                <?php
                endif;
                ?>
            </div>

            <!-- Load More Button -->
            <div style="text-align: center; margin-top: 40px;">
                <button class="fab" style="position: relative; width: auto; height: auto; padding: 15px 30px; border-radius: 25px; font-size: 1rem;" onclick="loadMoreStories()">
                    <i class="fas fa-plus" style="margin-right: 10px;"></i>
                    Xem thêm truyện
                </button>
            </div>
        </section>
    </div>

    <!-- Floating Action Buttons -->
    <div class="fab-container">
        <button class="fab" title="Lên đầu trang" onclick="scrollToTop()">
            <i class="fas fa-arrow-up"></i>
        </button>
        <button class="fab" title="Tìm kiếm" onclick="focusSearch()">
            <i class="fas fa-search"></i>
        </button>
    </div>

    <!-- Footer -->
    <?php include 'app/views/shares/footer.php'; ?>

    <script src="/Wed_Doc_Truyen/wedtruyen/assets/js/trangChu.js"></script>
</body>
</html>