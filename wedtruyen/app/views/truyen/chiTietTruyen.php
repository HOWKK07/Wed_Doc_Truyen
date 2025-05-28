<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/danhGiaController.php';
require_once '../../controllers/binhLuanController.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection error");
}

// Validate story ID
$id_truyen = isset($_GET['id_truyen']) ? (int)$_GET['id_truyen'] : 0;
if ($id_truyen <= 0) {
    die("Invalid story ID");
}

// Get story information
$sql = "SELECT truyen.*, GROUP_CONCAT(theloai.ten_theloai SEPARATOR ', ') AS the_loai
        FROM truyen
        LEFT JOIN truyen_theloai ON truyen.id_truyen = truyen_theloai.id_truyen
        LEFT JOIN theloai ON truyen_theloai.id_theloai = theloai.id_theloai
        WHERE truyen.id_truyen = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Database query preparation failed");
}

$stmt->bind_param("i", $id_truyen);
if (!$stmt->execute()) {
    die("Database query execution failed");
}

$result = $stmt->get_result();
if (!$result) {
    die("Failed to get query result");
}

$truyen = $result->fetch_assoc();
if (!$truyen) {
    die("Story not found");
}

// Đảm bảo tất cả các trường trong $truyen là chuỗi hoặc giá trị mặc định
$truyen = array_map(function($value) {
    return $value === null ? '' : $value;
}, $truyen);

// Get chapters list
$sql_chuong = "SELECT * FROM chuong WHERE id_truyen = ? ORDER BY so_chuong DESC";
$stmt_chuong = $conn->prepare($sql_chuong);
if (!$stmt_chuong) {
    die("Database query preparation failed");
}

$stmt_chuong->bind_param("i", $id_truyen);
if (!$stmt_chuong->execute()) {
    die("Database query execution failed");
}

$chuongs = $stmt_chuong->get_result();
if (!$chuongs) {
    die("Failed to get query result");
}

// Get ID of the first chapter
$sql_min_chapter = "SELECT id_chuong FROM chuong WHERE id_truyen = ? ORDER BY so_chuong ASC LIMIT 1";
$stmt_min_chapter = $conn->prepare($sql_min_chapter);
if (!$stmt_min_chapter) {
    die("Database query preparation failed");
}

$stmt_min_chapter->bind_param("i", $id_truyen);
if (!$stmt_min_chapter->execute()) {
    die("Database query execution failed");
}

$result_min_chapter = $stmt_min_chapter->get_result();
if (!$result_min_chapter) {
    die("Failed to get query result");
}

$min_chapter = $result_min_chapter->fetch_assoc();
$id_chuong_min = isset($min_chapter['id_chuong']) ? (int)$min_chapter['id_chuong'] : null;

// Sử dụng danhGiaController để lấy thông tin đánh giá
$danhGiaController = new danhGiaController($conn);
$ratingData = $danhGiaController->getRating($id_truyen);
$avg_rating = isset($ratingData['avg_rating']) ? round((float)$ratingData['avg_rating'], 1) : 0.0;
$total_ratings = isset($ratingData['total_ratings']) ? (int)$ratingData['total_ratings'] : 0;

// Check if story is in user's library
$is_followed = false;
if (isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['id_nguoidung'])) {
    $sql_check_follow = "SELECT * FROM follows WHERE id_nguoidung = ? AND id_truyen = ?";
    $stmt_check_follow = $conn->prepare($sql_check_follow);
    if ($stmt_check_follow) {
        $id_nguoidung = (int)$_SESSION['user']['id_nguoidung'];
        $stmt_check_follow->bind_param("ii", $id_nguoidung, $id_truyen);
        if ($stmt_check_follow->execute()) {
            $result_check_follow = $stmt_check_follow->get_result();
            $is_followed = $result_check_follow && $result_check_follow->num_rows > 0;
        }
    }
}

