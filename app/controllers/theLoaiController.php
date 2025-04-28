<?php
require_once '../../config/connect.php';
require_once '../../models/theLoaiModel.php';

class TheLoaiController {
    private $model;

    public function __construct($conn) {
        $this->model = new TheLoaiModel($conn);
    }

    // Xử lý thêm thể loại
    public function themTheLoai() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten_theloai = $_POST['ten_theloai'];

            // Gọi model để thêm thể loại
            $result = $this->model->themTheLoai($ten_theloai);

            if ($result) {
                header("Location: list.php?success=1");
                exit();
            } else {
                echo "Lỗi: Không thể thêm thể loại.";
            }
        }
    }

    // Xử lý sửa thể loại
    public function suaTheLoai($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten_theloai = $_POST['ten_theloai'];

            // Gọi model để cập nhật thể loại
            $result = $this->model->capNhatTheLoai($id, $ten_theloai);

            if ($result) {
                header("Location: list.php?success=1");
                exit();
            } else {
                echo "Lỗi: Không thể cập nhật thể loại.";
            }
        }
    }

    // Xử lý xóa thể loại
    public function xoaTheLoai($id) {
        $result = $this->model->xoaTheLoai($id);

        if ($result) {
            header("Location: list.php?success=1");
            exit();
        } else {
            echo "Lỗi: Không thể xóa thể loại.";
        }
    }

    // Phương thức để lấy danh sách thể loại
    public function layDanhSachTheLoai() {
        return $this->model->layDanhSachTheLoai();
    }
}
?>