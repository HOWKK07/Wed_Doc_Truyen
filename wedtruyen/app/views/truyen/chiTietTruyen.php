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

// Get story information with enhanced query
$sql = "SELECT truyen.*, 
        GROUP_CONCAT(DISTINCT theloai.ten_theloai SEPARATOR ', ') AS the_loai,
        loai_truyen.ten_loai_truyen,
        COUNT(DISTINCT c.id_chuong) as total_chapters,
        MAX(c.ngay_tao) as last_update
        FROM truyen
        LEFT JOIN truyen_theloai ON truyen.id_truyen = truyen_theloai.id_truyen
        LEFT JOIN theloai ON truyen_theloai.id_theloai = theloai.id_theloai
        LEFT JOIN loai_truyen ON truyen.id_loai_truyen = loai_truyen.id_loai_truyen
        LEFT JOIN chuong c ON truyen.id_truyen = c.id_truyen
        WHERE truyen.id_truyen = ?
        GROUP BY truyen.id_truyen";
        
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

// Get chapters list with enhanced query
$sql_chuong = "SELECT c.*, 
               (SELECT COUNT(*) FROM anh_chuong WHERE id_chuong = c.id_chuong) as total_pages
               FROM chuong c 
               WHERE c.id_truyen = ? 
               ORDER BY c.so_chuong DESC";
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

// Get latest chapter
$sql_latest = "SELECT id_chuong, so_chuong FROM chuong WHERE id_truyen = ? ORDER BY so_chuong DESC LIMIT 1";
$stmt_latest = $conn->prepare($sql_latest);
$stmt_latest->bind_param("i", $id_truyen);
$stmt_latest->execute();
$latest_chapter = $stmt_latest->get_result()->fetch_assoc();

// Get rating data
$danhGiaController = new danhGiaController($conn);
$ratingData = $danhGiaController->getRating($id_truyen);
$avg_rating = isset($ratingData['avg_rating']) ? round((float)$ratingData['avg_rating'], 1) : 0.0;
$total_ratings = isset($ratingData['total_ratings']) ? (int)$ratingData['total_ratings'] : 0;

// Check if story is in user's library
$is_followed = false;
$user_rating = 0;
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
    
    // Get user's rating
    $sql_user_rating = "SELECT so_sao FROM ratings WHERE id_nguoidung = ? AND id_truyen = ?";
    $stmt_user_rating = $conn->prepare($sql_user_rating);
    $stmt_user_rating->bind_param("ii", $id_nguoidung, $id_truyen);
    $stmt_user_rating->execute();
    $result_user_rating = $stmt_user_rating->get_result();
    if ($row = $result_user_rating->fetch_assoc()) {
        $user_rating = $row['so_sao'];
    }
}

// Get comments
$binhLuanController = new BinhLuanController($conn);
$binhLuans = $binhLuanController->layBinhLuanTheoTruyen($id_truyen);

// Get related stories
$sql_related = "SELECT DISTINCT t.id_truyen, t.ten_truyen, t.anh_bia, t.luot_xem 
                FROM truyen t
                JOIN truyen_theloai tt1 ON t.id_truyen = tt1.id_truyen
                WHERE tt1.id_theloai IN (
                    SELECT id_theloai FROM truyen_theloai WHERE id_truyen = ?
                )
                AND t.id_truyen != ?
                ORDER BY t.luot_xem DESC
                LIMIT 6";
