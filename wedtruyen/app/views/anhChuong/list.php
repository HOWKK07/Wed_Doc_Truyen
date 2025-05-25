<?php
session_start();
require_once '../../config/connect.php';

// Kiểm tra kết nối database ngay từ đầu
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Kết nối cơ sở dữ liệu không hợp lệ.");
}

// Kiểm tra và load class AnhChuongModel
$modelPath = '../../models/anhChuongModel.php';
if (!file_exists($modelPath)) {
    die("Lỗi: Không tìm thấy file model.");
}
require_once $modelPath;

if (!class_exists('AnhChuongModel')) {
    die("Lỗi: Không tìm thấy class AnhChuongModel");
}

if (!isset($_GET['id_chuong']) || empty($_GET['id_chuong'])) {
    die("Lỗi: Không tìm thấy ID chương.");
}

// Kiểm tra và ép kiểu an toàn
$id_chuong = filter_var($_GET['id_chuong'], FILTER_VALIDATE_INT);
if ($id_chuong === false) {
    die("Lỗi: ID chương không hợp lệ.");
}

// First query - chỉ để kiểm tra, không cần thiết cho logic chính
$stmt = $conn->prepare("SELECT * FROM anh_chuong WHERE id_chuong = ?");
if ($stmt === false) {
    die("Lỗi: Không thể chuẩn bị truy vấn.");
}

$stmt->bind_param("i", $id_chuong);
if (!$stmt->execute()) {
    die("Lỗi: Không thể thực thi truy vấn.");
}

$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo isset($row['ten_chuong']) ? htmlspecialchars($row['ten_chuong']) : "";
    }
} else {
    echo "";
}

// Initialize model với kiểm tra method
$model = new AnhChuongModel($conn);
if (!method_exists($model, 'layDanhSachAnh')) {
    die("Lỗi: Phương thức layDanhSachAnh không tồn tại");
}

$anh_chuongs = $model->layDanhSachAnh($id_chuong);
if (!($anh_chuongs instanceof mysqli_result)) {
    die("Lỗi: Kết quả trả về không hợp lệ");
}

// Lấy thông tin chương và truyện
$sql = "SELECT chuong.tieu_de AS ten_chuong, truyen.ten_truyen, truyen.id_truyen 
        FROM chuong 
        JOIN truyen ON chuong.id_truyen = truyen.id_truyen 
        WHERE chuong.id_chuong = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Lỗi: Không thể chuẩn bị truy vấn thông tin chương.");
}

$stmt->bind_param("i", $id_chuong);
if (!$stmt->execute()) {
    die("Lỗi: Không thể thực thi truy vấn thông tin chương.");
}

$result = $stmt->get_result();
if ($result === false) {
    die("Lỗi: Không thể lấy dữ liệu từ kết quả thông tin chương.");
}

$info = $result->fetch_assoc();
if (!$info) {
    die("Lỗi: Không tìm thấy thông tin chương hoặc truyện.");
}

$ten_chuong = isset($info['ten_chuong']) ? (string)$info['ten_chuong'] : '';
$ten_truyen = isset($info['ten_truyen']) ? (string)$info['ten_truyen'] : '';
$id_truyen = isset($info['id_truyen']) ? (int)$info['id_truyen'] : 0;

// Lấy số trang lớn nhất và danh sách ảnh
$so_trang_lon_nhat = 0;
$anh_list = [];
while ($anh = $anh_chuongs->fetch_assoc()) {
    $anh_list[] = $anh;
    if (isset($anh['so_trang'])) {
        $so_trang = (int)$anh['so_trang'];
        if ($so_trang > $so_trang_lon_nhat) {
            $so_trang_lon_nhat = $so_trang;
        }
    }
}

// Tính toán số trang mới
$so_trang_moi = $so_trang_lon_nhat + 1;
?>

