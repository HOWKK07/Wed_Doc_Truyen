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

        /* Reset rating styles */
        .story-rating {
            margin-bottom: 30px;
        }

        .rating-display {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        .rating-score {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .score-number {
            font-size: 2rem;
            font-weight: 700;
            color: #ffd700;
        }

        .rating-stars {
            display: flex;
            gap: 2px;
        }

        .rating-stars i {
            font-size: 16px;
            color: rgba(255,255,255,0.3);
        }

        .rating-stars i.active {
            color: #ffd700;
        }

        .rating-count {
            font-size: 14px;
            color: rgba(255,255,255,0.8);
        }

        /* User rating section */
        .user-rating-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .rating-form {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .rating-label {
            font-size: 14px;
            color: rgba(255,255,255,0.9);
        }

        .interactive-stars {
            display: inline-flex;
            gap: 5px;
        }

        .star-rate {
            font-size: 24px;
            color: rgba(255,255,255,0.3);
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
            display: inline-block;
            position: relative;
            z-index: 10;
        }

        .star-rate:hover {
            transform: scale(1.2);
        }

        .star-rate.hover,
        .star-rate:hover {
            color: #ffd700 !important;
        }

        .star-rate.active {
            color: #ffd700 !important;
        }

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

        .login-for-rating {
            margin-top: 10px;
        }

        .login-for-rating a {
            color: #ffd700;
            text-decoration: none;
            font-size: 14px;
        }

        .login-for-rating a:hover {
            text-decoration: underline;
        }

        /* Remove debug styles */
        .interactive-stars, .star-rate {
            outline: none !important;
            background: transparent !important;
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
                                <i class="fas fa-user"></i>
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

                        <!-- Thay thế phần story-rating trong phần Story Details bằng đoạn mới -->
                        <div class="story-rating">
                            <div class="rating-display">
                                <div class="rating-score">
                                    <span class="score-number"><?php echo $avg_rating; ?></span>
                                    <div class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo ($i <= round($avg_rating)) ? ' active' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="rating-count">
                                    <?php echo $total_ratings; ?> lượt đánh giá
                                </div>
                            </div>
                            
                            <?php if (isset($_SESSION['user'])): ?>
                            <div class="user-rating-section">
                                <form action="rate.php" method="POST" class="rating-form" id="ratingForm">
                                    <input type="hidden" name="id_truyen" value="<?php echo $id_truyen; ?>">
                                    <input type="hidden" name="so_sao" id="so_sao" value="<?php echo (int)$user_rating; ?>">
                                    <span class="rating-label">Đánh giá của bạn:</span>
                                    <div class="interactive-stars" id="interactiveStars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star-rate<?php echo ($i <= $user_rating) ? ' active' : ''; ?>" 
                                                  data-value="<?php echo $i; ?>" 
                                                  onclick="setRating(<?php echo $i; ?>)">
                                                <i class="fas fa-star"></i>
                                            </span>
                                        <?php endfor; ?>
                                    </div>
                                    <button type="submit" class="btn-rate">Gửi đánh giá</button>
                                </form>
                            </div>
                            <?php else: ?>
                            <div class="login-for-rating">
                                <a href="../taiKhoan/login.php">Đăng nhập để đánh giá</a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <!-- Kết thúc thay thế phần story-rating -->

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
        // --- ĐOẠN NÀY ĐÃ ĐƯỢC ĐƠN GIẢN HÓA, CHỈ GIỮ 1 CÁCH XỬ LÝ SAO ---
        document.addEventListener('DOMContentLoaded', function() {
            const interactiveStars = document.querySelector('.interactive-stars');
            const ratingInput = document.getElementById('so_sao');
            const stars = document.querySelectorAll('.star-rate');
            // Thêm dòng này để lấy form đánh giá
            const ratingForm = document.querySelector('.rating-form');

            if (interactiveStars && ratingInput && stars.length > 0) {
                stars.forEach(star => {
                    star.addEventListener('mouseenter', function() {
                        const value = parseInt(this.getAttribute('data-value'));
                        stars.forEach((s, idx) => {
                            s.classList.toggle('hover', idx < value);
                        });
                    });
                    star.addEventListener('mouseleave', function() {
                        stars.forEach(s => s.classList.remove('hover'));
                    });
                    star.addEventListener('click', function() {
                        const value = parseInt(this.getAttribute('data-value'));
                        ratingInput.value = value;
                        stars.forEach((s, idx) => {
                            s.classList.toggle('active', idx < value);
                        });
                    });
                });
                interactiveStars.addEventListener('mouseleave', function() {
                    stars.forEach(s => s.classList.remove('hover'));
                });
            }

            // Nếu bạn có xử lý gì với ratingForm thì giờ đã có biến này
            // ratingForm.addEventListener('submit', function(e) { ... });
        });

        // Modal management functions
        function openAddChapterModal() {
            const modal = document.getElementById('addChapterModal');
            if (modal) {
                modal.style.display = 'block';
                modal.classList.add('show');
            }
        }

        function closeAddChapterModal() {
            const modal = document.getElementById('addChapterModal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.getElementById('addChapterForm').reset();
            }
        }

        function openEditChapterModal(id_chuong, so_chuong, tieu_de) {
            const modal = document.getElementById('editChapterModal');
            if (modal) {
                document.getElementById('edit_id_chuong').value = id_chuong;
                document.getElementById('edit_so_chuong').value = so_chuong;
                document.getElementById('edit_tieu_de').value = tieu_de;
                modal.style.display = 'block';
                modal.classList.add('show');
            }
        }

        function closeEditChapterModal() {
            const modal = document.getElementById('editChapterModal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.getElementById('editChapterForm').reset();
            }
        }

        function openAddPageModal(id_chuong) {
            const modal = document.getElementById('addPageModal');
            if (modal) {
                document.getElementById('add_page_id_chuong').value = id_chuong;
                modal.style.display = 'block';
                modal.classList.add('show');
            }
        }

        function closeAddPageModal() {
            const modal = document.getElementById('addPageModal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
                document.getElementById('addPageForm').reset();
            }
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                event.target.classList.remove('show');
            }
        });

        // Handle form submissions
        document.addEventListener('DOMContentLoaded', function() {
            // Add Chapter Form
            const addChapterForm = document.getElementById('addChapterForm');
            if (addChapterForm) {
                addChapterForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const id_truyen = <?php echo $id_truyen; ?>;
                    
                    try {
                        const response = await fetch(`/Wed_Doc_Truyen/wedtruyen/app/views/chapter/add_ajax.php?id_truyen=${id_truyen}`, {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            alert('Thêm chương thành công!');
                            closeAddChapterModal();
                            location.reload();
                        } else {
                            alert('Lỗi: ' + (result.error || 'Không thể thêm chương'));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi thêm chương');
                    }
                });
            }

            // Edit Chapter Form
            const editChapterForm = document.getElementById('editChapterForm');
            if (editChapterForm) {
                editChapterForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    
                    try {
                        const response = await fetch('/Wed_Doc_Truyen/wedtruyen/app/views/chapter/edit_ajax.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const text = await response.text();
                        let result;
                        
                        try {
                            result = JSON.parse(text);
                        } catch (parseError) {
                            console.error('Response text:', text);
                            throw new Error('Invalid JSON response');
                        }
                        
                        if (result.success) {
                            alert('Cập nhật chương thành công!');
                            closeEditChapterModal();
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            alert('Lỗi: ' + (result.error || 'Không thể cập nhật chương'));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi cập nhật chương');
                    }
                });
            }

            // Add Page Form
            const addPageForm = document.getElementById('addPageForm');
            if (addPageForm) {
                addPageForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const id_chuong = document.getElementById('add_page_id_chuong').value;
                    
                    try {
                        const response = await fetch(`/Wed_Doc_Truyen/wedtruyen/app/views/anhChuong/add_ajax.php?id_chuong=${id_chuong}`, {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            alert(result.message || 'Thêm trang thành công!');
                            closeAddPageModal();
                        } else {
                            alert('Lỗi: ' + (result.error || 'Không thể thêm trang'));
                            if (result.errors && result.errors.length > 0) {
                                console.error('Errors:', result.errors);
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi thêm trang');
                    }
                });
            }
        });

        let isAsc = false;
function toggleSort() {
    const container = document.getElementById('chaptersContainer');
    if (!container) return;
    const items = Array.from(container.querySelectorAll('.chapter-item'));
    // Đảo thứ tự mảng
    isAsc = !isAsc;
    items.sort((a, b) => {
        const aNum = parseInt(a.getAttribute('data-chapter'));
        const bNum = parseInt(b.getAttribute('data-chapter'));
        return isAsc ? aNum - bNum : bNum - aNum;
    });
    // Xóa và thêm lại các item theo thứ tự mới
    items.forEach(item => container.appendChild(item));
}

// Tìm kiếm chương theo tiêu đề hoặc số chương
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchChapter');
    const chaptersContainer = document.getElementById('chaptersContainer');
    if (searchInput && chaptersContainer) {
        searchInput.addEventListener('input', function() {
            const keyword = this.value.trim().toLowerCase();
            const items = chaptersContainer.querySelectorAll('.chapter-item');
            items.forEach(item => {
                const title = item.querySelector('.chapter-title')?.textContent.toLowerCase() || '';
                const number = item.querySelector('.chapter-number')?.textContent.toLowerCase() || '';
                if (title.includes(keyword) || number.includes(keyword)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
    </script>
    <script>
// Follow/Unfollow functionality (nút Lưu truyện)
document.addEventListener('DOMContentLoaded', function() {
    const followButton = document.getElementById('follow-button');
    if (followButton) {
        followButton.addEventListener('click', async function(e) {
            e.preventDefault();
            <?php if (!isset($_SESSION['user'])): ?>
                alert('Bạn cần đăng nhập để sử dụng chức năng này!');
                window.location.href = '/Wed_Doc_Truyen/wedtruyen/app/views/taiKhoan/login.php';
                return;
            <?php else: ?>
                const isFollowed = this.getAttribute('data-followed') === 'true';
                const idTruyen = <?php echo $id_truyen; ?>;
                const button = this;
                button.disabled = true;
                const url = isFollowed
                    ? '/Wed_Doc_Truyen/wedtruyen/app/views/thuvien/delete.php?id_truyen=' + idTruyen
                    : '/Wed_Doc_Truyen/wedtruyen/app/views/thuvien/addFollow.php';
                const options = {
                    method: isFollowed ? 'GET' : 'POST',
                    headers: { 'Content-Type': 'application/json' }
                };
                if (!isFollowed) {
                    options.body = JSON.stringify({ id_truyen: idTruyen });
                }
                try {
                    const res = await fetch(url, options);
                    const data = await res.json();
                    if (data.success) {
                        button.setAttribute('data-followed', (!isFollowed).toString());
                        button.classList.toggle('followed', !isFollowed);
                        button.querySelector('span').textContent = !isFollowed ? 'Đã lưu' : 'Lưu truyện';
                        showNotification(!isFollowed ? 'Đã thêm vào thư viện' : 'Đã xóa khỏi thư viện', 'success');
                    } else {
                        showNotification(data.message || 'Có lỗi xảy ra', 'error');
                    }
                } catch (error) {
                    showNotification('Có lỗi xảy ra khi xử lý yêu cầu', 'error');
                } finally {
                    button.disabled = false;
                }
            <?php endif; ?>
        });
    }
});

// Thông báo nhỏ
function showNotification(message, type = 'info') {
    const existing = document.querySelector('.notification');
    if (existing) existing.remove();
    const notification = document.createElement('div');
    notification.className = `notification ${type} show`;
    notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> <span>${message}</span>`;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
    </script>
</body>
</html>