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
    <?php include_once __DIR__ . '/app/views/shares/header.php'; ?>

    <div class="homepage-banner-carousel">
        <div class="carousel-track">
            <img src="/Wed_Doc_Truyen/wedtruyen/assets/img/banner1.jpg" alt="Banner 1" class="carousel-img">
            <img src="/Wed_Doc_Truyen/wedtruyen/assets/img/banner2.jpg" alt="Banner 2" class="carousel-img">
            <img src="/Wed_Doc_Truyen/wedtruyen/assets/img/banner3.jpg" alt="Banner 3" class="carousel-img">
        </div>
        <div class="carousel-dots">
            <span class="dot active"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>
    </div>

    <style>
    .homepage-banner-carousel {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto 30px auto;
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    }
    .carousel-track {
        display: flex;
        transition: transform 0.7s cubic-bezier(.77,0,.18,1);
        will-change: transform;
    }
    .carousel-img {
        width: 100%;
        min-width: 100%;
        max-height: 300px;
        object-fit: cover;
        user-select: none;
        pointer-events: none;
    }
    .carousel-dots {
        position: absolute;
        left: 0; right: 0; bottom: 12px;
        display: flex;
        justify-content: center;
        gap: 8px;
    }
    .carousel-dots .dot {
        width: 12px; height: 12px;
        border-radius: 50%;
        background: #fff;
        opacity: 0.6;
        border: 1.5px solid #007bff;
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .carousel-dots .dot.active {
        opacity: 1;
        background: #007bff;
    }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const track = document.querySelector('.carousel-track');
        const dots = document.querySelectorAll('.carousel-dots .dot');
        const total = 3;
        let current = 0;
        let timer = null;

        function goTo(index) {
            current = index;
            track.style.transform = `translateX(-${index * 100}%)`;
            dots.forEach((d, i) => d.classList.toggle('active', i === index));
        }

        function next() {
            goTo((current + 1) % total);
        }

        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => {
                goTo(i);
                resetAuto();
            });
        });

        function resetAuto() {
            clearInterval(timer);
            timer = setInterval(next, 4000);
        }

        resetAuto();
        goTo(0);
    });
    </script>

    <!-- Nội dung chính -->
    <div class="content">
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <p style="color: green; text-align: center;">Thêm truyện thành công!</p>
        <?php endif; ?>

        <h1 style="text-align: center;">Danh Sách Truyện</h1>
        <div class="truyen-list">
            <?php
            // Lấy danh sách truyện từ cơ sở dữ liệu
            $sql = "SELECT * FROM truyen";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='truyen-item'>";
                    echo "<a href='/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=" . $row['id_truyen'] . "'>";
                    echo "<img src='/Wed_Doc_Truyen/" . htmlspecialchars($row['anh_bia']) . "' alt='Ảnh bìa'>";
                    echo "</a>";
                    echo "<h3>" . htmlspecialchars($row['ten_truyen']) . "</h3>";
                    echo "</div>";
                }
            } else {
                echo "<p style='text-align: center;'>Không có truyện nào để hiển thị.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'app/views/shares/footer.php'; ?>
</body>
</html>
