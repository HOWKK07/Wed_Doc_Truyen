<?php
session_start();
require_once '../../config/connect.php';
require_once '../../helpers/utils.php';
require_once '../../controllers/truyenController.php';

if (!class_exists('TruyenController')) {
    die("TruyenController class not found");
}

if (!isset($conn)) {
    die("Database connection not established");
}

$controller = new TruyenController($conn);

// Lấy danh sách loại truyện và thể loại
$loaiTruyen = $controller->layDanhSachLoaiTruyen();
if (!is_array($loaiTruyen)) {
    $loaiTruyen = [];
}

$theLoai = $controller->layDanhSachTheLoai();
if (!is_array($theLoai)) {
    $theLoai = [];
}

$error_message = ''; // Biến lưu lỗi để hiển thị trên giao diện

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $controller->themTruyen();
        echo "<p style='color: green; text-align: center;'>Thêm truyện thành công!</p>";
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>
<!-- Thêm header -->
<?php include '../shares/header.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Truyện</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/truyen/add.css">
    <script>
        function filterTheLoai() {
            const searchValue = document.getElementById('the_loai_search').value.toLowerCase();
            const items = document.querySelectorAll('.the_loai_item');
            const listContainer = document.getElementById('the_loai_list');

            if (searchValue.trim() === '') {
                listContainer.style.display = 'none';
                return;
            } else {
                listContainer.style.display = 'block';
            }

            let hasVisibleItems = false;
            items.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                if (name.includes(searchValue)) {
                    item.style.display = 'block';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            });

            if (!hasVisibleItems) {
                listContainer.style.display = 'none';
            }
        }

        // Thêm thể loại vào danh sách đã chọn
        function addTheLoai(element) {
            const id = element.getAttribute('data-id');
            const name = element.getAttribute('data-name');

            // Kiểm tra nếu thể loại đã được chọn
            const existingItem = document.querySelector(`#selected_the_loai_list .the_loai_selected[data-id="${id}"]`);
            if (existingItem) {
                alert('Thể loại này đã được chọn.');
                return;
            }

            // Thêm thể loại vào danh sách đã chọn
            const selectedList = document.getElementById('selected_the_loai_list');
            const selectedItem = document.createElement('div');
            selectedItem.classList.add('the_loai_selected');
            selectedItem.setAttribute('data-id', id);
            selectedItem.innerHTML = `
                ${name}
                <span class="remove_the_loai" onclick="removeTheLoai(this)">×</span>
            `;
            selectedList.appendChild(selectedItem);

            // Thêm input ẩn để gửi dữ liệu
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'the_loai[]';
            hiddenInput.value = id;
            selectedItem.appendChild(hiddenInput);
        }

        // Xóa thể loại khỏi danh sách đã chọn
        function removeTheLoai(element) {
            const selectedItem = element.parentElement;
            selectedItem.remove();
        }
    </script>
</head>
<body>
    <div class="content">
        <form action="" method="POST" enctype="multipart/form-data">
            <h2>Thêm Truyện Mới</h2>

            <!-- Hiển thị lỗi nếu có -->
            <?php if (!empty($error_message)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>

            <label for="ten_truyen">Tên truyện</label>
            <input type="text" id="ten_truyen" name="ten_truyen" required>

            <label for="tac_gia">Tác giả</label>
            <input type="text" id="tac_gia" name="tac_gia" required>

            <label for="id_loai_truyen">Loại truyện</label>
            <select id="id_loai_truyen" name="id_loai_truyen" required>
                <?php foreach ($loaiTruyen as $loai): ?>
                    <option value="<?php echo $loai['id_loai_truyen']; ?>">
                        <?php echo htmlspecialchars($loai['ten_loai_truyen']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Thể loại -->
            <label for="the_loai">Thể loại</label>
            <div id="the_loai_container">
                <!-- Ô tìm kiếm -->
                <input type="text" id="the_loai_search" placeholder="Tìm thể loại..." oninput="filterTheLoai()">

                <!-- Danh sách thể loại -->
                <div id="the_loai_list" style="display: none;">
                    <?php foreach ($theLoai as $the): ?>
                        <div class="the_loai_item" data-id="<?php echo $the['id_theloai']; ?>" data-name="<?php echo $the['ten_theloai']; ?>" onclick="addTheLoai(this)">
                            <?php echo $the['ten_theloai']; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Danh sách thể loại đã chọn -->
                <div id="selected_the_loai">
                    <h4>Thể loại đã chọn:</h4>
                    <div id="selected_the_loai_list"></div>
                </div>
            </div>

            <label for="trang_thai">Trạng thái</label>
            <select id="trang_thai" name="trang_thai" required>
                <option value="Đang ra">Đang ra</option>
                <option value="Hoàn thành">Hoàn thành</option>
            </select>

            <label for="anh_bia">Ảnh bìa</label>
            <input type="file" id="anh_bia" name="anh_bia" accept="image/*" required>

            <!-- Thêm trường nhập liệu cho năm phát hành -->
            <label for="nam_phat_hanh">Năm phát hành</label>
            <input type="number" id="nam_phat_hanh" name="nam_phat_hanh" min="1900" max="<?php echo date('Y'); ?>" required>

            <label for="mo_ta">Mô tả</label>
            <textarea id="mo_ta" name="mo_ta" rows="5" required></textarea>

            <button type="submit">Thêm Truyện</button>
        </form>
    </div>
    <!-- Thêm footer -->
<?php include '../shares/footer.php'; ?>
</body>
</html>

<?php
$conn->close(); // Đóng kết nối cơ sở dữ liệu
?>