// Sử dụng binhLuanController để lấy thông tin bình luận
$binhLuanController = new BinhLuanController($conn);
$binhLuans = $binhLuanController->layBinhLuanTheoTruyen($id_truyen);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($truyen['ten_truyen'] ?? 'Chi Tiết Truyện'); ?></title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/truyen/chiTietTruyen.css">
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Main content -->
    <div class="container">
        <div class="content-wrapper">
            <!-- Cột nội dung chính -->
            <div class="main-content">
                <div class="truyen-header">
                    <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($truyen['anh_bia'] ?? ''); ?>" alt="Ảnh bìa">
                    <div class="truyen-info">
                        <h1><?php echo htmlspecialchars((string)$truyen['ten_truyen']); ?></h1>
                        <p><strong>Tác giả:</strong> <?php echo htmlspecialchars((string)$truyen['tac_gia']); ?></p>
                        <p><strong>Thể loại:</strong></p>
                        <div class="genres">
                            <?php
                            $the_loai = isset($truyen['the_loai']) ? explode(', ', (string)$truyen['the_loai']) : [];
                            foreach ($the_loai as $genre) {
                                echo "<span>" . htmlspecialchars($genre) . "</span>";
                            }
                            ?>
                        </div>
                        <p><strong>Năm xuất bản:</strong> <?php echo htmlspecialchars($truyen['nam_phat_hanh'] ?? ''); ?></p>
                        <p><strong>Trạng thái:</strong> <?php echo htmlspecialchars($truyen['trang_thai'] ?? ''); ?></p>
                        <p><strong>Lượt xem:</strong> <?php echo number_format((int)($truyen['luot_xem'] ?? 0)); ?> </p>
                        <!-- Rating Section -->
                        <div class="rating-section">
                            <p><strong>Đánh giá:</strong> <?php echo $avg_rating; ?> / 5 (<?php echo $total_ratings; ?> lượt)</p>
                            
                            <?php if (isset($_SESSION['user'])): ?>
                                <form action="rate.php" method="POST" style="display: flex; align-items: center; gap: 10px;">
                                    <input type="hidden" name="id_truyen" value="<?php echo $id_truyen; ?>">
                                    <input type="hidden" id="so_sao" name="so_sao" value="0">
                                    
                                    <div class="stars">
                                        <span data-value="1" class="star">★</span>
                                        <span data-value="2" class="star">★</span>
                                        <span data-value="3" class="star">★</span>
                                        <span data-value="4" class="star">★</span>
                                        <span data-value="5" class="star">★</span>
                                    </div>

                                    <button type="submit" class="submit-rating-btn">Gửi đánh giá</button>
                                </form>
                            <?php else: ?>
                                <p><a href="../taiKhoan/login.php">Đăng nhập</a> để đánh giá truyện</p>
                            <?php endif; ?>
                        </div>

                        <p><strong>Mô tả:</strong> <?php echo nl2br(htmlspecialchars($truyen['mo_ta'] ?? '')); ?></p>
                        
                        <div class="truyen-actions">
                            <?php if ($id_chuong_min): ?>
                                <a href="../chapter/docChapter.php?id_chuong=<?php echo $id_chuong_min; ?>" class="start-reading">Bắt đầu đọc</a>
                            <?php else: ?>
                                <button class="start-reading" disabled>Không có chương để đọc</button>
                            <?php endif; ?>

                            <button type="button" class="add-chapter-btn" onclick="openAddChapterModal()">Thêm Chapter</button>

                            <button id="follow-button" class="add-to-library" data-followed="<?php echo $is_followed ? 'true' : 'false'; ?>">
                                <?php echo $is_followed ? 'Xóa khỏi thư viện' : 'Thêm vào thư viện'; ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="chapter-list">
                    <h2>Danh sách Chương</h2>
                    <?php while ($chuong = $chuongs->fetch_assoc()): ?>
                        <div class="chapter-item">
                            <div class="chapter-info">
                                <a href="../chapter/docChapter.php?id_chuong=<?php echo htmlspecialchars($chuong['id_chuong']); ?>" class="chapter-title">
                                    Chương <?php echo htmlspecialchars($chuong['so_chuong']); ?>: <?php echo htmlspecialchars($chuong['tieu_de']); ?>
                                </a>
                                <span class="chapter-meta">
                                    Ngày tạo: <?php echo date('d/m/Y', strtotime($chuong['ngay_tao'] ?? 'now')); ?>
                                    | 👁 <?php echo (int)($chuong['luot_xem'] ?? 0); ?> lượt xem
                                </span>
                            </div>
                            <!-- Các nút chức năng -->
                            <?php if (isset($_SESSION['user']) && $_SESSION['user']['vai_tro'] === 'admin'): ?>
                                <div class="chapter-actions">
                                    <a href="../anhChuong/list.php?id_chuong=<?php echo htmlspecialchars($chuong['id_chuong']); ?>" class="btn btn-primary">Danh Sách Trang</a>
                                    <button type="button"
    class="btn btn-success"
    onclick="openAddPageModal('<?php echo htmlspecialchars($chuong['id_chuong']); ?>')"
