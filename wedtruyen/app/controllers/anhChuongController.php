<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/../models/anhChuongModel.php';

class AnhChuongController {
    private $conn;
    private $model;

    public function __construct($conn) {
        $this->conn = $conn;
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

    public function themNhieuAnh(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_chuong = isset($_POST['id_chuong']) ? (int)$_POST['id_chuong'] : null;
            if (!$id_chuong) throw new Exception("ID chương không được cung cấp.");
            if (!isset($_FILES['anh']) || !is_array($_FILES['anh']['name'])) throw new Exception("Không có ảnh nào được tải lên.");

            $target_dir = __DIR__ . "/../../../uploads/anhchuong/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            // Lấy số trang lớn nhất hiện tại
            $model = new AnhChuongModel($this->conn);
            $so_trang_lon_nhat = $model->laySoTrangLonNhat($id_chuong);

            foreach ($_FILES['anh']['name'] as $index => $name) {
                if ($_FILES['anh']['error'][$index] !== UPLOAD_ERR_OK) continue;
                $file_extension = pathinfo($name, PATHINFO_EXTENSION);
                $file_name = uniqid() . '.' . $file_extension;
                $file_path = $target_dir . $file_name;
                if (move_uploaded_file($_FILES['anh']['tmp_name'][$index], $file_path)) {
                    $duong_dan_anh = "uploads/anhchuong/" . $file_name;
                    $so_trang = $so_trang_lon_nhat + $index + 1;
                    $model->themAnh($id_chuong, $duong_dan_anh, $so_trang);
                }
            }
            header("Location: list.php?id_chuong=$id_chuong");
            exit();
        }
    }

    public function themNoiDungChuong(mysqli $conn): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_chuong = $_POST['id_chuong'];
            $so_trang = $_POST['so_trang_bat_dau'];
            $noi_dung = $_POST['noi_dung'];

            $sql = "INSERT INTO chuong_noidung (id_chuong, so_trang, noi_dung) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $id_chuong, $so_trang, $noi_dung);

            if ($stmt->execute()) {
                header("Location: list.php?id_chuong=$id_chuong");
                exit();
            } else {
                echo "Lỗi: Không thể thêm trang.";
            }
        }
    }
}
require_once '../../controllers/anhChuongController.php';
$controller = new AnhChuongController($conn);
?>