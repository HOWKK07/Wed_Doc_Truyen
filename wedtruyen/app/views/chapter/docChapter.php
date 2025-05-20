<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/chapterController.php';
require_once '../../models/anhChuongModel.php';
require_once '../../controllers/binhLuanController.php';
require_once '../../controllers/LichSuDocController.php';

// Kiểm tra tham số id_chuong
if (!isset($_GET['id_chuong'])) {
    die("Lỗi: Không tìm thấy ID chương.");
}

$id_chuong = $_GET['id_chuong']; // Lấy ID chương từ URL

$chapterController = new ChapterController($conn);
$anhChuongModel = new AnhChuongModel($conn);
$binhLuanController = new BinhLuanController($conn);

// Lấy thông tin chương
$chuong = $chapterController->layThongTinChapter($id_chuong);

// Lấy thông tin chương trước và chương sau
$chuong['id_chuong_truoc'] = $chapterController->layChuongTruoc($id_chuong);
$chuong['id_chuong_sau'] = $chapterController->layChuongSau($id_chuong);

// Lấy danh sách ảnh của chương
$anh_chuongs = $anhChuongModel->layDanhSachAnh($id_chuong, 'ASC'); // Sắp xếp tăng dần

// Lấy danh sách bình luận của chương
$binhLuans = $binhLuanController->layBinhLuanTheoChuong($id_chuong);