$stmt_related = $conn->prepare($sql_related);
$stmt_related->bind_param("ii", $id_truyen, $id_truyen);
$stmt_related->execute();
$related_stories = $stmt_related->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($truyen['ten_truyen'] ?? 'Chi Tiết Truyện'); ?> - Web Đọc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/truyen/chiTietTruyen-optimized.css">
    <style>
        /* Rating Stars Interactive */
        .interactive-stars {
            display: inline-flex;
            gap: 5px;
            cursor: pointer;
        }

        .star-rate {
            font-size: 24px;
            color: rgba(255,255,255,0.3);
            cursor: pointer;
            transition: all 0.2s ease;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .star-rate:hover {
            transform: scale(1.2);
        }

        .star-rate.hover {
            color: #ffd700 !important;
            transform: scale(1.2);
        }

        .star-rate.active {
            color: #ffd700 !important;
        }

        /* Ensure form submission */
        .rating-form {
            display: inline-flex;
            align-items: center;
            gap: 15px;
        }

        /* Button hover state */
        .btn-rate {
            padding: 6px 16px;
            background: rgba(255,215,0,0.2);
            border: 1px solid #ffd700;
            color: #ffd700;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            outline: none;
        }

        .btn-rate:hover {
            background: #ffd700;
            color: #333;
            transform: translateY(-1px);
        }

        .btn-rate:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Hero Banner -->
    <div class="hero-banner" style="background-image: url('/Wed_Doc_Truyen/<?php echo htmlspecialchars($truyen['anh_bia'] ?? ''); ?>');">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="container">
                <div class="story-main-info">
                    <div class="story-cover">
                        <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($truyen['anh_bia'] ?? ''); ?>" alt="<?php echo htmlspecialchars($truyen['ten_truyen']); ?>">
                        <div class="story-status-badge <?php echo strtolower($truyen['trang_thai']) === 'hoàn thành' ? 'completed' : 'ongoing'; ?>">
                            <?php echo htmlspecialchars($truyen['trang_thai']); ?>
                        </div>
                    </div>
                    <div class="story-details">
                        <h1 class="story-title"><?php echo htmlspecialchars($truyen['ten_truyen']); ?></h1>
                        
                        <div class="story-meta-info">
                            <div class="meta-item">
                                <i class="fas fa-user-pen"></i>
                                <span>Tác giả: <strong><?php echo htmlspecialchars($truyen['tac_gia']); ?></strong></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-layer-group"></i>
                                <span>Loại: <strong><?php echo htmlspecialchars($truyen['ten_loai_truyen'] ?? 'Chưa phân loại'); ?></strong></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-book"></i>
                                <span>Số chương: <strong><?php echo $truyen['total_chapters']; ?></strong></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-eye"></i>
                                <span>Lượt xem: <strong><?php echo number_format((int)($truyen['luot_xem'] ?? 0)); ?></strong></span>
                            </div>
                        </div>

                        <div class="story-genres">
                            <?php
                            $the_loai = isset($truyen['the_loai']) ? explode(', ', (string)$truyen['the_loai']) : [];
                            foreach ($the_loai as $genre) {
                                echo "<span class='genre-tag'><i class='fas fa-tag'></i> " . htmlspecialchars($genre) . "</span>";
                            }
                            ?>
                        </div>

                        <div class="story-rating">
                            <div class="rating-display">
                                <div class="rating-score">
                                    <span class="score-number"><?php echo $avg_rating; ?></span>
                                    <div class="rating-stars">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= round($avg_rating) ? 'active' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-count">(<?php echo $total_ratings; ?> đánh giá)</span>
                                </div>
                                
                                <?php if (isset($_SESSION['user'])): ?>
                                <div class="user-rating">
                                    <span>Đánh giá của bạn:</span>
                                    <form action="rate.php" method="POST" class="rating-form">
                                        <input type="hidden" name="id_truyen" value="<?php echo $id_truyen; ?>">
                                        <input type="hidden" id="so_sao" name="so_sao" value="<?php echo $user_rating; ?>">
                                        <div class="interactive-stars">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star star-rate <?php echo $i <= $user_rating ? 'active' : ''; ?>" data-value="<?php echo $i; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <button type="submit" class="btn-rate">Gửi đánh giá</button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="story-actions">
                            <?php if ($id_chuong_min): ?>
                                <a href="../chapter/docChapter.php?id_chuong=<?php echo $id_chuong_min; ?>" class="btn btn-primary btn-large">
                                    <i class="fas fa-book-reader"></i> Đọc từ đầu
                                </a>
                                <?php if ($latest_chapter && $latest_chapter['so_chuong'] > 1): ?>
                                    <a href="../chapter/docChapter.php?id_chuong=<?php echo $latest_chapter['id_chuong']; ?>" class="btn btn-secondary btn-large">
                                        <i class="fas fa-forward"></i> Đọc chương mới nhất
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn btn-primary btn-large" disabled>
                                    <i class="fas fa-book-reader"></i> Chưa có chương
                                </button>
                            <?php endif; ?>
                            
                            <button id="follow-button" class="btn btn-follow <?php echo $is_followed ? 'followed' : ''; ?>" 
                                    data-followed="<?php echo $is_followed ? 'true' : 'false'; ?>">
                                <i class="fas <?php echo $is_followed ? 'fa-bookmark' : 'fa-bookmark'; ?>"></i>
                                <span><?php echo $is_followed ? 'Đã lưu' : 'Lưu truyện'; ?></span>
                            </button>
                            
                            <button class="btn btn-share" onclick="shareStory()">
                                <i class="fas fa-share-alt"></i> Chia sẻ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container main-content">
        <div class="content-grid">
            <!-- Left Column -->
            <div class="content-left">
                <!-- Story Description -->
                <div class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-info-circle"></i> Giới thiệu
                    </h2>
                    <div class="story-description">
                        <?php echo nl2br(htmlspecialchars($truyen['mo_ta'] ?? 'Chưa có mô tả')); ?>
                    </div>
                </div>

                <!-- Chapters List -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-list"></i> Danh sách chương
                        </h2>
                        <div class="chapter-controls">
                            <input type="text" id="searchChapter" placeholder="Tìm chương..." class="search-chapter">
                            <button class="btn-sort" onclick="toggleSort()">
                                <i class="fas fa-sort"></i> Sắp xếp
                            </button>
                        </div>
                    </div>
                    
                    <div class="chapters-container" id="chaptersContainer">
                        <?php 
                        $chapter_list = [];
                        while ($chuong = $chuongs->fetch_assoc()) {
                            $chapter_list[] = $chuong;
                        }
                        
                        foreach($chapter_list as $chuong): 
                        ?>
                        <div class="chapter-item" data-chapter="<?php echo $chuong['so_chuong']; ?>">
                            <a href="../chapter/docChapter.php?id_chuong=<?php echo htmlspecialchars($chuong['id_chuong']); ?>" class="chapter-link">
                                <div class="chapter-main">
                                    <span class="chapter-number">Chương <?php echo htmlspecialchars($chuong['so_chuong']); ?></span>
                                    <span class="chapter-title"><?php echo htmlspecialchars($chuong['tieu_de']); ?></span>
                                </div>
                                <div class="chapter-info">
                                    <span class="chapter-date">
                                        <i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($chuong['ngay_tao'] ?? 'now')); ?>
                                    </span>
                                    <span class="chapter-stats">
                                        <i class="far fa-eye"></i> <?php echo number_format((int)($chuong['luot_xem'] ?? 0)); ?>
                                    </span>
                                    <?php if(isset($chuong['total_pages']) && $chuong['total_pages'] > 0): ?>
                                    <span class="chapter-pages">
                                        <i class="far fa-image"></i> <?php echo $chuong['total_pages']; ?> trang
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </a>
                            
                            <?php if (isset($_SESSION['user']) && $_SESSION['user']['vai_tro'] === 'admin'): ?>
                            <div class="chapter-admin-actions">
                                <a href="../anhChuong/list.php?id_chuong=<?php echo htmlspecialchars($chuong['id_chuong']); ?>" 
                                   class="btn-admin" title="Quản lý trang">
                                    <i class="fas fa-images"></i>
                                </a>
                                <button onclick="openAddPageModal('<?php echo htmlspecialchars($chuong['id_chuong']); ?>')" 
                                        class="btn-admin" title="Thêm trang">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button onclick="openEditChapterModal('<?php echo htmlspecialchars($chuong['id_chuong']); ?>', '<?php echo htmlspecialchars($chuong['so_chuong']); ?>', '<?php echo htmlspecialchars(addslashes($chuong['tieu_de'])); ?>')"
                                        class="btn-admin" title="Sửa chương">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="../chapter/delete.php?id_chuong=<?php echo htmlspecialchars($chuong['id_chuong']); ?>&id_truyen=<?php echo htmlspecialchars($chuong['id_truyen']); ?>" 
                                   class="btn-admin btn-delete" 
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa chương này?');"
                                   title="Xóa chương">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['vai_tro'] === 'admin'): ?>
                    <div class="chapter-add-section">
                        <button class="btn btn-primary btn-block" onclick="openAddChapterModal()">
                            <i class="fas fa-plus-circle"></i> Thêm chương mới
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Comments Section -->
                <div class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-comments"></i> Bình luận (<?php echo $binhLuans->num_rows; ?>)
                    </h2>
                    
                    <?php if (isset($_SESSION['user'])): ?>
                    <form action="../binhLuan/addComment.php" method="POST" class="comment-form-modern">
                        <input type="hidden" name="id_truyen" value="<?php echo $id_truyen; ?>">
                        <div class="comment-input-group">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user']['ten_dang_nhap']); ?>&background=667eea&color=fff" 
                                 alt="Avatar" class="user-avatar">
                            <textarea name="noi_dung" rows="3" placeholder="Viết bình luận của bạn..." required></textarea>
                        </div>
                        <div class="comment-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Gửi bình luận
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="login-prompt-box">
                        <i class="fas fa-lock"></i>
                        <p>Vui lòng <a href="../taiKhoan/login.php">đăng nhập</a> để bình luận</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="comments-list">
                        <?php if ($binhLuans->num_rows > 0): ?>
                            <?php while ($comment = $binhLuans->fetch_assoc()): ?>
                            <div class="comment-item-modern">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($comment['ten_dang_nhap']); ?>&background=667eea&color=fff" 
                                     alt="Avatar" class="comment-avatar">
                                <div class="comment-content">
                                    <div class="comment-header">
                                        <strong class="comment-author"><?php echo htmlspecialchars($comment['ten_dang_nhap']); ?></strong>
                                        <span class="comment-time">
                                            <i class="far fa-clock"></i> 
                                            <?php echo date('d/m/Y H:i', strtotime($comment['ngay_binh_luan'])); ?>
                                        </span>
                                    </div>
                                    <p class="comment-text"><?php echo htmlspecialchars($comment['noi_dung']); ?></p>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-comments">
                                <i class="fas fa-comment-slash"></i>
                                <p>Chưa có bình luận nào. Hãy là người đầu tiên!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="content-right">
                <!-- Story Stats -->
                <div class="sidebar-widget">
                    <h3 class="widget-title">
                        <i class="fas fa-chart-bar"></i> Thống kê
                    </h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <i class="fas fa-eye"></i>
                            <div class="stat-info">
                                <span class="stat-value"><?php echo number_format((int)($truyen['luot_xem'] ?? 0)); ?></span>
                                <span class="stat-label">Lượt xem</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-book"></i>
                            <div class="stat-info">
                                <span class="stat-value"><?php echo $truyen['total_chapters']; ?></span>
                                <span class="stat-label">Chương</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-star"></i>
                            <div class="stat-info">
                                <span class="stat-value"><?php echo $avg_rating; ?>/5</span>
                                <span class="stat-label">Đánh giá</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-comment"></i>
                            <div class="stat-info">
                                <span class="stat-value"><?php echo $binhLuans->num_rows; ?></span>
                                <span class="stat-label">Bình luận</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Stories -->
                <?php if ($related_stories && $related_stories->num_rows > 0): ?>
                <div class="sidebar-widget">
                    <h3 class="widget-title">
                        <i class="fas fa-book-open"></i> Truyện cùng thể loại
                    </h3>
                    <div class="related-stories">
                        <?php while($related = $related_stories->fetch_assoc()): ?>
                        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=<?php echo $related['id_truyen']; ?>" 
                           class="related-item">
                            <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($related['anh_bia'] ?? ''); ?>" 
                                 alt="<?php echo htmlspecialchars($related['ten_truyen']); ?>">
                            <div class="related-info">
                                <h4><?php echo htmlspecialchars($related['ten_truyen']); ?></h4>
                                <span class="related-views">
                                    <i class="fas fa-eye"></i> <?php echo number_format($related['luot_xem']); ?>
                                </span>
                            </div>
                        </a>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Share Box -->
                <div class="sidebar-widget">
                    <h3 class="widget-title">
                        <i class="fas fa-share-alt"></i> Chia sẻ truyện
                    </h3>
                    <div class="share-buttons">
                        <button class="share-btn facebook" onclick="shareOnFacebook()">
                            <i class="fab fa-facebook-f"></i>
                        </button>
                        <button class="share-btn twitter" onclick="shareOnTwitter()">
                            <i class="fab fa-twitter"></i>
                        </button>
                        <button class="share-btn copy" onclick="copyLink()">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>

    <!-- Modals -->
    <?php if (isset($_SESSION['user']) && $_SESSION['user']['vai_tro'] === 'admin'): ?>
    <!-- Add Chapter Modal -->
    <div id="addChapterModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Thêm chương mới</h3>
                <button class="modal-close" onclick="closeAddChapterModal()">&times;</button>
            </div>
            <form id="addChapterForm" class="modal-form">
                <input type="hidden" name="id_truyen" value="<?php echo $id_truyen; ?>">
                <div class="form-group">
                    <label>Số chương</label>
                    <input type="number" name="so_chuong" required>
                </div>
                <div class="form-group">
                    <label>Tiêu đề chương</label>
                    <input type="text" name="tieu_de" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeAddChapterModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm chương</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Chapter Modal -->
    <div id="editChapterModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Sửa chương</h3>
                <button class="modal-close" onclick="closeEditChapterModal()">&times;</button>
            </div>
            <form id="editChapterForm" class="modal-form">
                <input type="hidden" name="id_chuong" id="edit_id_chuong">
                <input type="hidden" name="id_truyen" value="<?php echo $id_truyen; ?>">
                <div class="form-group">
                    <label>Số chương</label>
                    <input type="number" name="so_chuong" id="edit_so_chuong" required>
                </div>
                <div class="form-group">
                    <label>Tiêu đề chương</label>
                    <input type="text" name="tieu_de" id="edit_tieu_de" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeEditChapterModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Page Modal -->
    <div id="addPageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Thêm trang ảnh</h3>
                <button class="modal-close" onclick="closeAddPageModal()">&times;</button>
            </div>
            <form id="addPageForm" class="modal-form" enctype="multipart/form-data">
                <input type="hidden" name="id_chuong" id="add_page_id_chuong">
                <div class="form-group">
                    <label>Chọn ảnh (có thể chọn nhiều)</label>
                    <input type="file" name="anh[]" accept="image/*" multiple required>
                    <small>Hỗ trợ: JPG, PNG, GIF, WEBP</small>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeAddPageModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Tải lên</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Scripts -->
    <script>
        // Rating system with debugging
        const stars = document.querySelectorAll('.star-rate');
        const ratingInput = document.getElementById('so_sao');
        const ratingForm = document.querySelector('.rating-form');
        
        console.log('Rating system initialized:', {
            stars: stars.length,
            ratingInput: ratingInput,
            ratingForm: ratingForm
        });
        
        if (stars.length > 0 && ratingInput) {
            stars.forEach((star, index) => {
                star.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent any default behavior
                    e.stopPropagation(); // Stop event bubbling
                    
                    const value = parseInt(this.getAttribute('data-value'));
                    console.log('Star clicked:', value);
                    
                    ratingInput.value = value;
                    
                    // Update active stars
                    stars.forEach((s, i) => {
                        if (i < value) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
                
                star.addEventListener('mouseenter', function() {
                    const value = parseInt(this.getAttribute('data-value'));
                    
                    // Update hover stars
                    stars.forEach((s, i) => {
                        if (i < value) {
                            s.classList.add('hover');
                        } else {
                            s.classList.remove('hover');
                        }
                    });
                });
            });

            const interactiveStars = document.querySelector('.interactive-stars');
            if (interactiveStars) {
                interactiveStars.addEventListener('mouseleave', function() {
                    stars.forEach(s => {
                        s.classList.remove('hover');
                    });
                });
            }
            
            // Form submission handler
            if (ratingForm) {
                ratingForm.addEventListener('submit', function(e) {
                    const rating = ratingInput.value;
                    if (!rating || rating === '0') {
                        e.preventDefault();
                        alert('Vui lòng chọn số sao để đánh giá!');
                        return false;
                    }
                    console.log('Submitting rating:', rating);
                });
            }
        }

        // Follow/Unfollow
        document.getElementById('follow-button').addEventListener('click', function() {
            const button = this;
            const idTruyen = <?php echo $id_truyen; ?>;
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
                    button.classList.toggle('followed');
                    button.querySelector('span').textContent = isFollowed ? 'Lưu truyện' : 'Đã lưu';
                    
                    // Show notification
                    showNotification(data.message || (isFollowed ? 'Đã xóa khỏi thư viện' : 'Đã thêm vào thư viện'));
                } else {
                    showNotification(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Có lỗi xảy ra khi thực hiện thao tác', 'error');
            });
        });

        // Chapter search and sort
        let sortAsc = false;
        const chaptersContainer = document.getElementById('chaptersContainer');
        const chapters = Array.from(chaptersContainer.children);

        document.getElementById('searchChapter').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            chapters.forEach(chapter => {
                const chapterNumber = chapter.getAttribute('data-chapter');
                const chapterTitle = chapter.querySelector('.chapter-title').textContent.toLowerCase();
                
                if (chapterNumber.includes(searchTerm) || chapterTitle.includes(searchTerm)) {
                    chapter.style.display = '';
                } else {
                    chapter.style.display = 'none';
                }
            });
        });

        function toggleSort() {
            sortAsc = !sortAsc;
            const sorted = chapters.sort((a, b) => {
                const aNum = parseInt(a.getAttribute('data-chapter'));
                const bNum = parseInt(b.getAttribute('data-chapter'));
                return sortAsc ? aNum - bNum : bNum - aNum;
            });
            
            chaptersContainer.innerHTML = '';
            sorted.forEach(chapter => chaptersContainer.appendChild(chapter));
        }

        // Share functions
        function shareStory() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo addslashes($truyen['ten_truyen']); ?>',
                    text: 'Đọc truyện <?php echo addslashes($truyen['ten_truyen']); ?> tại Web Đọc',
                    url: window.location.href
                });
            } else {
                copyLink();
            }
        }

        function shareOnFacebook() {
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`, '_blank');
        }

        function shareOnTwitter() {
            window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(window.location.href)}&text=${encodeURIComponent('Đọc truyện <?php echo addslashes($truyen['ten_truyen']); ?>')}`, '_blank');
        }

        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                showNotification('Đã sao chép link!');
            });
        }

        // Notification system
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        <?php if (isset($_SESSION['user']) && $_SESSION['user']['vai_tro'] === 'admin'): ?>
        // Admin modal functions
        function openAddChapterModal() {
            document.getElementById('addChapterModal').classList.add('show');
        }

        function closeAddChapterModal() {
            document.getElementById('addChapterModal').classList.remove('show');
            document.getElementById('addChapterForm').reset();
        }

        function openEditChapterModal(id, so_chuong, tieu_de) {
            document.getElementById('edit_id_chuong').value = id;
            document.getElementById('edit_so_chuong').value = so_chuong;
            document.getElementById('edit_tieu_de').value = tieu_de;
            document.getElementById('editChapterModal').classList.add('show');
        }

        function closeEditChapterModal() {
            document.getElementById('editChapterModal').classList.remove('show');
        }

        function openAddPageModal(id_chuong) {
            document.getElementById('add_page_id_chuong').value = id_chuong;
            document.getElementById('addPageModal').classList.add('show');
        }

        function closeAddPageModal() {
            document.getElementById('addPageModal').classList.remove('show');
            document.getElementById('addPageForm').reset();
        }

        // AJAX form submissions
        document.getElementById('addChapterForm').onsubmit = async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams();
            for (const [key, value] of formData) {
                params.append(key, value);
            }

            try {
                const response = await fetch(`/Wed_Doc_Truyen/wedtruyen/app/views/chapter/add_ajax.php?id_truyen=${formData.get('id_truyen')}`, {
                    method: 'POST',
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: params.toString()
                });
                
                const result = await response.json();
                if (result.success) {
                    showNotification('Thêm chương thành công!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(result.error || 'Có lỗi xảy ra!', 'error');
                }
            } catch (error) {
                showNotification('Lỗi kết nối!', 'error');
            }
        };

        document.getElementById('editChapterForm').onsubmit = async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams();
            for (const [key, value] of formData) {
                params.append(key, value);
            }

            try {
                const response = await fetch(`/Wed_Doc_Truyen/wedtruyen/app/views/chapter/edit_ajax.php?id_chuong=${formData.get('id_chuong')}`, {
                    method: 'POST',
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: params.toString()
                });
                
                const result = await response.json();
                if (result.success) {
                    showNotification('Cập nhật chương thành công!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(result.error || 'Có lỗi xảy ra!', 'error');
                }
            } catch (error) {
                showNotification('Lỗi kết nối!', 'error');
            }
        };

        document.getElementById('addPageForm').onsubmit = async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const id_chuong = formData.get('id_chuong');

            try {
                const response = await fetch(`/Wed_Doc_Truyen/wedtruyen/app/views/anhChuong/add_ajax.php?id_chuong=${id_chuong}`, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (result.success) {
                    showNotification('Thêm trang thành công!');
                    closeAddPageModal();
                } else {
                    showNotification(result.error || 'Có lỗi xảy ra!', 'error');
                }
            } catch (error) {
                showNotification('Lỗi kết nối!', 'error');
            }
        };
        <?php endif; ?>

        // Close modals when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('show');
                }
            });
        });

        // Smooth scroll to sections
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Initialize tooltips
        document.querySelectorAll('[title]').forEach(element => {
            element.addEventListener('mouseenter', function() {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = this.getAttribute('title');
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
                tooltip.style.left = rect.left + (rect.width - tooltip.offsetWidth) / 2 + 'px';
                
                this.setAttribute('data-original-title', this.getAttribute('title'));
                this.removeAttribute('title');
                
                setTimeout(() => tooltip.classList.add('show'), 10);
            });
            
            element.addEventListener('mouseleave', function() {
                const tooltip = document.querySelector('.tooltip');
                if (tooltip) {
                    tooltip.classList.remove('show');
                    setTimeout(() => tooltip.remove(), 300);
                }
                
                if (this.hasAttribute('data-original-title')) {
                    this.setAttribute('title', this.getAttribute('data-original-title'));
                    this.removeAttribute('data-original-title');
                }
            });
        });
    </script>

    <?php
    // Close database resources
    $stmt->close();
    $stmt_chuong->close();
    $stmt_min_chapter->close();
    $stmt_latest->close();
    $stmt_related->close();
    if (isset($stmt_check_follow)) {
        $stmt_check_follow->close();
    }
    if (isset($stmt_user_rating)) {
        $stmt_user_rating->close();
    }
    $conn->close();
    ?>
    <!-- Thêm đoạn code này vào cuối file chiTietTruyen.php trước thẻ </body> -->
