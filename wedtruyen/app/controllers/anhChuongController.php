<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/../models/anhChuongModel.php';

class AnhChuongController {
    private AnhChuongModel $model;

    public function __construct(mysqli $conn) {
        $this->model = new AnhChuongModel($conn);
    }

    public function themAnh(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_chuong = isset($_POST['id_chuong']) ? (int)$_POST['id_chuong'] : null;
            $so_trang = isset($_POST['so_trang']) ? (int)$_POST['so_trang'] : 0;

            if (!$id_chuong) {
                throw new Exception("ID chương không được cung cấp.");
            }

            if (!isset($_FILES['anh']['error']) || $_FILES['anh']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Lỗi tải lên ảnh: " . ($_FILES['anh']['error'] ?? 'Không xác định'));
            }

            $target_dir = __DIR__ . "/../../../uploads/anhchuong/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_extension = pathinfo((string)$_FILES["anh"]["name"], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $target_dir . $file_name;

            if (!move_uploaded_file((string)$_FILES["anh"]["tmp_name"], $file_path)) {
                throw new Exception("Không thể tải lên ảnh.");
            }

            $duong_dan_anh = "anhchuong/" . $file_name;
            $result = $this->model->themAnh($id_chuong, $duong_dan_anh, $so_trang);

            if ($result) {
                header("Location: ../truyen/docchapter.php?id_chuong=" . $id_chuong);
                exit();
            } else {
                throw new Exception("Không thể thêm ảnh.");
            }
        }
    }
}
?>