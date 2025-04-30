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
            $so_trang = isset($_POST['so_trang']) ? $_POST['so_trang'] : 0; // Đặt giá trị mặc định

            // Kiểm tra file ảnh
            if ($_FILES['anh']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Lỗi tải lên ảnh: " . $_FILES['anh']['error']);
            }

            // Xử lý upload ảnh
            $target_dir = __DIR__ . "/../../../uploads/anhchuong/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true); // Tạo thư mục nếu chưa tồn tại
            }

            $file_extension = pathinfo($_FILES["anh"]["name"], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = __DIR__ . "/../../../uploads/anhchuong/" . $file_name;

            if (!move_uploaded_file($_FILES["anh"]["tmp_name"], $file_path)) {
                throw new Exception("Không thể tải lên ảnh.");
            }

            // Lưu thông tin ảnh vào cơ sở dữ liệu
            $duong_dan_anh = "anhchuong/" . $file_name;
            $result = $this->model->themAnh($id_chuong, $duong_dan_anh, $so_trang);

            if ($result) {
                header("Location: ../truyen/docchapter.php?id_chuong=$id_chuong");
                exit();
            } else {
                throw new Exception("Không thể thêm ảnh.");
            }
        }
    }

    public function themNhieuAnh() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_chuong = $_POST['id_chuong'];
            $so_trang_bat_dau = isset($_POST['so_trang_bat_dau']) ? $_POST['so_trang_bat_dau'] : 0;

            // Kiểm tra file ảnh
            if (!isset($_FILES['anh']) || $_FILES['anh']['error'][0] !== UPLOAD_ERR_OK) {
                throw new Exception("Lỗi tải lên ảnh.");
            }

            $target_dir = __DIR__ . "/../../../uploads/anhchuong/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true); // Tạo thư mục nếu chưa tồn tại
            }

            $files = $_FILES['anh'];

            // Sắp xếp file ảnh theo tên file
            $file_names = $files['name'];
            array_multisort($file_names, SORT_NATURAL | SORT_FLAG_CASE, $files['tmp_name'], $files['error'], $files['size']);

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

            header("Location: list.php?id_chuong=$id_chuong");
            exit();
        }
    }

    public function suaAnh() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_anh = $_POST['id_anh'];
            $so_trang = $_POST['so_trang'];

            // Kiểm tra xem có ảnh mới không
            if (isset($_FILES['anh_moi']) && $_FILES['anh_moi']['error'] === UPLOAD_ERR_OK) {
                $target_dir = __DIR__ . "/../../../uploads/anhchuong/";
                $file_extension = pathinfo($_FILES["anh_moi"]["name"], PATHINFO_EXTENSION);
                $file_name = uniqid() . '.' . $file_extension;
                $file_path = $target_dir . $file_name;

                if (!move_uploaded_file($_FILES["anh_moi"]["tmp_name"], $file_path)) {
                    throw new Exception("Không thể tải lên ảnh mới.");
                }

                // Lưu đường dẫn ảnh mới
                $duong_dan_anh_moi = "uploads/anhchuong/" . $file_name;
                $this->model->suaAnh($id_anh, $duong_dan_anh_moi, $so_trang);
            } else {
                // Nếu không có ảnh mới, chỉ cập nhật số trang
                $this->model->suaAnh($id_anh, null, $so_trang);
            }

            header("Location: ../anhChuong/list.php?id_chuong=" . $_POST['id_chuong']);
            exit();
        }
    }

    public function xoaAnh() {
        if (isset($_GET['id_anh']) && isset($_GET['id_chuong'])) {
            $id_anh = $_GET['id_anh'];
            $id_chuong = $_GET['id_chuong'];

            // Lấy thông tin ảnh để xóa file
            $anh = $this->model->layThongTinAnh($id_anh);
            if ($anh) {
                $file_path = __DIR__ . "/../../../" . $anh['duong_dan_anh'];
                if (file_exists($file_path)) {
                    unlink($file_path); // Xóa file ảnh
                }

                $this->model->xoaAnh($id_anh);
            }

            header("Location: ../anhChuong/list.php?id_chuong=$id_chuong");
            exit();
        }
    }
}
?>