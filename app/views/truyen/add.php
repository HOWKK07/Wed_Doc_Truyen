<?php
session_start();
require_once '../../config/connect.php'; // Kết nối cơ sở dữ liệu
require_once '../../controllers/truyenController.php';

// Lấy danh sách loại truyện
$sqlLoaiTruyen = "SELECT * FROM loai_truyen";
$resultLoaiTruyen = $conn->query($sqlLoaiTruyen);

// Lấy danh sách thể loại
$sqlTheLoai = "SELECT * FROM theloai";
$resultTheLoai = $conn->query($sqlTheLoai);

$controller = new TruyenController($conn);
$controller->themTruyen(); // Gọi trực tiếp controller để xử lý thêm truyện
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Truyện</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .form-container h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .form-container input,
        .form-container select,
        .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #0056b3;
        }

        #the_loai_container {
            margin-bottom: 15px;
        }

        #the_loai_search {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        #the_loai_list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            background-color: #f9f9f9;
        }

        .the_loai_item {
            padding: 5px;
            cursor: pointer;
            border-bottom: 1px solid #ddd;
        }

        .the_loai_item:hover {
            background-color: #007bff;
            color: white;
        }

        #selected_the_loai {
            margin-top: 15px;
        }

        #selected_the_loai_list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .the_loai_selected {
            display: flex;
            align-items: center;
            padding: 5px 10px;
            border: 1px solid #ccc;
            border-radius: 20px;
            background-color: #f9f9f9;
            font-size: 14px;
            color: #333;
        }

        .remove_the_loai {
            margin-left: 10px;
            font-size: 16px;
            font-weight: bold;
            color: #dc3545;
            cursor: pointer;
        }

        .remove_the_loai:hover {
            color: #c82333;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <div class="form-container">
            <h1>Thêm Truyện</h1>
            <form action="" method="POST" enctype="multipart/form-data">
                <label for="ten_truyen">Tên truyện</label>
                <input type="text" id="ten_truyen" name="ten_truyen" placeholder="Nhập tên truyện" required>

                <label for="tac_gia">Tác giả</label>
                <input type="text" id="tac_gia" name="tac_gia" placeholder="Nhập tên tác giả" required>

                <label for="the_loai">Thể loại</label>
                <div id="the_loai_container">
                    <input type="text" id="the_loai_search" placeholder="Tìm thể loại..." oninput="filterTheLoai()">
                    <div id="the_loai_list" style="display: none;">
                        <?php while ($row = $resultTheLoai->fetch_assoc()): ?>
                            <div class="the_loai_item" data-id="<?php echo $row['id_theloai']; ?>" data-name="<?php echo $row['ten_theloai']; ?>" onclick="addTheLoai(this)">
                                <?php echo $row['ten_theloai']; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div id="selected_the_loai">
                        <h4>Thể loại đã chọn:</h4>
                        <div id="selected_the_loai_list"></div>
                    </div>
                </div>

                <label for="loai_truyen">Loại truyện</label>
                <select id="loai_truyen" name="loai_truyen" required>
                    <?php while ($row = $resultLoaiTruyen->fetch_assoc()): ?>
                        <option value="<?php echo $row['id_loai_truyen']; ?>"><?php echo $row['ten_loai_truyen']; ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="trang_thai">Trạng thái</label>
                <select id="trang_thai" name="trang_thai" required>
                    <option value="Đang ra">Đang ra</option>
                    <option value="Hoàn thành">Hoàn thành</option>
                </select>

                <label for="anh_bia">Ảnh bìa</label>
                <input type="file" id="anh_bia" name="anh_bia" accept="image/*" required>

                <label for="mo_ta">Mô tả</label>
                <textarea id="mo_ta" name="mo_ta" rows="5" placeholder="Nhập mô tả truyện" required></textarea>

                <button type="submit">Thêm Truyện</button>
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

            // Hiển thị danh sách nếu có từ khóa, ẩn nếu không có
            if (searchValue.trim() === '') {
                listContainer.style.display = 'none';
                return;
            } else {
                listContainer.style.display = 'block';
            }

            // Lọc danh sách thể loại
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

            // Ẩn danh sách nếu không có mục nào phù hợp
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
</body>
</html>

<?php
$conn->close(); // Đóng kết nối cơ sở dữ liệu
?>