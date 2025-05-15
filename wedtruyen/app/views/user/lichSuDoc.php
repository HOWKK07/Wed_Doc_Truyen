<?php
session_start();
require_once '../../config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: /Wed_Doc_Truyen/wedtruyen/app/views/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy lịch sử đọc của người dùng
$sql = "SELECT t.ten_truyen, t.anh_bia, t.id_truyen, l.chuong_doc, l.ngay_doc, 
        (SELECT MAX(so_chuong) FROM chuong WHERE id_truyen = t.id_truyen) as chuong_moi_nhat
        FROM lich_su_doc l
        JOIN truyen t ON l.id_truyen = t.id_truyen
        WHERE l.id_user = ?
        ORDER BY l.ngay_doc DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Đọc Truyện</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/trangChu.css">
    <style>
        .history-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 0 15px;
        }

        .history-item {
            display: flex;
            align-items: center;
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .history-item img {
            width: 100px;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 20px;
        }

        .history-info {
            flex: 1;
        }

        .history-info h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
            color: #333;
        }

        .chapter-status {
            color: #666;
            margin-bottom: 5px;
        }

        .read-time {
            color: #888;
            font-size: 14px;
        }

        .continue-reading {
            background: #007bff;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            transition: background 0.3s;
        }

        .continue-reading:hover {
            background: #0056b3;
        }

        .empty-history {
            text-align: center;
            padding: 50px 0;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <div class="history-container">
        <h1>Lịch Sử Đọc Truyện</h1>
        
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="history-item">
                    <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($row['anh_bia']); ?>" alt="Ảnh bìa">
                    <div class="history-info">
                        <h3><?php echo htmlspecialchars($row['ten_truyen']); ?></h3>
                        <div class="chapter-status">
                            Đã đọc: Chapter <?php echo $row['chuong_doc']; ?> / <?php echo $row['chuong_moi_nhat']; ?>
                        </div>
                        <div class="read-time">
                            <?php
                            $time_diff = time() - strtotime($row['ngay_doc']);
                            if ($time_diff < 3600) {
                                echo floor($time_diff / 60) . ' phút trước';
                            } elseif ($time_diff < 86400) {
                                echo floor($time_diff / 3600) . ' giờ trước';
                            } else {
                                echo floor($time_diff / 86400) . ' ngày trước';
                            }
                            ?>
                        </div>
                        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/docTruyen.php?id_truyen=<?php echo $row['id_truyen']; ?>&chuong=<?php echo $row['chuong_doc']; ?>" class="continue-reading">
                            Tiếp tục đọc
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-history">
                <h2>Chưa có lịch sử đọc truyện</h2>
                <p>Hãy bắt đầu đọc truyện để tạo lịch sử đọc của bạn!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html> 