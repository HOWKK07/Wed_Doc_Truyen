<?php
require_once '../../config/connect.php';
require_once '../../models/chapterModel.php';

class ChapterController {
    private $model;
    private $conn; // Khai báo thuộc tính $conn

    public function __construct($conn) {
        $this->conn = $conn; // Gán kết nối cơ sở dữ liệu vào $conn
        $this->model = new ChapterModel($conn);
    }

    // Xử lý thêm chapter
    public function themChapter() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_truyen = $_POST['id_truyen'];
            $so_chuong = $_POST['so_chuong'];
            $tieu_de = $_POST['tieu_de'];

            if (empty($id_truyen) || empty($so_chuong) || empty($tieu_de)) {
                throw new Exception("Vui lòng điền đầy đủ thông tin.");
            }

            $result = $this->model->themChapter($id_truyen, $so_chuong, $tieu_de);

            if ($result) {
                header("Location: ../truyen/chiTietTruyen.php?id_truyen=$id_truyen");
                exit();
            } else {
                throw new Exception("Không thể thêm chapter.");
            }
        }
    }

    // Xử lý sửa chapter
    public function suaChapter() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_chuong = $_POST['id_chuong'];
            $so_chuong = $_POST['so_chuong'];
            $tieu_de = $_POST['tieu_de'];

            if (empty($id_chuong) || empty($so_chuong) || empty($tieu_de)) {
                throw new Exception("Vui lòng điền đầy đủ thông tin.");
            }

            $result = $this->model->suaChapter($id_chuong, $so_chuong, $tieu_de);

            if ($result) {
                header("Location: ../truyen/chiTietTruyen.php?id_truyen=" . $_POST['id_truyen']);
                exit();
            } else {
                throw new Exception("Không thể sửa chapter.");
            }
        }
    }

    // Xử lý xóa chapter
    public function xoaChapter($id_chuong, $id_truyen) {
        $result = $this->model->xoaChapter($id_chuong);

        if ($result) {
            header("Location: ../truyen/chiTietTruyen.php?id_truyen=$id_truyen");
            exit();
        } else {
            throw new Exception("Không thể xóa chapter.");
        }
    }

    // Lấy danh sách chapter theo ID truyện
    public function layDanhSachChapter($id_truyen) {
        return $this->model->layDanhSachChapter($id_truyen);
    }

    // Lấy thông tin chi tiết của một chapter
    public function layThongTinChapter($id_chuong) {
        $sql = "SELECT c.*, t.ten_truyen 
                FROM chuong c 
                JOIN truyen t ON c.id_truyen = t.id_truyen 
                WHERE c.id_chuong = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_chuong);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Lấy danh sách ảnh theo ID chương
    public function layDanhSachAnh($id_chuong) {
        $model = new AnhChuongModel($this->conn);
        return $model->layDanhSachAnh($id_chuong);
    }

    // Lấy chương trước
    public function layChuongTruoc($id_chuong) {
        $sql = "SELECT id_chuong FROM chuong WHERE id_truyen = (SELECT id_truyen FROM chuong WHERE id_chuong = ?) AND so_chuong < (SELECT so_chuong FROM chuong WHERE id_chuong = ?) ORDER BY so_chuong DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql); // Sử dụng $this->conn
        $stmt->bind_param("ii", $id_chuong, $id_chuong);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['id_chuong'] ?? null;
    }

    // Lấy chương sau
    public function layChuongSau($id_chuong) {
        $sql = "SELECT id_chuong FROM chuong WHERE id_truyen = (SELECT id_truyen FROM chuong WHERE id_chuong = ?) AND so_chuong > (SELECT so_chuong FROM chuong WHERE id_chuong = ?) ORDER BY so_chuong ASC LIMIT 1";
        $stmt = $this->conn->prepare($sql); // Sử dụng $this->conn
        $stmt->bind_param("ii", $id_chuong, $id_chuong);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['id_chuong'] ?? null;
    }
}
?>