>Thêm Trang</button>
                                    <button type="button"
                                    
    class="btn btn-warning"
    onclick="openEditChapterModal('<?php echo htmlspecialchars($chuong['id_chuong']); ?>', '<?php echo htmlspecialchars($chuong['so_chuong']); ?>', '<?php echo htmlspecialchars(addslashes($chuong['tieu_de'])); ?>')"
>
    Sửa
</button>
                                    <a href="../chapter/delete.php?id_chuong=<?php echo htmlspecialchars($chuong['id_chuong']); ?>&id_truyen=<?php echo htmlspecialchars($chuong['id_truyen']); ?>" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa chương này?');">Xóa</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Cột bình luận -->
            <div class="comments-section">
                <h2>Bình luận</h2>
                <div class="comments-list">
                    <?php if ($binhLuans->num_rows > 0): ?>
                        <?php while ($comment = $binhLuans->fetch_assoc()): ?>
                            <div class="comment-item">
                                <div class="comment-header">
                                    <strong class="comment-author"><?php echo htmlspecialchars($comment['ten_dang_nhap']); ?></strong>
                                    <span class="comment-date"><?php echo date('d/m/Y H:i', strtotime($comment['ngay_binh_luan'])); ?></span>
                                </div>
                                <p class="comment-content"><?php echo htmlspecialchars($comment['noi_dung']); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-comments">Chưa có bình luận nào. Hãy là người đầu tiên bình luận!</p>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['user'])): ?>
                    <form action="../binhLuan/addComment.php" method="POST" class="comment-form">
                        <input type="hidden" name="id_truyen" value="<?php echo $id_truyen; ?>">
                        <textarea name="noi_dung" rows="3" placeholder="Viết bình luận..." required></textarea>
                        <button type="submit">Gửi bình luận</button>
                    </form>
                <?php else: ?>
                    <p class="login-prompt"><a href="../taiKhoan/login.php">Đăng nhập</a> để bình luận.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>

    <!-- Popup Thêm Chapter -->
<div id="addChapterModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close-btn" onclick="closeAddChapterModal()">&times;</span>
    <h3>Thêm Chapter</h3>
    <form id="addChapterForm">
      <input type="hidden" name="id_truyen" value="<?php echo $id_truyen; ?>">
      <label>Số chương:</label>
      <input type="number" name="so_chuong" required>
      <label>Tiêu đề:</label>
      <input type="text" name="tieu_de" required>
      <button type="submit">Thêm</button>
    </form>
    <div id="addChapterError" style="color:red;"></div>
  </div>
</div>

