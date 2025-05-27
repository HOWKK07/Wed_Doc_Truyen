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
            return;
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

    // Xử lý cập nhật ảnh chương qua AJAX
    public function suaAnhAjax() {
        $id_anh = (int)$_POST['id_anh'];
        $so_trang = (int)$_POST['so_trang'];
        $model = new AnhChuongModel($this->conn);
        $anh = $model->layThongTinAnh($id_anh);
        if (!$anh) throw new Exception('Không tìm thấy ảnh');

        // Xử lý đổi ảnh nếu có
        $duong_dan_anh_moi = null;
        if (isset($_FILES['duong_dan_anh']) && $_FILES['duong_dan_anh']['error'] === UPLOAD_ERR_OK) {
            $target_dir = __DIR__ . "/../../../uploads/anhchuong/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $file_extension = pathinfo($_FILES["duong_dan_anh"]["name"], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path_moi = $target_dir . $file_name;
            if (!move_uploaded_file($_FILES["duong_dan_anh"]["tmp_name"], $file_path_moi)) {
                throw new Exception('Không thể tải lên ảnh mới.');
            }
            $duong_dan_anh_moi = "uploads/anhchuong/" . $file_name;
        }

        // Xử lý đổi audio nếu có
        if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
            $audio_dir = __DIR__ . "/../../../uploads/audio_trang/";
            if (!is_dir($audio_dir)) mkdir($audio_dir, 0777, true);
            $audio_ext = pathinfo($_FILES["audio_file"]["name"], PATHINFO_EXTENSION);
            $audio_name = uniqid('audio_') . '.' . $audio_ext;
            $audio_path = $audio_dir . $audio_name;
            if (!move_uploaded_file($_FILES["audio_file"]["tmp_name"], $audio_path)) {
                throw new Exception('Không thể tải lên audio mới.');
            }
            $duong_dan_audio = "uploads/audio_trang/" . $audio_name;

            // Cập nhật hoặc thêm vào bảng audio_trang
            $sql = "INSERT INTO audio_trang (id_anh, duong_dan_audio) VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE duong_dan_audio = VALUES(duong_dan_audio)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("is", $id_anh, $duong_dan_audio);
            $stmt->execute();
        }

        // Cập nhật ảnh chương
        $model->suaAnh($id_anh, $so_trang, $duong_dan_anh_moi);
    }
}
require_once '../../controllers/anhChuongController.php';
$controller = new AnhChuongController($conn);
?>