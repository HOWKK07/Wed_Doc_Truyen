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

    <!-- Banner -->
    <div class="banner">
        <div class="banner-slider">
            <img src="/Wed_Doc_Truyen/wedtruyen/assets/images/banner1.jpg" alt="Banner 1" class="banner-image">
            <img src="/Wed_Doc_Truyen/wedtruyen/assets/images/banner2.jpg" alt="Banner 2" class="banner-image">
            <img src="/Wed_Doc_Truyen/wedtruyen/assets/images/banner3.jpg" alt="Banner 3" class="banner-image">
        </div>
        <button class="banner-btn prev">&lt;</button>
        <button class="banner-btn next">&gt;</button>
    </div>

    <!-- Nội dung chính -->
    <div class="content">
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <p style="color: green; text-align: center;">Thêm truyện thành công!</p>
        <?php endif; ?>

        <h1 style="text-align: center;">Danh Sách Truyện</h1>
        <div class="truyen-list">
            <?php
            // Lấy danh sách truyện từ cơ sở dữ liệu với thông tin chapter mới nhất
            $sql = "SELECT t.*, 
                    (SELECT MAX(ngay_cap_nhat) FROM chuong WHERE id_truyen = t.id_truyen) as chapter_moi_nhat,
                    (SELECT so_chuong FROM chuong WHERE id_truyen = t.id_truyen ORDER BY so_chuong DESC LIMIT 1) as chuong_moi_nhat,
                    (SELECT luot_xem FROM truyen WHERE id_truyen = t.id_truyen) as luot_xem
                    FROM truyen t";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='truyen-item'>";
                    
                    // Tính thời gian đã trôi qua
                    $time_ago = '';
                    $time_diff = null;
                    if ($row['chapter_moi_nhat']) {
                        $chapter_time = strtotime($row['chapter_moi_nhat']);
                        $current_time = time();
                        $time_diff = $current_time - $chapter_time;
                        
                        if ($time_diff < 3600) {
                            $mins = floor($time_diff / 60);
                            $time_ago = $mins . ' Phút Trước';
                        } elseif ($time_diff < 86400) {
                            $hours = floor($time_diff / 3600);
                            $time_ago = $hours . ' Giờ Trước';
                        } else {
                            $days = floor($time_diff / 86400);
                            $time_ago = $days . ' Ngày Trước';
                        }
                    }

                    // Hiển thị badge thời gian và HOT
                    if ($time_diff !== null && $time_diff < 86400) {
                        echo "<div class='update-info'>";
                        echo "<span class='time-badge'>" . $time_ago . "</span>";
                        echo "<span class='hot-badge'>Hot</span>";
                        echo "</div>";
                    } else if ($time_ago) {
                        echo "<div class='update-info'>";
                        echo "<span class='time-badge'>" . $time_ago . "</span>";
                        echo "</div>";
                    }

                    echo "<a href='/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=" . $row['id_truyen'] . "'>";
                    echo "<img src='/Wed_Doc_Truyen/" . htmlspecialchars($row['anh_bia']) . "' alt='Ảnh bìa'>";
                    echo "</a>";
                    echo "<div class='truyen-info'>";
                    echo "<h3>" . htmlspecialchars($row['ten_truyen']) . "</h3>";
                    if ($row['chuong_moi_nhat']) {
                        echo "<div class='chapter-info'>Chapter " . $row['chuong_moi_nhat'] . "</div>";
                    }
                    echo "<div class='view-count'>" . number_format($row['luot_xem']) . " lượt đọc</div>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p style='text-align: center;'>Không có truyện nào để hiển thị.</p>";
            }
            ?>
        </div>

        <!-- Bảng xếp hạng -->
        <div class="ranking-section">
            <h2>Bảng Xếp Hạng Truyện</h2>
            <div class="ranking-container">
                <?php
                // Lấy top 5 truyện có lượt xem cao nhất
                $sql_ranking = "SELECT id_truyen, ten_truyen, anh_bia, luot_xem 
                              FROM truyen 
                              ORDER BY luot_xem DESC 
                              LIMIT 6";
                $result_ranking = $conn->query($sql_ranking);

                if ($result_ranking && $result_ranking->num_rows > 0) {
                    $rank = 1;
                    while ($row_ranking = $result_ranking->fetch_assoc()) {
                        echo "<div class='ranking-item rank-" . $rank . "'>";
                        echo "<div class='rank-number'>#" . $rank . "</div>";
                        echo "<a href='/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=" . $row_ranking['id_truyen'] . "'>";
                        echo "<img src='/Wed_Doc_Truyen/" . htmlspecialchars($row_ranking['anh_bia']) . "' alt='Ảnh bìa'>";
                        echo "</a>";
                        echo "<div class='ranking-info'>";
                        echo "<h3>" . htmlspecialchars($row_ranking['ten_truyen']) . "</h3>";
                        echo "<p>" . number_format($row_ranking['luot_xem']) . " lượt đọc</p>";
                        echo "</div>";
                        echo "</div>";
                        $rank++;
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'app/views/shares/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.querySelector('.banner-slider');
            const prevBtn = document.querySelector('.banner-btn.prev');
            const nextBtn = document.querySelector('.banner-btn.next');
            let currentSlide = 0;

            function updateSlider() {
                slider.style.transform = `translateX(-${currentSlide * 33.333}%)`;
            }

            prevBtn.addEventListener('click', () => {
                currentSlide = (currentSlide - 1 + 3) % 3;
                updateSlider();
            });

            nextBtn.addEventListener('click', () => {
                currentSlide = (currentSlide + 1) % 3;
                updateSlider();
            });

            // Auto slide every 5 seconds
            setInterval(() => {
                currentSlide = (currentSlide + 1) % 3;
                updateSlider();
            }, 5000);
        });
    </script>
</body>
</html>
