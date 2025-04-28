<?php
require_once '../../config/connect.php';
require_once '../../models/chapterModel.php';

class ChapterController {
    private $model;

    public function __construct($conn) {
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
        return $this->model->layThongTinChapter($id_chuong);
    }
}
?>