if (isset($_SESSION['user'])) {
    $id_nguoidung = $_SESSION['user']['id_nguoidung'];
    luuLichSuDoc($conn, $id_nguoidung, $id_chuong);

tangLuotXemChuong1Lan1Ngay($conn, $id_nguoidung, $id_chuong);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($chuong['tieu_de']); ?></title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/chapter/docChapter.css">
</head>
<body>
    <div id="reader-container">
        <!-- Reading progress bar -->
        <div class="reading-progress" id="reading-progress"></div>

        <!-- Header with controls -->
        <header class="reader-header" id="reader-header">
            <div class="header-content">
                <a href="../truyen/chiTietTruyen.php?id_truyen=<?php echo $chuong['id_truyen']; ?>" class="back-to-comic" title="Quay lại truyện">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <div class="chapter-title">
                    <span>
                        <?php echo htmlspecialchars($chuong['ten_truyen']); ?> - 
                        <?php echo htmlspecialchars($chuong['tieu_de']); ?> 
                        (Chương <?php echo htmlspecialchars($chuong['so_chuong']); ?>)
                    </span>
                </div>
                <button class="control-btn" id="fullscreen-btn" title="Toàn màn hình">
                    <i class="fas fa-expand"></i>
                </button>
            </div>
        </header>

        <!-- Chapter container -->
        <div id="chapter-container">
            <!-- Main viewer area -->
            <div id="viewer-area">
                <?php while ($anh = $anh_chuongs->fetch_assoc()): ?>
                    <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($anh['duong_dan_anh']); ?>" 
                         alt="Trang <?php echo $anh['so_trang']; ?>" 
                         class="page-viewer" 
                         data-page="<?php echo $anh['so_trang']; ?>">
                <?php endwhile; ?>
            </div>

            <!-- Comments section -->
            <div class="comments-container">
                <h2 class="comments-title">Bình luận</h2>
                
                <div class="comment-list">
                    <?php if ($binhLuans->num_rows > 0): ?>
                        <?php while ($comment = $binhLuans->fetch_assoc()): ?>
                            <div class="comment-item">
                                <div class="comment-header">
                                    <span class="comment-user"><?php echo htmlspecialchars($comment['ten_dang_nhap']); ?></span>
                                    <span class="comment-date"><?php echo date('d/m/Y H:i', strtotime($comment['ngay_binh_luan'])); ?></span>
                                </div>
                                <div class="comment-content">
                                    <?php echo htmlspecialchars($comment['noi_dung']); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Chưa có bình luận nào.</p>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['user'])): ?>
                    <form action="../binhLuan/addChapterComment.php" method="POST" class="comment-form">
                        <input type="hidden" name="id_chuong" value="<?php echo $id_chuong; ?>">
                        <textarea name="noi_dung" class="comment-textarea" placeholder="Viết bình luận của bạn..." required></textarea>
                        <button type="submit" class="comment-submit">Gửi bình luận</button>
                    </form>
                <?php else: ?>
                    <div class="login-prompt">
                        <p>Vui lòng <a href="../taiKhoan/login.php" class="login-link">đăng nhập</a> để bình luận.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Page navigation -->
        <div class="page-navigation" id="page-navigation">
            <button class="nav-btn" id="prev-page" title="Trang trước">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="page-indicator">
                <span id="current-page">1</span> / <span id="total-pages"><?php echo $anh_chuongs->num_rows; ?></span>
            </div>
            <button class="nav-btn" id="next-page" title="Trang sau">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>


        <!-- Chapter navigation -->
        <div class="chapter-navigation">
            <?php if (!empty($chuong['id_chuong_truoc'])): ?>
                <a href="docChapter.php?id_chuong=<?php echo $chuong['id_chuong_truoc']; ?>" class="nav-btn">Chương trước</a>
            <?php endif; ?>

            <a href="../truyen/chiTietTruyen.php?id_truyen=<?php echo $chuong['id_truyen']; ?>" class="nav-btn">Danh sách chương</a>

            <?php if (!empty($chuong['id_chuong_sau'])): ?>
                <a href="docChapter.php?id_chuong=<?php echo $chuong['id_chuong_sau']; ?>" class="nav-btn">Chương sau</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pages = document.querySelectorAll('.page-viewer');
            const currentPageEl = document.getElementById('current-page');
            const totalPagesEl = document.getElementById('total-pages');
            const prevBtn = document.getElementById('prev-page');
            const nextBtn = document.getElementById('next-page');
            const fullscreenBtn = document.getElementById('fullscreen-btn');
            const viewerArea = document.getElementById('viewer-area');
            let currentPage = 0;


            // Hiển thị trang hiện tại
            function showPage(index) {
                pages.forEach((page, i) => {
                    page.classList.toggle('active', i === index);
                });
                currentPageEl.textContent = index + 1;
                currentPage = index;

                // Cập nhật trạng thái của các ô số trang
                const pageNumbers = document.querySelectorAll('.page-number');
                pageNumbers.forEach((pageNumber, i) => {
                    pageNumber.classList.toggle('active', i === index);
                });

                // Vô hiệu hóa nút nếu ở trang đầu hoặc cuối
                prevBtn.disabled = index === 0;
                nextBtn.disabled = index === pages.length - 1;
            }

            // Hiển thị trang đầu tiên
            showPage(currentPage);

            // Chuyển sang trang trước
            function prevPage() {
                if (currentPage > 0) {
                    currentPage--;
                    showPage(currentPage);
                }
            }

            // Chuyển sang trang tiếp theo
            function nextPage() {
                if (currentPage < pages.length - 1) {
                    currentPage++;
                    showPage(currentPage);
                }
            }

            // Chuyển đến trang cụ thể
            function goToPage(index) {
                if (index >= 0 && index < pages.length) {
                    showPage(index);
                }
            }

            // Gắn sự kiện cho nút chuyển trang
            prevBtn.addEventListener('click', prevPage);
            nextBtn.addEventListener('click', nextPage);

            // Xử lý sự kiện phím mũi tên
            document.addEventListener('keydown', function (e) {
                // Chỉ xử lý nếu đang ở chế độ fullscreen
                if (document.fullscreenElement) {
                    if (e.key === 'ArrowLeft') {
                        prevPage();
                        e.preventDefault(); // Ngăn hành vi mặc định
                    } else if (e.key === 'ArrowRight') {
                        nextPage();
                        e.preventDefault(); // Ngăn hành vi mặc định
                    } else if (e.key === 'Escape') {
                        // Thoát fullscreen khi nhấn ESC
                        document.exitFullscreen();
                    }
                } else {
                    // Xử lý bình thường khi không ở chế độ fullscreen
                    if (e.key === 'ArrowLeft') {
                        prevPage();
                    } else if (e.key === 'ArrowRight') {
                        nextPage();
                    }
                }
            });

            // Kích hoạt chế độ toàn màn hình
            function toggleFullscreen() {
                if (!document.fullscreenElement) {
                    viewerArea.requestFullscreen().catch(err => {
                        console.error(`Lỗi khi vào chế độ toàn màn hình: ${err.message}`);
                    });
                    document.body.classList.add('fullscreen-mode');
                    fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
                    // Hiển thị thanh điều hướng khi vào fullscreen
                    showNavigationBar();
                } else {
                    document.exitFullscreen().catch(err => {
                        console.error(`Lỗi khi thoát chế độ toàn màn hình: ${err.message}`);
                    });
                    document.body.classList.remove('fullscreen-mode');
                    fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
                }
            }

            // Gắn sự kiện cho nút toàn màn hình
            fullscreenBtn.addEventListener('click', toggleFullscreen);

            // Thoát chế độ toàn màn hình khi người dùng bấm ESC
            document.addEventListener('fullscreenchange', function () {
                if (!document.fullscreenElement) {
                    document.body.classList.remove('fullscreen-mode');
                    fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>'; // Đặt lại biểu tượng
                }
            });

            // Xử lý sự kiện nhấn vào hai bên của trang
            viewerArea.addEventListener('click', function (e) {
                // Chỉ xử lý nếu đang ở chế độ fullscreen
                if (document.fullscreenElement) {
                    const clickX = e.clientX;
                    const windowWidth = window.innerWidth;

                    // Tính toán vùng click chính xác hơn
                    const clickZone = windowWidth / 4; // Chia màn hình thành 4 phần

                    if (clickX < clickZone) {
                        prevPage(); // Nhấn 25% bên trái để chuyển sang trang trước
                    } else if (clickX > windowWidth - clickZone) {
                        nextPage(); // Nhấn 25% bên phải để chuyển sang trang sau
                    }
                    // Click ở giữa không làm gì để tránh chuyển trang nhầm
                }
            });



            // Khởi tạo thanh điều hướng

        });
    </script>
</body>
</html>