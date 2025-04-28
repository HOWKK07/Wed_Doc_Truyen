<?php
require_once '../../config/connect.php';
require_once '../../models/loaiTruyenModel.php';

class LoaiTruyenController {
    private $model;

    public function __construct($conn) {
        $this->model = new LoaiTruyenModel($conn);
    }

    // Xử lý thêm loại truyện
    public function themLoaiTruyen() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten_loai_truyen = trim($_POST['ten_loai_truyen']);

            if (empty($ten_loai_truyen)) {
                throw new Exception("Tên loại truyện không được để trống.");
            }

            // Gọi model để thêm loại truyện
            if (!$this->model->themLoaiTruyen($ten_loai_truyen)) {
                throw new Exception("Không thể thêm loại truyện. Vui lòng thử lại.");
            }

            header("Location: list.php?success=1");
            exit();
        }
    }

    // Xử lý sửa loại truyện
    public function suaLoaiTruyen($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten_loai_truyen = $_POST['ten_loai_truyen'];

            // Gọi model để cập nhật loại truyện
            $this->model->capNhatLoaiTruyen($id, $ten_loai_truyen);
            header("Location: list.php");
            exit();
        }
    }

    // Xử lý xóa loại truyện
    public function xoaLoaiTruyen($id) {
        $this->model->xoaLoaiTruyen($id);
        header("Location: list.php");
        exit();
    }

    // Phương thức để lấy danh sách loại truyện
    public function layDanhSachLoaiTruyen() {
        return $this->model->layDanhSachLoaiTruyen();
    }

    // Phương thức để lấy loại truyện theo ID
    public function layLoaiTruyenTheoId($id) {
        return $this->model->layLoaiTruyenTheoId($id);
    }
}
?>
