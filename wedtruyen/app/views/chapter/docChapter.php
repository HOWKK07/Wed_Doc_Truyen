<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/chapterController.php';
require_once '../../models/anhChuongModel.php';
require_once '../../controllers/binhLuanController.php';
require_once '../../controllers/LichSuDocController.php';

// Kiểm tra tham số id_chuong
if (!isset($_GET['id_chuong']) || !is_numeric($_GET['id_chuong'])) {
    die("Lỗi: Không tìm thấy ID chương hợp lệ.");
}
$id_chuong = (int)$_GET['id_chuong']; // Lấy ID chương từ URL

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

    tangLuotXemChuong($conn, $id_nguoidung, $id_chuong);

    // Lấy lại id_truyen từ database nếu $chuong['id_truyen'] chưa có
    if (empty($chuong['id_truyen'])) {
        $stmt = $conn->prepare("SELECT id_truyen FROM chuong WHERE id_chuong = ?");
        $stmt->bind_param("i", $id_chuong);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $id_truyen = $row ? $row['id_truyen'] : null;
    } else {
        $id_truyen = $chuong['id_truyen'];
    }

    // Chỉ lưu lịch sử đọc nếu $id_chuong hợp lệ
    if (!empty($id_chuong) && is_numeric($id_chuong)) {
        luuLichSuDoc($conn, $id_nguoidung, (int)$id_chuong);
    }

    // Chỉ cập nhật nếu có id_truyen
    if ($id_truyen) {
        $stmt = $conn->prepare("UPDATE truyen SET luot_xem = (SELECT SUM(luot_xem) FROM chuong WHERE id_truyen = ?) WHERE id_truyen = ?");
        $stmt->bind_param("ii", $id_truyen, $id_truyen);
        $stmt->execute();
    }
}



// Lấy danh sách audio/phụ đề cho từng trang
$audio_trang_map = [];
$sql = "SELECT id_anh, duong_dan_audio, duong_dan_sub FROM audio_trang WHERE id_anh IN (SELECT id_anh FROM anh_chuong WHERE id_chuong = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_chuong);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $audio_trang_map[(int)$row['id_anh']] = [
        'audio' => $row['duong_dan_audio'],
        'sub'   => $row['duong_dan_sub']
    ];
}
?>

<!DOCTYPE html>
<html lang="vi">
        <style>
        .subtitle-line > div:first-child {
            color: #111 !important;
        }
    </style>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($chuong['tieu_de']); ?></title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/chapter/docChapter.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div id="reader-container">
        <!-- Reading progress bar -->
        <div class="reading-progress" id="reading-progress"></div>

        <!-- Header -->
        <header class="reader-header" id="reader-header">
            <div class="header-content">
                <a href="../truyen/chiTietTruyen.php?id_truyen=<?php echo $chuong['id_truyen']; ?>" class="back-to-comic">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <div class="chapter-title">
                    <?= htmlspecialchars($chuong['ten_truyen'] ?? '') ?> - 
                    <?= htmlspecialchars($chuong['tieu_de'] ?? '') ?> 
                    (Chương <?= htmlspecialchars($chuong['so_chuong'] ?? '') ?>)
                </div>
                <button class="control-btn" id="fullscreen-btn">
                    <i class="fas fa-expand"></i>
                </button>
            </div>
        </header>

        <!-- Main content -->
        <div id="chapter-container">
            <div class="chapter-content-wrapper">
                <div id="viewer-area">
                    <?php
                    $anh_chuongs2 = $anhChuongModel->layDanhSachAnh($id_chuong, 'ASC');
                    while ($anh = $anh_chuongs2->fetch_assoc()): ?>
                        <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($anh['duong_dan_anh']); ?>"
                             class="page-viewer"
                             data-id-anh="<?php echo $anh['id_anh']; ?>"
                             data-page="<?php echo $anh['so_trang']; ?>">
                    <?php endwhile; ?>
                </div>
                <div class="media-controls" style="
                    flex: 0 0 350px;
                    min-width: 320px;
                    max-width: 400px;
                    position: fixed;
                    right: 0;
                    top: 80px;
                    z-index: 100;
                    background: rgba(26,26,26,0.97);
                    border-radius: 12px 0 0 12px;
                    box-shadow: -2px 4px 16px rgba(0,0,0,0.12);
                    padding: 18px 18px 18px 18px;
                    display: flex;
                    flex-direction: column;
                    align-items: stretch;
                ">
                    <div id="audio-loading" style="display:none;">
                        <i class="fas fa-spinner fa-spin"></i> Đang tải audio...
                    </div>
                    <button id="play-audio-btn" class="audio-control" style="padding:8px 20px; margin-bottom: 12px;">Phát audio</button>
                    <audio id="audio-player" controls style="width:100%;display:none;margin-bottom:12px"></audio>
                    <div id="subtitle-list-box" style="background:#f9f9f9;border-radius:8px;padding:12px;min-height:200px;max-height:400px;overflow-y:auto;max-width:350px;display:none"></div>
                </div>
            </div>
        </div>

        <!-- Navigation and comments -->
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

        <div class="chapter-navigation">
            <?php if (!empty($chuong['id_chuong_truoc'])): ?>
                <a href="docChapter.php?id_chuong=<?php echo $chuong['id_chuong_truoc']; ?>" class="nav-btn">Chương trước</a>
            <?php endif; ?>

            <a href="../truyen/chiTietTruyen.php?id_truyen=<?php echo $chuong['id_truyen']; ?>" class="nav-btn">Danh sách chương</a>

            <?php if (!empty($chuong['id_chuong_sau'])): ?>
                <a href="docChapter.php?id_chuong=<?php echo $chuong['id_chuong_sau']; ?>" class="nav-btn">Chương sau</a>
            <?php endif; ?>
        </div>

        <!-- Comments section (moved to bottom) -->
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

                // Không cần cập nhật main-content nữa
                // document.getElementById('main-content').innerHTML = ...;

                // Cập nhật audio/sub cho trang ảnh
                loadAudioAndSub(index);
                // ...các xử lý khác giữ nguyên...
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
            window.nextPage = nextPage; // <-- Thêm dòng này

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

    <script>
