<?php
require_once '../../models/thuVienModel.php';

class ThuVienController {
    private $model;

    public function __construct($conn) {
        $this->model = new ThuVienModel($conn);
    }

    // Lấy danh sách truyện trong thư viện
    public function layThuVien($id_nguoidung) {
        return $this->model->layThuVien($id_nguoidung);
    }

    // Thêm truyện vào thư viện
    public function themVaoThuVien($id_nguoidung, $id_truyen) {
        if (empty($id_truyen) || empty($id_nguoidung)) {
            throw new Exception("ID người dùng hoặc ID truyện không hợp lệ.");
        }
        return $this->model->themVaoThuVien($id_nguoidung, $id_truyen);
    }

    // Xóa truyện khỏi thư viện
    public function xoaKhoiThuVien($id_nguoidung, $id_truyen) {
        if (empty($id_truyen) || empty($id_nguoidung)) {
            throw new Exception("ID người dùng hoặc ID truyện không hợp lệ.");
        }
        return $this->model->xoaKhoiThuVien($id_nguoidung, $id_truyen);
    }
}