<script>
// Debug code - để kiểm tra vấn đề
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== RATING SYSTEM DEBUG ===');
    
    // 1. Kiểm tra các phần tử có tồn tại không
    const stars = document.querySelectorAll('.star-rate');
    const ratingInput = document.getElementById('so_sao');
    const interactiveStars = document.querySelector('.interactive-stars');
    const ratingForm = document.querySelector('.rating-form');
    
    console.log('1. Elements check:');
    console.log('- Stars found:', stars.length);
    console.log('- Stars elements:', stars);
    console.log('- Rating input:', ratingInput);
    console.log('- Interactive stars container:', interactiveStars);
    console.log('- Rating form:', ratingForm);
    
    // 2. Kiểm tra data attributes
    if (stars.length > 0) {
        console.log('2. Star data attributes:');
        stars.forEach((star, index) => {
            console.log(`- Star ${index + 1}: data-value =`, star.getAttribute('data-value'));
        });
    }
    
    // 3. Kiểm tra current value
    if (ratingInput) {
        console.log('3. Current rating value:', ratingInput.value);
    }
    
    // 4. Test click event
    if (stars.length > 0) {
        console.log('4. Adding test click listener to first star...');
        stars[0].addEventListener('click', function(e) {
            console.log('TEST CLICK WORKED!');
            console.log('Event:', e);
            console.log('Target:', e.target);
            console.log('This element:', this);
            console.log('Data-value:', this.getAttribute('data-value'));
        });
    }
    
    // 5. Kiểm tra có script nào khác can thiệp không
    console.log('5. Other event listeners on stars:');
    if (stars.length > 0) {
        // This is a simple check, might not show all listeners
        console.log('First star click listeners:', stars[0].onclick);
    }
    
    // 6. Thử một cách tiếp cận khác - dùng event delegation
    if (interactiveStars) {
        console.log('6. Testing event delegation approach...');
        interactiveStars.addEventListener('click', function(e) {
            if (e.target.classList.contains('star-rate')) {
                console.log('DELEGATION CLICK WORKED!');
                const value = parseInt(e.target.getAttribute('data-value'));
                console.log('Clicked star value:', value);
                
                // Update the rating
                if (ratingInput) {
                    ratingInput.value = value;
                    console.log('Updated input value to:', value);
                    
                    // Update visual
                    stars.forEach((star, index) => {
                        if (index < value) {
                            star.classList.add('active');
                        } else {
                            star.classList.remove('active');
                        }
                    });
                }
            }
        });
    }
    
    console.log('=== END DEBUG ===');
});

// Alternative approach - jQuery style (if jQuery is loaded)
if (typeof $ !== 'undefined') {
    $(document).ready(function() {
        console.log('jQuery is loaded, trying jQuery approach...');
        
        $('.star-rate').on('click', function() {
            console.log('jQuery click detected!');
            const value = $(this).data('value');
            console.log('Value:', value);
        });
    });
}
</script>

<!-- Thêm CSS để đảm bảo stars có thể click được -->
<style>
    .interactive-stars {
        position: relative;
        z-index: 10;
    }
    
    .star-rate {
        position: relative;
        z-index: 11;
        cursor: pointer !important;
        pointer-events: auto !important;
        display: inline-block;
        width: 30px;
        height: 30px;
        line-height: 30px;
        text-align: center;
    }
    
    /* Đảm bảo không có element nào che stars */
    .rating-form * {
        pointer-events: auto !important;
    }
</style>
</body>
</html>