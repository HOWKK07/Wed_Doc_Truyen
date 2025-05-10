<?php
session_start();
require_once '../../config/connect.php';
require_once '../../helpers/utils.php';
require_once '../../controllers/truyenController.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Lỗi: Không tìm thấy ID truyện.");
}

$id_truyen = $_GET['id'];
$controller = new TruyenController($conn);

// Lấy thông tin truyện cần sửa
$truyen = $controller->layThongTinTruyen($id_truyen);

// Lấy danh sách loại truyện và thể loại
$loaiTruyen = $controller->layDanhSachLoaiTruyen();
$theLoai = $controller->layDanhSachTheLoai();
$theLoaiDaChon = $controller->layTheLoaiCuaTruyen($id_truyen); // Lấy các thể loại đã chọn

$error_message = ''; // Biến lưu lỗi để hiển thị trên giao diện

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $ten_truyen = $_POST['ten_truyen'];
        $tac_gia = $_POST['tac_gia'];
        $id_loai_truyen = $_POST['id_loai_truyen'];
        $mo_ta = $_POST['mo_ta'];
        $trang_thai = $_POST['trang_thai'];
        $the_loai = $_POST['the_loai'] ?? []; // Lấy danh sách thể loại từ form
        $anh_bia = $truyen['anh_bia']; // Giữ nguyên ảnh bìa cũ

        // Xử lý tải lên ảnh bìa mới (nếu có)
        if (isset($_FILES['anh_bia']) && $_FILES['anh_bia']['error'] === UPLOAD_ERR_OK) {
            $file_extension = pathinfo($_FILES['anh_bia']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = __DIR__ . "/../../../uploads/anhbia/" . $file_name;

            if (move_uploaded_file($_FILES['anh_bia']['tmp_name'], $file_path)) {
                $anh_bia = "uploads/anhbia/" . $file_name;
            } else {
                throw new Exception("Không thể tải lên ảnh bìa.");
            }
        }

        // Gọi phương thức cập nhật truyện
        $controller->capNhatTruyen($id_truyen, $ten_truyen, $tac_gia, $id_loai_truyen, $anh_bia, $mo_ta, $trang_thai, $the_loai);

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Truyện</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/truyen/edit.css">
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <div class="form-container">
            <h1>Sửa Truyện</h1>
            <form action="" method="POST" enctype="multipart/form-data">
                <label for="ten_truyen">Tên truyện</label>
                <input type="text" id="ten_truyen" name="ten_truyen" value="<?php echo htmlspecialchars($truyen['ten_truyen']); ?>" required>

                <label for="tac_gia">Tác giả</label>
                <input type="text" id="tac_gia" name="tac_gia" value="<?php echo htmlspecialchars($truyen['tac_gia']); ?>" required>

                <!-- Loại truyện -->
                <label for="id_loai_truyen">Loại truyện</label>
                <select id="id_loai_truyen" name="id_loai_truyen" required>
                    <?php foreach ($loaiTruyen as $loai): ?>
                        <option value="<?php echo htmlspecialchars($loai['id_loai_truyen']); ?>" <?php echo $loai['id_loai_truyen'] == $truyen['id_loai_truyen'] ? 'selected' : ''; ?>>
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
                        <div id="selected_the_loai_list">
                            <?php foreach ($theLoaiDaChon as $the): ?>
                                <div class="the_loai_selected" data-id="<?php echo $the['id_theloai']; ?>">
                                    <?php echo $the['ten_theloai']; ?>
                                    <span class="remove_the_loai" onclick="removeTheLoai(this)">×</span>
                                    <input type="hidden" name="the_loai[]" value="<?php echo $the['id_theloai']; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <label for="trang_thai">Trạng thái</label>
                <select id="trang_thai" name="trang_thai" required>
                    <option value="Đang ra" <?php echo $truyen['trang_thai'] == 'Đang ra' ? 'selected' : ''; ?>>Đang ra</option>
                    <option value="Hoàn thành" <?php echo $truyen['trang_thai'] == 'Hoàn thành' ? 'selected' : ''; ?>>Hoàn thành</option>
                </select>

                <label for="anh_bia">Ảnh bìa</label>
                <input type="file" id="anh_bia" name="anh_bia" accept="image/*">

                <!-- Thêm trường nhập liệu cho năm phát hành -->
                <label for="nam_phat_hanh">Năm phát hành</label>
                <input type="number" id="nam_phat_hanh" name="nam_phat_hanh" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($truyen['nam_phat_hanh']); ?>" required>

                <label for="mo_ta">Mô tả</label>
                <textarea id="mo_ta" name="mo_ta" rows="5" required><?php echo htmlspecialchars($truyen['mo_ta']); ?></textarea>

                <button type="submit">Cập Nhật Truyện</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>

    <script>
        // Lọc thể loại theo từ khóa
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

            const existingItem = document.querySelector(`#selected_the_loai_list .the_loai_selected[data-id="${id}"]`);
            if (existingItem) {
                alert('Thể loại này đã được chọn.');
                return;
            }

            const selectedList = document.getElementById('selected_the_loai_list');
            const selectedItem = document.createElement('div');
            selectedItem.classList.add('the_loai_selected');
            selectedItem.setAttribute('data-id', id);
            selectedItem.innerHTML = `
                ${name}
                <span class="remove_the_loai" onclick="removeTheLoai(this)">×</span>
                <input type="hidden" name="the_loai[]" value="${id}">
            `;
            selectedList.appendChild(selectedItem);
        }

        // Xóa thể loại khỏi danh sách đã chọn
        function removeTheLoai(element) {
            const selectedItem = element.parentElement;
            selectedItem.remove();
        }
    </script>
</body>
</html>