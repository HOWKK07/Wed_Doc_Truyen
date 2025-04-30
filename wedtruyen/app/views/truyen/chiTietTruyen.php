<?php
session_start();
require_once '../../config/connect.php';
require_once '../../models/chapterModel.php';
require_once '../../models/anhChuongModel.php';

$id_truyen = $_GET['id_truyen'];

// Lấy thông tin truyện
$sql = "SELECT truyen.*, GROUP_CONCAT(theloai.ten_theloai SEPARATOR ', ') AS the_loai
        FROM truyen
        LEFT JOIN truyen_theloai ON truyen.id_truyen = truyen_theloai.id_truyen
        LEFT JOIN theloai ON truyen_theloai.id_theloai = theloai.id_theloai
        WHERE truyen.id_truyen = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_truyen);
$stmt->execute();
$truyen = $stmt->get_result()->fetch_assoc();

// Lấy danh sách chương
$sql_chuong = "SELECT * FROM chuong WHERE id_truyen = ? ORDER BY so_chuong DESC";
$stmt_chuong = $conn->prepare($sql_chuong);
$stmt_chuong->bind_param("i", $id_truyen);
$stmt_chuong->execute();
$chuongs = $stmt_chuong->get_result();

// Lấy ID của chapter có số chapter nhỏ nhất
$sql_min_chapter = "SELECT id_chuong FROM chuong WHERE id_truyen = ? ORDER BY so_chuong ASC LIMIT 1";
$stmt_min_chapter = $conn->prepare($sql_min_chapter);
$stmt_min_chapter->bind_param("i", $id_truyen);
$stmt_min_chapter->execute();
$result_min_chapter = $stmt_min_chapter->get_result();
$min_chapter = $result_min_chapter->fetch_assoc();
$id_chuong_min = $min_chapter['id_chuong'] ?? null; // Lấy ID chapter nhỏ nhất hoặc null nếu không có
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($truyen['ten_truyen']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .truyen-header {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .truyen-header img {
            width: 300px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .truyen-info {
            flex: 1;
        }

        .truyen-info h1 {
            font-size: 28px;
            margin: 0 0 10px;
            color: #333;
        }

        .truyen-info p {
            margin: 5px 0;
            color: #555;
        }

        .truyen-info .genres {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .truyen-info .genres span {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
        }

        .truyen-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px; /* Khoảng cách giữa các nút */
        }

        .truyen-actions button,
        .truyen-actions .add-chapter-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }

        .truyen-actions .add-to-library {
            background-color: #28a745;
            color: white;
        }

        .truyen-actions .add-to-library:hover {
            background-color: #218838;
        }

        .truyen-actions .start-reading {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745; /* Màu xanh lá */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            text-align: center;
            cursor: pointer;
        }

        .truyen-actions .start-reading:hover {
            background-color: #218838; /* Màu xanh lá đậm hơn khi hover */
        }

        .truyen-actions .start-reading:disabled {
            background-color: #6c757d; /* Màu xám khi bị vô hiệu hóa */
            cursor: not-allowed;
        }

        .truyen-actions .add-chapter-btn {
            background-color: #ffc107;
            color: black;
        }

        .truyen-actions .add-chapter-btn:hover {
            background-color: #e0a800;
        }

        .chapter-list {
            margin-top: 30px;
        }

        .chapter-list h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .chapter-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            margin-bottom: 10px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s;
        }

        .chapter-item:hover {
            background-color: #f9f9f9;
        }

        .chapter-item .chapter-info {
            display: flex;
            flex-direction: column;
        }

        .chapter-item .chapter-info .chapter-title {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            text-decoration: none;
        }

        .chapter-item .chapter-info .chapter-title:hover {
            text-decoration: underline;
        }

        .chapter-item .chapter-info .chapter-meta {
            font-size: 14px;
            color: #555;
        }

        .chapter-item .chapter-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chapter-item .chapter-actions span {
            font-size: 14px;
            color: #555;
        }

        .chapter-item .chapter-actions .comment-count {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            color: #555;
        }

        .chapter-item .chapter-actions .comment-count i {
            font-size: 16px;
            color: #007bff;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="container">
        <div class="truyen-header">
            <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($truyen['anh_bia']); ?>" alt="Ảnh bìa" style="max-width: 100%; height: auto;">
            <div class="truyen-info">
                <h1><?php echo htmlspecialchars($truyen['ten_truyen']); ?></h1>
                <p><strong>Tác giả:</strong> <?php echo htmlspecialchars($truyen['tac_gia']); ?></p>
                <p><strong>Thể loại:</strong></p>
                <div class="genres">
                    <?php
                    $the_loai = explode(', ', $truyen['the_loai']);
                    foreach ($the_loai as $genre) {
                        echo "<span>" . htmlspecialchars($genre) . "</span>";
                    }
                    ?>
                </div>
                <p><strong>Năm xuất bản:</strong> <?php echo htmlspecialchars($truyen['nam_phat_hanh']); ?></p>
                <p><strong>Trạng thái:</strong> <?php echo htmlspecialchars($truyen['trang_thai']); ?></p>
                <p><strong>Mô tả:</strong> <?php echo nl2br(htmlspecialchars($truyen['mo_ta'])); ?></p>
                <div class="truyen-actions">
                    <button class="add-to-library">Thêm vào thư viện</button>
                    <?php if ($id_chuong_min): ?>
                        <a href="../chapter/docChapter.php?id_chuong=<?php echo $id_chuong_min; ?>" class="start-reading">Bắt đầu đọc</a>
                    <?php else: ?>
                        <button class="start-reading" disabled>Không có chương để đọc</button>
                    <?php endif; ?>
                    <a href="../chapter/add.php?id_truyen=<?php echo $id_truyen; ?>&ten_truyen=<?php echo urlencode($truyen['ten_truyen']); ?>" class="add-chapter-btn">Thêm Chapter</a>
                </div>
            </div>
        </div>

        <div class="chapter-list">
            <h2>Danh sách Chương</h2>
            <?php while ($chuong = $chuongs->fetch_assoc()): ?>
                <?php
                // Tính số trang lớn nhất trong chương
                $sql_anh = "SELECT MAX(so_trang) AS so_trang_lon_nhat FROM anh_chuong WHERE id_chuong = ?";
                $stmt_anh = $conn->prepare($sql_anh);
                $stmt_anh->bind_param("i", $chuong['id_chuong']);
                $stmt_anh->execute();
                $result_anh = $stmt_anh->get_result();
                $so_trang_lon_nhat = $result_anh->fetch_assoc()['so_trang_lon_nhat'] ?? 0;
                ?>
                <div class="chapter-item">
                    <div class="chapter-info">
                        <a href="../chapter/docChapter.php?id_chuong=<?php echo $chuong['id_chuong']; ?>" class="chapter-title">
                            Chương <?php echo htmlspecialchars($chuong['so_chuong']); ?>: <?php echo htmlspecialchars($chuong['tieu_de']); ?>
                        </a>
                        <span class="chapter-meta">Ngày tạo: <?php echo date('d/m/Y', strtotime($chuong['ngay_tao'])); ?></span>
                    </div>
                    <div class="chapter-actions">
                        <a href="../anhChuong/add.php?id_chuong=<?php echo $chuong['id_chuong']; ?>&so_trang_bat_dau=<?php echo $so_trang_lon_nhat + 1; ?>" class="add-image-btn" style="background-color: #28a745; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none;">Thêm Trang</a>
                        <a href="../anhChuong/list.php?id_chuong=<?php echo $chuong['id_chuong']; ?>" class="list-image-btn" style="background-color: #17a2b8; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none;">Danh Sách Trang</a>
                        <a href="../chapter/edit.php?id_chuong=<?php echo $chuong['id_chuong']; ?>&id_truyen=<?php echo $id_truyen; ?>" class="edit-btn" style="background-color: #ffc107; color: black; padding: 5px 10px; border-radius: 5px; text-decoration: none;">Sửa</a>
                        <a href="../chapter/delete.php?id_chuong=<?php echo $chuong['id_chuong']; ?>&id_truyen=<?php echo $id_truyen; ?>" class="delete-btn" style="background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none;" onclick="return confirm('Bạn có chắc chắn muốn xóa chương này?');">Xóa</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>