<!DOCTYPE html>
<html lang="vi">
<!-- Phần HTML giữ nguyên như trước -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Trang</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/anhChuong/list.css">
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="container">
        <!-- Hiển thị tên truyện và chương -->
        <div class="info">
            <h2>Truyện: <?php echo isset($ten_truyen) ? htmlspecialchars($ten_truyen) : "Không có giá trị."; ?></h2>
            <h3>Chương: <?php echo isset($ten_chuong) ? htmlspecialchars($ten_chuong) : ""; ?></h3>
        </div>

        <!-- Nút trở về chi tiết truyện -->
        <a href="../truyen/chiTietTruyen.php?id_truyen=<?php echo $id_truyen; ?>" class="back-to-detail-btn">← Trở về chi tiết truyện</a>

        <!-- Nút thêm trang và lưu thứ tự -->
        <a href="add.php?id_chuong=<?php echo $id_chuong; ?>&so_trang_bat_dau=<?php echo $so_trang_moi; ?>" class="add-page-btn">+ Thêm Trang</a>
        <button id="save-order">Lưu thứ tự</button>

        <!-- Hiển thị danh sách trang -->
        <h1>Danh Sách Trang</h1>
        <div class="page-list" id="page-list">
            <?php
            // Lấy dữ liệu audio_trang cho tất cả id_anh của chương này
            $audio_map = [];
            if (!empty($anh_list)) {
                $ids = array_column($anh_list, 'id_anh');
                $ids_str = implode(',', array_map('intval', $ids));
                $sql_audio = "SELECT * FROM audio_trang WHERE id_anh IN ($ids_str)";
                $result_audio = $conn->query($sql_audio);
                if ($result_audio) {
                    while ($row = $result_audio->fetch_assoc()) {
                        $audio_map[$row['id_anh']] = $row;
                    }
                }
            }
            ?>

            <?php foreach ($anh_list as $anh): 
                $audio = isset($audio_map[$anh['id_anh']]) ? $audio_map[$anh['id_anh']] : [];
            ?>
                <div class="page-item" data-id="<?php echo $anh['id_anh']; ?>" draggable="true">
                    <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($anh['duong_dan_anh']); ?>" alt="Trang <?php echo $anh['so_trang']; ?>">
                    <p>Trang: <?php echo $anh['so_trang']; ?></p>
                    <div class="actions">
                        <!-- Thêm/Sửa audio -->
                        <?php if (!empty($audio['duong_dan_audio'])): ?>
                            <audio controls src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($audio['duong_dan_audio']); ?>" style="width:120px;"></audio>
                        <?php else: ?>
                            <form action="upload_audio_trang.php?id_chuong=<?php echo $id_chuong; ?>" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="id_anh" value="<?php echo $anh['id_anh']; ?>">
                                <input type="file" name="audio_file" required>
                                <button type="submit">Tải lên audio</button>
                            </form>
                        <?php endif; ?>
                        <!-- Xem sub -->
                        <?php if (!empty($audio['duong_dan_sub'])): ?>
                            <a href="/Wed_Doc_Truyen/<?php echo htmlspecialchars($audio['duong_dan_sub']); ?>" target="_blank">Xem sub</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Hiển thị danh sách trang -->
        <div>
            <?php
            foreach ($anh_list as $anh) {
                if (is_array($anh) && isset($anh['so_trang'])) {
                    echo "Trang: " . htmlspecialchars($anh['so_trang']);
                }
            }
            ?>
        </div>
    </div>

    <script>
        const pageList = document.getElementById('page-list');
        let draggingItem = null;

        pageList.addEventListener('dragstart', (e) => {
            draggingItem = e.target;
            e.dataTransfer.effectAllowed = 'move';
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

        pageList.addEventListener('dragend', () => {
            draggingItem = null;
        });

        // Lưu thứ tự mới
        document.getElementById('save-order').addEventListener('click', () => {
            const order = [];
            document.querySelectorAll('.page-item').forEach((item, index) => {
                const id = item.getAttribute('data-id');
                order.push({ id: id, so_trang: index + 1 });
            });

            fetch('updateOrder.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(order)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cập nhật lại số trang hiển thị trên giao diện
                    document.querySelectorAll('.page-item').forEach((item, index) => {
                        const p = item.querySelector('p');
                        if (p) p.textContent = 'Trang: ' + (index + 1);
                    });
                    alert('Thứ tự đã được cập nhật!');
                    // location.reload(); // Không cần reload nếu chỉ muốn cập nhật số trang
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