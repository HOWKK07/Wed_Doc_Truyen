<?php
require_once '../../config/connect.php';
require_once '../../models/truyenModel.php';

class TruyenController {
    private $model;

    public function __construct($conn) {
        $this->model = new TruyenModel($conn);
    }

    public function themTruyen() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten_truyen = $_POST['ten_truyen'];
            $tac_gia = $_POST['tac_gia'];
            $id_loai_truyen = $_POST['loai_truyen'];
            $the_loai = $_POST['the_loai']; // Đây là mảng các thể loại
            $mo_ta = $_POST['mo_ta'];
            $trang_thai = $_POST['trang_thai'];

            // Xử lý upload ảnh bìa
            $target_dir = "../../uploads/anhbia/";
            $file_extension = pathinfo($_FILES["anh_bia"]["name"], PATHINFO_EXTENSION); // Lấy phần mở rộng của file
            $file_name = $ten_truyen . '.' . $file_extension; // Đổi tên file thành tên truyện
            $file_path = $target_dir . $file_name;

            // Di chuyển file vào thư mục uploads/anhbia
            if (!move_uploaded_file($_FILES["anh_bia"]["tmp_name"], $file_path)) {
                echo "Lỗi: Không thể tải lên ảnh bìa.";
                return;
            }

            // Tạo thư mục cho truyện trong uploads/truyen
            $truyen_folder = "../../uploads/truyen/" . $ten_truyen;
            if (!is_dir($truyen_folder)) {
                if (!mkdir($truyen_folder, 0777, true)) {
                    echo "Lỗi: Không thể tạo thư mục cho truyện.";
                    return;
                }
            }

            // Gọi model để thêm truyện
            $id_truyen = $this->model->themTruyen($ten_truyen, $tac_gia, $id_loai_truyen, $file_name, $mo_ta, $trang_thai);

            if ($id_truyen) {
                // Lưu thể loại vào bảng trung gian
                foreach ($the_loai as $id_theloai) {
                    $this->model->themTheLoaiChoTruyen($id_truyen, $id_theloai);
                }

                header("Location: ../../index.php?success=1");
                exit();
            } else {
                echo "Lỗi: Không thể thêm truyện.";
            }
        }
    }
}
?>