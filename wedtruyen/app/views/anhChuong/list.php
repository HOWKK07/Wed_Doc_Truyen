<?php
session_start();
require_once '../../config/connect.php';
require_once '../../models/anhChuongModel.php';

if (!isset($_GET['id_chuong']) || empty($_GET['id_chuong'])) {
    die("Lỗi: Không tìm thấy ID chương.");
}

$id_chuong = $_GET['id_chuong'];

$model = new AnhChuongModel($conn);
$anh_chuongs = $model->layDanhSachAnh($id_chuong);

// Lấy thông tin chương và truyện
$sql = "SELECT chuong.tieu_de AS ten_chuong, truyen.ten_truyen, truyen.id_truyen 
        FROM chuong 
        JOIN truyen ON chuong.id_truyen = truyen.id_truyen 
        WHERE chuong.id_chuong = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_chuong);
$stmt->execute();
$result = $stmt->get_result();
$info = $result->fetch_assoc();

if (!$info) {
    die("Lỗi: Không tìm thấy thông tin chương hoặc truyện.");
}

$ten_chuong = $info['ten_chuong'];
$ten_truyen = $info['ten_truyen'];
$id_truyen = $info['id_truyen'];

// Lấy số trang lớn nhất
$so_trang_lon_nhat = 0;
$anh_list = [];
while ($anh = $anh_chuongs->fetch_assoc()) {
    $anh_list[] = $anh;
    if ($anh['so_trang'] > $so_trang_lon_nhat) {
        $so_trang_lon_nhat = $anh['so_trang'];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Trang</title>
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

        .info {
            text-align: center;
            margin-bottom: 20px;
        }

        .info h2, .info h3 {
            margin: 5px 0;
        }

        .page-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .page-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            cursor: grab;
        }

        .page-item.dragging {
            opacity: 0.5;
            background-color: #f0f0f0;
        }

        .page-item img {
            width: 150px; /* Giới hạn chiều rộng */
            height: auto; /* Tự động điều chỉnh chiều cao theo tỷ lệ */
            border-radius: 5px;
            margin-bottom: 10px;
            object-fit: cover; /* Đảm bảo ảnh không bị méo */
        }

        .page-item p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .actions a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .actions .edit-btn {
            background-color: #ffc107;
            color: black;
        }

        .actions .edit-btn:hover {
            background-color: #e0a800;
        }

        .actions .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .actions .delete-btn:hover {
            background-color: #c82333;
        }

        .add-page-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 2px dashed #28a745;
            border-radius: 5px;
            padding: 20px;
            cursor: pointer;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .add-page-item:hover {
            background-color: #f9f9f9;
            border-color: #218838;
        }

        .add-page-item a {
            text-decoration: none;
            color: #28a745;
            font-weight: bold;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            color: #007bff;
            font-size: 16px;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="container">
        <!-- Hiển thị tên truyện và chương -->
        <div class="info">
            <h2>Truyện: <?php echo htmlspecialchars($ten_truyen); ?></h2>
            <h3>Chương: <?php echo htmlspecialchars($ten_chuong); ?></h3>
        </div>

        <h1>Danh Sách Trang</h1>
        <div class="page-list" id="page-list">
            <?php foreach ($anh_list as $anh): ?>
                <div class="page-item" draggable="true" data-id="<?php echo $anh['id_anh']; ?>">
                    <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($anh['duong_dan_anh']); ?>" alt="Trang <?php echo $anh['so_trang']; ?>">
                    <p>Trang: <?php echo $anh['so_trang']; ?></p>
                    <div class="actions">
                        <a href="edit.php?id_anh=<?php echo $anh['id_anh']; ?>" class="edit-btn">Sửa</a>
                        <a href="delete.php?id_anh=<?php echo $anh['id_anh']; ?>&id_chuong=<?php echo $id_chuong; ?>" class="delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa trang này?');">Xóa</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="add-page-item">
            <a href="add.php?id_chuong=<?php echo $id_chuong; ?>&so_trang_bat_dau=<?php echo $so_trang_lon_nhat + 1; ?>">
                + Thêm Trang
            </a>
        </div>
        <button id="save-order" style="margin-top: 20px; padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Lưu Thứ Tự
        </button>
        <a href="../truyen/chiTietTruyen.php?id_truyen=<?php echo $id_truyen; ?>" class="back-link">Quay lại chi tiết truyện</a>
    </div>

    <script>
        const pageList = document.getElementById('page-list');
        let draggingItem;

        // Kéo thả các phần tử
        pageList.addEventListener('dragstart', (e) => {
            draggingItem = e.target;
            draggingItem.classList.add('dragging');
        });

        pageList.addEventListener('dragend', (e) => {
            draggingItem.classList.remove('dragging');
            draggingItem = null;
        });

        pageList.addEventListener('dragover', (e) => {
            e.preventDefault();
            const afterElement = getDragAfterElement(pageList, e.clientY);
            if (afterElement == null) {
                pageList.appendChild(draggingItem);
            } else {
                pageList.insertBefore(draggingItem, afterElement);
            }
        });

        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.page-item:not(.dragging)')];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        // Lưu thứ tự mới
        document.getElementById('save-order').addEventListener('click', () => {
            const order = [];
            document.querySelectorAll('.page-item').forEach((item, index) => {
                order.push({
                    id: item.getAttribute('data-id'),
                    so_trang: index + 1
                });
            });

            // Gửi yêu cầu AJAX để lưu thứ tự
            fetch('updateOrder.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(order)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Thứ tự đã được cập nhật!');
                    location.reload();
                } else {
                    alert('Có lỗi xảy ra khi cập nhật thứ tự.');
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                alert('Có lỗi xảy ra khi cập nhật thứ tự.');
            });
        });
    </script>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>