<!-- Popup Sửa Chapter -->
<div id="editChapterModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close-btn" onclick="closeEditChapterModal()">&times;</span>
    <h3>Sửa Chapter</h3>
    <form id="editChapterForm">
      <input type="hidden" name="id_chuong" id="edit_id_chuong">
      <input type="hidden" name="id_truyen" value="<?php echo $id_truyen; ?>">
      <label>Số chương:</label>
      <input type="number" name="so_chuong" id="edit_so_chuong" required>
      <label>Tiêu đề:</label>
      <input type="text" name="tieu_de" id="edit_tieu_de" required>
      <button type="submit">Cập nhật</button>
    </form>
    <div id="editChapterError" style="color:red;"></div>
  </div>
</div>

<!-- Popup Thêm Trang -->
<div id="addPageModal" class="modal" style="display:none;">
  <div class="modal-content" style="max-width:400px;margin:auto;">
    <span class="close-btn" onclick="closeAddPageModal()" style="float:right;cursor:pointer;">&times;</span>
    <h3>Thêm Trang Ảnh</h3>
    <form id="addPageForm" enctype="multipart/form-data">
      <input type="hidden" name="id_chuong" id="add_page_id_chuong">
      <label>Chọn ảnh:</label>
      <input type="file" name="anh[]" accept="image/*" multiple required><br>
      <div style="margin-top:16px; text-align:right;">
        <button type="button" onclick="closeAddPageModal()" style="margin-right:10px;">Hủy</button>
        <button type="submit">Thêm Trang</button>
      </div>
    </form>
    <div id="addPageError" style="color:red;"></div>
  </div>
</div>

<style>
.modal {position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;z-index:9999;}
.modal-content {background:#fff;padding:24px;border-radius:8px;position:relative;}
.close-btn {font-size:24px;cursor:pointer;position:absolute;top:8px;right:16px;}
</style>

    <script>
        document.getElementById('follow-button').addEventListener('click', function () {
            const button = this;
            const idTruyen = <?php echo $id_truyen; ?>; // ID truyện
            const isFollowed = button.getAttribute('data-followed') === 'true';

            fetch(isFollowed ? '../thuvien/delete.php?id_truyen=' + idTruyen : '../thuvien/addFollow.php', {
                method: isFollowed ? 'GET' : 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: isFollowed ? null : JSON.stringify({ id_truyen: idTruyen })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        button.setAttribute('data-followed', isFollowed ? 'false' : 'true');
                        button.textContent = isFollowed ? 'Thêm vào thư viện' : 'Xóa khỏi thư viện';
                        button.style.backgroundColor = isFollowed ? '#6c757d' : '#dc3545';
                        alert(data.message || (isFollowed ? 'Đã xóa khỏi thư viện' : 'Đã thêm vào thư viện'));
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi thực hiện thao tác');
                });
        });

        const stars = document.querySelectorAll('.star');
        const soSaoInput = document.getElementById('so_sao');

        // Thêm sự kiện click cho từng ngôi sao
        stars.forEach(star => {
            star.addEventListener('click', function () {
                const value = this.getAttribute('data-value');
                soSaoInput.value = value;
                updateStars(value);
            });
        });

        // Cập nhật trạng thái của các ngôi sao
        function updateStars(value) {
            stars.forEach(star => {
                const starValue = star.getAttribute('data-value');
                if (starValue <= value) {
                    star.classList.add('selected');
                } else {
                    star.classList.remove('selected');
                }
            });
        }

        // Close modals when clicking outside of them
        window.addEventListener('click', function (event) {
            const addChapterModal = document.getElementById('addChapterModal');
            const editChapterModal = document.getElementById('editChapterModal');
            const addPageModal = document.getElementById('addPageModal');

            if (event.target === addChapterModal) {
                closeAddChapterModal();
            } else if (event.target === editChapterModal) {
                closeEditChapterModal();
            } else if (event.target === addPageModal) {
                closeAddPageModal();
            }
        });

        function openAddChapterModal() {
            document.getElementById('addChapterModal').style.display = 'flex';
        }
        function closeAddChapterModal() {
            document.getElementById('addChapterModal').style.display = 'none';
            document.getElementById('addChapterError').innerText = '';
        }
        function openEditChapterModal(id, so_chuong, tieu_de) {
            document.getElementById('edit_id_chuong').value = id;
            document.getElementById('edit_so_chuong').value = so_chuong;
            document.getElementById('edit_tieu_de').value = tieu_de;
            document.getElementById('editChapterModal').style.display = 'flex';
        }
        function closeEditChapterModal() {
            document.getElementById('editChapterModal').style.display = 'none';
            document.getElementById('editChapterError').innerText = '';
        }
        function openAddPageModal(id_chuong) {
            document.getElementById('add_page_id_chuong').value = id_chuong;
            document.getElementById('addPageModal').style.display = 'flex';
        }
        function closeAddPageModal() {
            document.getElementById('addPageModal').style.display = 'none';
            document.getElementById('addPageError').innerText = '';
        }
// Xử lý gửi form Thêm Chapter bằng AJAX
document.getElementById('addChapterForm').onsubmit = async function(e) {
    e.preventDefault();
    const form = this;
    const so_chuong = form.so_chuong.value;
    const tieu_de = form.tieu_de.value;
    const id_truyen = form.id_truyen.value;

    const res = await fetch('/Wed_Doc_Truyen/wedtruyen/app/views/chapter/add_ajax.php?id_truyen=' + id_truyen, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `so_chuong=${encodeURIComponent(so_chuong)}&tieu_de=${encodeURIComponent(tieu_de)}`
    });
    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch { data = null; }
    if (data && data.success) {
        alert('Thêm chapter thành công!');
        location.reload();
    } else {
        document.getElementById('addChapterError').innerText = data && data.error ? data.error : 'Có lỗi xảy ra!';
    }
};

