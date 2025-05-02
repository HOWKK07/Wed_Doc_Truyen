<?php
session_start();
require_once '../../config/connect.php';

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

// Get rating information
$sql_rating = "SELECT AVG(so_sao) AS avg_rating, COUNT(*) AS total_ratings FROM ratings WHERE id_truyen = ?";
$stmt_rating = $conn->prepare($sql_rating);
if (!$stmt_rating) {
    die("Database query preparation failed");
}

$stmt_rating->bind_param("i", $id_truyen);
if (!$stmt_rating->execute()) {
    die("Database query execution failed");
}

$result_rating = $stmt_rating->get_result();
if (!$result_rating) {
    die("Failed to get query result");
}

$rating_data = $result_rating->fetch_assoc();
$avg_rating = isset($rating_data['avg_rating']) ? round((float)$rating_data['avg_rating'], 1) : 0.0;
$total_ratings = isset($rating_data['total_ratings']) ? (int)$rating_data['total_ratings'] : 0;

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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1200px, initial-scale=1.0">
    <title>Chi Tiết Truyện</title>
    <style>
        /* CSS của bạn đã được thêm vào đây */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .truyen-header {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .truyen-header img {
            width: 300px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .truyen-info {
            flex: 1;
        }

        .truyen-info h1 {
            margin: 0;
            font-size: 28px;
            color: #333;
        }

        .truyen-info p {
            margin: 5px 0;
            color: #555;
        }

        .genres span {
            display: inline-block;
            margin: 5px 5px 0 0;
            padding: 5px 10px;
            background-color: #17a2b8;
            color: white;
            border-radius: 5px;
            font-size: 14px;
        }

        .rating-section {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .rating-section p {
            margin: 5px 0;
        }

        .stars {
            display: flex;
            gap: 5px;
            margin: 5px 0;
        }

        .star {
            font-size: 24px;
            color: #ccc;
            cursor: pointer;
            transition: color 0.3s;
        }

        .star:hover,
        .star.hover {
            color: #ffc107;
        }

        .star.selected {
            color: #ffc107;
        }

        .submit-rating-btn {
            padding: 5px 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .submit-rating-btn:hover {
            background-color: #218838;
        }

        .truyen-actions {
            margin-top: 20px;
        }

        .truyen-actions a, .truyen-actions button {
            display: inline-block;
            margin-right: 10px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }

        .truyen-actions button {
            border: none;
            cursor: pointer;
        }

        .truyen-actions .start-reading {
            background-color: #28a745;
        }

        .truyen-actions .add-chapter-btn {
            background-color: #ffc107;
            color: black;
        }

        .truyen-actions .add-to-library {
            background-color: #6c757d;
        }

        .truyen-actions a:hover, .truyen-actions button:hover {
            opacity: 0.8;
        }

        .chapter-list {
            margin-top: 20px;
        }

        .chapter-list h2 {
            margin-bottom: 10px;
            color: #333;
        }

        .chapter-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .chapter-item a {
            text-decoration: none;
            color: #007bff;
        }

        .chapter-item a:hover {
            text-decoration: underline;
        }

        .chapter-meta {
            font-size: 12px;
            color: #888;
            display: block; /* Đảm bảo ngày tạo nằm dưới tên chương */
            margin-top: 5px;
        }

        .chapter-actions a {
            margin-left: 10px;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 12px;
        }

        .chapter-actions .add-image-btn {
            background-color: #28a745;
            color: white;
        }

        .chapter-actions .list-image-btn {
            background-color: #17a2b8;
            color: white;
        }

        .chapter-actions .edit-btn {
            background-color: #ffc107;
            color: black;
        }

        .chapter-actions .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .chapter-actions a:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Main content -->
    <div class="container">
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

                    <a href="../chapter/add.php?id_truyen=<?php echo $id_truyen; ?>" class="add-chapter-btn">Thêm Chapter</a>

                    <button id="follow-button" class="add-to-library" data-followed="<?php echo $is_followed ? 'true' : 'false'; ?>">
                        <?php echo $is_followed ? 'Xóa khỏi thư viện' : 'Thêm vào thư viện'; ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="chapter-list">
            <h2>Danh sách Chương</h2>
            <?php while ($chuong = $chuongs->fetch_assoc()): ?>
                <?php
                // Get max page number for the chapter
                $sql_anh = "SELECT MAX(so_trang) AS so_trang_lon_nhat FROM anh_chuong WHERE id_chuong = ?";
                $stmt_anh = $conn->prepare($sql_anh);
                if (!$stmt_anh) {
                    continue;
                }
                
                $stmt_anh->bind_param("i", $chuong['id_chuong']);
                if (!$stmt_anh->execute()) {
                    $stmt_anh->close();
                    continue;
                }
                
                $result_anh = $stmt_anh->get_result();
                if (!$result_anh) {
                    $stmt_anh->close();
                    continue;
                }
                
                $anh_data = $result_anh->fetch_assoc();
                $so_trang_lon_nhat = (int)($anh_data['so_trang_lon_nhat'] ?? 0);
                $stmt_anh->close();
                ?>
                <div class="chapter-item">
                    <div class="chapter-info">
                        <a href="../chapter/docChapter.php?id_chuong=<?php echo htmlspecialchars($chuong['id_chuong']); ?>" class="chapter-title">
                            Chương <?php echo htmlspecialchars($chuong['so_chuong']); ?>: <?php echo htmlspecialchars($chuong['tieu_de']); ?>
                        </a>
                        <span class="chapter-meta">Ngày tạo: <?php echo date('d/m/Y', strtotime($chuong['ngay_tao'] ?? 'now')); ?></span>
                    </div>
                    <div class="chapter-actions">
                        <a href="../anhChuong/add.php?id_chuong=<?php echo htmlspecialchars($chuong['id_chuong']); ?>&so_trang_bat_dau=<?php echo $so_trang_lon_nhat + 1; ?>" class="add-image-btn">Thêm Trang</a>
                        <a href="../anhChuong/list.php?id_chuong=<?php echo htmlspecialchars($chuong['id_chuong']); ?>" class="list-image-btn">Danh Sách Trang</a>
                        <a href="../chapter/edit.php?id_chuong=<?php echo htmlspecialchars($chuong['id_chuong']); ?>&id_truyen=<?php echo $id_truyen; ?>" class="edit-btn">Sửa</a>
                        <a href="../chapter/delete.php?id_chuong=<?php echo htmlspecialchars($chuong['id_chuong']); ?>&id_truyen=<?php echo $id_truyen; ?>" class="delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa chương này?');">Xóa</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>

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

        // Lấy tất cả các ngôi sao
        const stars = document.querySelectorAll('.star');
        const soSaoInput = document.getElementById('so_sao');

        // Thêm sự kiện hover và click cho từng ngôi sao
        stars.forEach(star => {
            // Khi di chuột qua ngôi sao
            star.addEventListener('mouseover', function () {
                resetStars(); // Xóa trạng thái cũ
                highlightStars(this.getAttribute('data-value')); // Làm nổi bật các ngôi sao
            });

            // Khi click vào ngôi sao
            star.addEventListener('click', function () {
                const value = this.getAttribute('data-value');
                soSaoInput.value = value; // Gán giá trị cho input ẩn
                resetStars();
                highlightStars(value, true); // Làm nổi bật các ngôi sao đã chọn
            });
        });

        // Xóa trạng thái của tất cả các ngôi sao
        function resetStars() {
            stars.forEach(star => {
                star.classList.remove('selected', 'hover');
            });
        }

        // Làm nổi bật các ngôi sao
        function highlightStars(value, select = false) {
            stars.forEach(star => {
                if (star.getAttribute('data-value') <= value) {
                    star.classList.add(select ? 'selected' : 'hover');
                }
            });
        }
    </script>

    <?php
    // Close database resources
    $stmt->close();
    $stmt_chuong->close();
    $stmt_min_chapter->close();
    $stmt_rating->close();
    if (isset($stmt_check_follow)) {
        $stmt_check_follow->close();
    }
    $conn->close();
    ?>
</body>
</html>