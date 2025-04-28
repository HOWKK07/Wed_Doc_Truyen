<?php
require_once '../../config/connect.php';
require_once '../../models/anhChuongModel.php';

class AnhChuongController {
    private $model;

    public function __construct($conn) {
        $this->model = new AnhChuongModel($conn);
    }

    // Xử lý thêm ảnh
    public function themAnh() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_chuong = $_POST['id_chuong'];
            $so_trang = $_POST['so_trang'];

            // Kiểm tra file ảnh
            if ($_FILES['anh']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Lỗi tải lên ảnh: " . $_FILES['anh']['error']);
            }

            // Xử lý upload ảnh
            $target_dir = __DIR__ . "/../../../uploads/anhchuong/";
            $file_extension = pathinfo($_FILES["anh"]["name"], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $target_dir . $file_name;

            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (!move_uploaded_file($_FILES["anh"]["tmp_name"], $file_path)) {
                throw new Exception("Không thể tải lên ảnh.");
            }

            // Lưu thông tin ảnh vào cơ sở dữ liệu
            $duong_dan_anh = "uploads/anhchuong/" . $file_name;
            $result = $this->model->themAnh($id_chuong, $duong_dan_anh, $so_trang);

            if ($result) {
                header("Location: ../chapter/docchapter.php?id_chuong=$id_chuong");
                exit();
            } else {
                throw new Exception("Không thể thêm ảnh.");
            }
        }
    }

    public function themNhieuAnh() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_chuong = $_POST['id_chuong'];
            $so_trang_bat_dau = $_POST['so_trang'];

            // Lấy ID truyện từ chương thông qua model
            $chuong = $this->model->layIdTruyenTheoIdChuong($id_chuong);
            if (!$chuong) {
                throw new Exception("Không tìm thấy chương với ID: $id_chuong");
            }
            $id_truyen = $chuong['id_truyen'];

            // Kiểm tra file ảnh
            if (!isset($_FILES['anh']) || $_FILES['anh']['error'][0] !== UPLOAD_ERR_OK) {
                throw new Exception("Lỗi tải lên ảnh.");
            }

            $target_dir = __DIR__ . "/../../../uploads/anhchuong/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $files = $_FILES['anh'];
            $so_trang_hien_tai = $so_trang_bat_dau;

            for ($i = 0; $i < count($files['name']); $i++) {
                $file_extension = pathinfo($files["name"][$i], PATHINFO_EXTENSION);
                $file_name = uniqid() . '.' . $file_extension;
                $file_path = $target_dir . $file_name;

                if (!move_uploaded_file($files["tmp_name"][$i], $file_path)) {
                    throw new Exception("Không thể tải lên ảnh: " . $files["name"][$i]);
                }

                // Lưu thông tin ảnh vào cơ sở dữ liệu
                $duong_dan_anh = "uploads/anhchuong/" . $file_name;
                $this->model->themAnh($id_chuong, $duong_dan_anh, $so_trang_hien_tai);
                $so_trang_hien_tai++;
            }

            // Chuyển hướng về trang chi tiết truyện
            header("Location: ../truyen/chiTietTruyen.php?id_truyen=$id_truyen");
            exit();
        }
    }
}
?>