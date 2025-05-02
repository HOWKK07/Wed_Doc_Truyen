<?php
session_start();
require_once '../../config/connect.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../taiKhoan/login.php");
    exit();
}

// Validate database connection
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection error");
}

// Validate user session data
if (!isset($_SESSION['user']['id_nguoidung']) || !is_numeric($_SESSION['user']['id_nguoidung'])) {
    die("Invalid user data");
}

$id_nguoidung = (int)$_SESSION['user']['id_nguoidung'];

// Get saved stories
$sql = "SELECT truyen.id_truyen, truyen.ten_truyen, truyen.anh_bia 
        FROM follows 
        JOIN truyen ON follows.id_truyen = truyen.id_truyen 
        WHERE follows.id_nguoidung = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database query preparation failed");
}

$stmt->bind_param("i", $id_nguoidung);

if (!$stmt->execute()) {
    die("Database query execution failed");
}

$result = $stmt->get_result();

if (!$result) {
    die("Failed to get query result");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện của tôi</title>
    <style>
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

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .truyen-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .truyen-item {
            position: relative;
            width: 200px;
            text-align: center;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .truyen-item img {
            width: 100%;
            height: 270px;
            object-fit: cover;
        }

        .truyen-item h3 {
            font-size: 16px;
            margin: 10px 0;
            color: #333;
        }

        .delete-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            line-height: 25px;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }
    </style>
    <script>
        function removeFromLibrary(idTruyen) {
            if (confirm('Bạn có chắc chắn muốn xóa truyện này khỏi thư viện?')) {
                fetch('../thuvien/delete.php?id_truyen=' + idTruyen, {
                    method: 'GET'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Xóa truyện khỏi giao diện Thư viện
                        const item = document.querySelector(`.truyen-item[data-id="${idTruyen}"]`);
                        if (item) item.remove();

                        // Cập nhật trạng thái nút trong trang Chi tiết truyện (nếu có)
                        const detailButton = window.opener?.document.getElementById('follow-button');
                        if (detailButton) {
                            detailButton.setAttribute('data-followed', 'false');
                            detailButton.textContent = 'Thêm vào thư viện';
                        }

                        alert('Truyện đã được xóa khỏi thư viện!');
                    } else {
                        alert(data.message || 'Có lỗi xảy ra.');
                    }
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                    alert('Có lỗi xảy ra.');
                });
            }
        }
    </script>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="container">
        <h1>Thư viện của tôi</h1>
        <div class="truyen-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php 
                    $id_truyen = htmlspecialchars((string)$row['id_truyen']);
                    $ten_truyen = htmlspecialchars($row['ten_truyen']);
                    $anh_bia = htmlspecialchars($row['anh_bia']);
                    ?>
                    <div class="truyen-item" data-id="<?php echo $id_truyen; ?>">
                        <!-- Nút X để xóa -->
                        <button class="delete-btn" onclick="removeFromLibrary(<?php echo $id_truyen; ?>)">×</button>
                        <a href="../truyen/chiTietTruyen.php?id_truyen=<?php echo $id_truyen; ?>">
                            <img src="/Wed_Doc_Truyen/<?php echo $anh_bia; ?>" alt="<?php echo $ten_truyen; ?>">
                        </a>
                        <h3><?php echo $ten_truyen; ?></h3>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #555;">Bạn chưa thêm truyện nào vào thư viện.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>

    <?php
    // Close database resources
    $stmt->close();
    $conn->close();
    ?>
</body>
</html>