const audioData = <?php echo json_encode($audio_trang_map, JSON_UNESCAPED_UNICODE); ?>;
const audioPlayer = document.getElementById('audio-player');
const playBtn = document.getElementById('play-audio-btn');
const subtitleListBox = document.getElementById('subtitle-list-box');
let cues = [];
let currentCueIndex = -1;
let currentPage = 0;

// Parse VTT thành cues
function parseVTT(vtt) {
    const lines = vtt.split('\n');
    let cues = [];
    let cue = {};
    for (let line of lines) {
        if (line.includes('-->')) {
            const [start, end] = line.split('-->').map(s => s.trim());
            cue = { start: toSeconds(start), end: toSeconds(end), text: '' };
        } else if (line.trim() && cue.start !== undefined) {
            cue.text += line.trim() + ' ';
        } else if (!line.trim() && cue.text) {
            cues.push({ ...cue });
            cue = {};
        }
    }
    if (cue.text) cues.push(cue);
    return cues;
}
function toSeconds(time) {
    const [h, m, s] = time.replace(',', '.').split(':').map(Number);
    return h * 3600 + m * 60 + s;
}

// Hiển thị danh sách phụ đề
function renderSubtitleList() {
    if (!cues.length) {
        subtitleListBox.innerHTML = '';
        subtitleListBox.style.display = 'none';
        return;
    }
    subtitleListBox.style.display = '';
    subtitleListBox.innerHTML = cues.map((cue, idx) => `
        <div class="subtitle-line" data-idx="${idx}" style="padding:6px 0;cursor:pointer;">
            <div style="font-size:14px;">${cue.text}</div>
            <div style="font-size:12px;color:#888;">${formatTime(cue.start)} - ${formatTime(cue.end)}</div>
        </div>
    `).join('');
    // Click vào dòng phụ đề để tua audio
    subtitleListBox.querySelectorAll('.subtitle-line').forEach(line => {
        line.onclick = function() {
            const idx = +this.getAttribute('data-idx');
            if (cues[idx]) {
                audioPlayer.currentTime = cues[idx].start + 0.01;
                audioPlayer.play();
            }
        }
    });
}
function formatTime(sec) {
    sec = Math.floor(sec);
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
}

// Load audio và sub cho từng trang
function loadAudioAndSub(pageIdx) {
    const img = document.querySelectorAll('.page-viewer')[pageIdx];
    const id_anh = img ? img.getAttribute('data-id-anh') : null;
    const data = audioData[id_anh];
    if (data && data.audio) {
        audioPlayer.src = "/Wed_Doc_Truyen/" + data.audio;
        audioPlayer.style.display = '';
        playBtn.style.display = '';
        if (data.sub) {
            fetch("/Wed_Doc_Truyen/" + data.sub)
                .then(res => res.text())
                .then(vtt => {
                    cues = parseVTT(vtt);
                    currentCueIndex = -1;
                    renderSubtitleList();
                });
        } else {
            cues = [];
            subtitleListBox.style.display = 'none';
        }
    } else {
        audioPlayer.src = '';
        audioPlayer.style.display = 'none';
        playBtn.style.display = 'none';
        cues = [];
        subtitleListBox.style.display = 'none';
    }
}

// Highlight dòng phụ đề đang phát
audioPlayer.addEventListener('timeupdate', () => {
    if (!cues.length) return;
    const t = audioPlayer.currentTime;
    let found = -1;
    for (let i = 0; i < cues.length; i++) {
        if (t >= cues[i].start && t <= cues[i].end) {
            found = i;
            break;
        }
    }
    if (found !== currentCueIndex) {
        // Bỏ highlight cũ
        if (currentCueIndex >= 0) {
            const oldLine = subtitleListBox.querySelector(`.subtitle-line[data-idx="${currentCueIndex}"]`);
            if (oldLine) oldLine.style.background = '';
        }
        // Highlight mới
        if (found >= 0) {
            const newLine = subtitleListBox.querySelector(`.subtitle-line[data-idx="${found}"]`);
            if (newLine) {
                newLine.style.background = '#ffe9b3';
                // Scroll vào giữa box nếu bị khuất
                newLine.scrollIntoView({ block: 'center', behavior: 'smooth' });
            }
        }
        currentCueIndex = found;
    }
});

// Nút play audio
playBtn.addEventListener('click', () => {
    audioPlayer.currentTime = 0;
    audioPlayer.play();
});

// Khi chuyển trang, gọi lại loadAudioAndSub
function showPage(idx) {
    currentPage = idx;
    loadAudioAndSub(idx);
}
loadAudioAndSub(0); // Trang đầu tiên

// Tự động chuyển trang khi audio phát hết
audioPlayer.addEventListener('ended', function () {
    if (typeof nextPage === 'function') {
        nextPage();
        setTimeout(() => {
            if (audioPlayer.src && audioPlayer.style.display !== 'none') {
                audioPlayer.currentTime = 0;
                audioPlayer.play();
            }
        }, 300);
    }
});
    </script>
    
</body>
</html>