// Xử lý gửi form Thêm Trang bằng AJAX
document.getElementById('addPageForm').onsubmit = async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id_chuong = document.getElementById('add_page_id_chuong').value;
    
    if (!id_chuong) {
        document.getElementById('addPageError').innerText = 'Vui lòng chọn chương!';
        return;
    }
    
    try {
        const res = await fetch('/Wed_Doc_Truyen/wedtruyen/app/views/anhChuong/add_ajax.php?id_chuong=' + id_chuong, {
            method: 'POST',
            body: formData
        });
        
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Response:', text);
            document.getElementById('addPageError').innerText = 'Lỗi server: ' + text;
            return;
        }
        
        if (data && data.success) {
            alert('Thêm trang thành công!');
            closeAddPageModal();
            // Không reload trang, chỉ đóng modal
        } else {
            document.getElementById('addPageError').innerText = data && data.error ? data.error : 'Có lỗi xảy ra!';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('addPageError').innerText = 'Lỗi kết nối: ' + error.message;
    }
};

// Xử lý gửi form Sửa Chapter bằng AJAX
document.getElementById('editChapterForm').onsubmit = async function(e) {
    e.preventDefault();
    const form = this;
    const id_chuong = form.edit_id_chuong.value;
    const so_chuong = form.edit_so_chuong.value;
    const tieu_de = form.edit_tieu_de.value;
    const id_truyen = form.id_truyen.value;

    const res = await fetch('/Wed_Doc_Truyen/wedtruyen/app/views/chapter/edit_ajax.php?id_chuong=' + id_chuong, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_chuong=${encodeURIComponent(id_chuong)}&so_chuong=${encodeURIComponent(so_chuong)}&tieu_de=${encodeURIComponent(tieu_de)}&id_truyen=${encodeURIComponent(id_truyen)}`
    });
    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch { data = null; }
    if (data && data.success) {
        alert('Cập nhật chapter thành công!');
        location.reload();
    } else {
        document.getElementById('editChapterError').innerText = data && data.error ? data.error : 'Có lỗi xảy ra!';
    }
};
    </script>

    <?php
    // Close database resources
    $stmt->close();
    $stmt_chuong->close();
    $stmt_min_chapter->close();
    if (isset($stmt_check_follow)) {
        $stmt_check_follow->close();
    }
    $conn->close();
    ?>
</body>
</html>