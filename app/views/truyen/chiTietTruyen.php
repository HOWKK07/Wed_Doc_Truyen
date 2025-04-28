<?php
session_start();
require_once '../../config/connect.php';
require_once '../../models/chapterModel.php';

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
$sql_chuong = "SELECT * FROM chuong WHERE id_truyen = ? ORDER BY so_chuong ASC";
$stmt_chuong = $conn->prepare($sql_chuong);
$stmt_chuong->bind_param("i", $id_truyen);
$stmt_chuong->execute();
$chuongs = $stmt_chuong->get_result();
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
            background-color: #007bff;
            color: white;
        }

        .truyen-actions .start-reading:hover {
            background-color: #0056b3;
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
            <img src="../../uploads/anhbia/<?php echo htmlspecialchars($truyen['anh_bia']); ?>" alt="<?php echo htmlspecialchars($truyen['ten_truyen']); ?>">
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
                <p><strong>Mô tả:</strong> <?php echo nl2br(htmlspecialchars($truyen['mo_ta'])); ?></p>
                <div class="truyen-actions">
                    <button class="add-to-library">Thêm vào thư viện</button>
                    <button class="start-reading">Bắt đầu đọc</button>
                    <a href="../chapter/add.php?id_truyen=<?php echo $id_truyen; ?>&ten_truyen=<?php echo urlencode($truyen['ten_truyen']); ?>" class="add-chapter-btn">Thêm Chapter</a>
                </div>
            </div>
        </div>

        <div class="chapter-list">
            <h2>Danh sách Chương</h2>
            <?php while ($chuong = $chuongs->fetch_assoc()): ?>
                <div class="chapter-item">
                    <div class="chapter-info">
                        <a href="docchapter.php?id_chuong=<?php echo $chuong['id_chuong']; ?>" class="chapter-title">
                            Chương <?php echo htmlspecialchars($chuong['so_chuong']); ?>: <?php echo htmlspecialchars($chuong['tieu_de']); ?>
                        </a>
                        <span class="chapter-meta">Ngày tạo: <?php echo date('d/m/Y', strtotime($chuong['ngay_tao'])); ?></span>
                    </div>
                    <div class="chapter-actions">
                        <span><?php echo rand(0, 100); ?> lượt xem</span>
                        <div class="comment-count">
                            <i class="fas fa-comment"></i>
                            <span><?php echo rand(0, 10); ?> bình luận</span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>