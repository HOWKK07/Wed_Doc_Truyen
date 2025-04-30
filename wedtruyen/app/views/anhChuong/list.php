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
            cursor: grab;
        }

        .page-item img {
            width: 150px;
            height: auto;
            border-radius: 5px;
        }

        .actions a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .actions .edit-btn {
            background-color: #ffc107;
            color: black;
        }

        .actions .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .actions a:hover {
            opacity: 0.8;
        }

        .add-page-btn, #save-order {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .add-page-btn:hover {
            background-color: #218838;
        }

        #save-order {
            background-color: #007bff;
        }

        #save-order:hover {
            background-color: #0056b3;
        }

        .back-to-detail-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-to-detail-btn:hover {
            background-color: #5a6268;
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

        <!-- Nút trở về chi tiết truyện -->
        <a href="../truyen/chiTietTruyen.php?id_truyen=<?php echo $id_truyen; ?>" class="back-to-detail-btn">← Trở về chi tiết truyện</a>

        <!-- Nút thêm trang và lưu thứ tự -->
        <a href="add.php?id_chuong=<?php echo $id_chuong; ?>&so_trang_bat_dau=<?php echo $so_trang_lon_nhat + 1; ?>" class="add-page-btn">+ Thêm Trang</a>
        <button id="save-order">Lưu Thứ Tự</button>

        <!-- Hiển thị danh sách trang -->
        <h1>Danh Sách Trang</h1>
        <div class="page-list" id="page-list">
            <?php foreach ($anh_list as $anh): ?>
                <div class="page-item" draggable="true" data-id="<?php echo $anh['id_anh']; ?>" data-so-trang="<?php echo $anh['so_trang']; ?>">
                    <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($anh['duong_dan_anh']); ?>" alt="Trang <?php echo $anh['so_trang']; ?>">
                    <p>Trang: <?php echo $anh['so_trang']; ?></p>
                    <div class="actions">
                        <a href="edit.php?id_anh=<?php echo $anh['id_anh']; ?>" class="edit-btn">Sửa</a>
                        <a href="delete.php?id_anh=<?php echo $anh['id_anh']; ?>&id_chuong=<?php echo $id_chuong; ?>" class="delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa trang này?');">Xóa</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        const pageList = document.getElementById('page-list');
        let draggingItem;
        let autoScrollInterval;

        pageList.addEventListener('dragstart', (e) => {
            draggingItem = e.target;
            draggingItem.classList.add('dragging');
        });

        pageList.addEventListener('dragend', (e) => {
            draggingItem.classList.remove('dragging');
            draggingItem = null;

            // Dừng auto-scroll khi kết thúc kéo
            clearInterval(autoScrollInterval);
        });

        pageList.addEventListener('dragover', (e) => {
            e.preventDefault();

            const afterElement = getDragAfterElement(pageList, e.clientY);
            if (afterElement == null) {
                pageList.appendChild(draggingItem);
            } else {
                pageList.insertBefore(draggingItem, afterElement);
            }

            // Auto-scroll khi kéo gần mép trên hoặc mép dưới
            handleAutoScroll(e.clientY);
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

        function handleAutoScroll(mouseY) {
            const scrollMargin = 50; // Khoảng cách từ mép trên/dưới để bắt đầu cuộn
            const scrollSpeed = 10; // Tốc độ cuộn (px mỗi lần)

            // Dừng auto-scroll trước khi thiết lập mới
            clearInterval(autoScrollInterval);

            if (mouseY < scrollMargin) {
                // Cuộn lên
                autoScrollInterval = setInterval(() => {
                    window.scrollBy(0, -scrollSpeed);
                }, 20);
            } else if (mouseY > window.innerHeight - scrollMargin) {
                // Cuộn xuống
                autoScrollInterval = setInterval(() => {
                    window.scrollBy(0, scrollSpeed);
                }, 20);
            }
        }
    </script>

    <script>
        // Lưu thứ tự mới
        document.getElementById('save-order').addEventListener('click', () => {
            const order = [];
            const originalOrder = []; // Lưu thứ tự ban đầu
            const currentOrder = []; // Lưu thứ tự hiện tại

            // Lấy thứ tự ban đầu và hiện tại
            document.querySelectorAll('.page-item').forEach((item, index) => {
                const id = item.getAttribute('data-id');
                originalOrder.push({ id: id, so_trang: item.getAttribute('data-so-trang') });
                currentOrder.push({ id: id, so_trang: index + 1 });
            });

            // So sánh thứ tự ban đầu và hiện tại, chỉ thêm các trang đã thay đổi
            currentOrder.forEach((item, index) => {
                if (item.so_trang != originalOrder[index].so_trang) {
                    order.push(item);
                }
            });

            // Nếu không có thay đổi, không gửi yêu cầu
            if (order.length === 0) {
                alert('Không có thay đổi nào để lưu.');
                return;
            }

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
                        location.reload(); // Tải lại trang để hiển thị thứ tự mới
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
</body>
</html>