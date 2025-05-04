<?php
require_once '../../models/binhLuanModel.php';

class BinhLuanController {
    private $model;

    public function __construct($conn) {
        $this->model = new BinhLuanModel($conn);
    }

    // Lấy bình luận theo ID truyện
    public function layBinhLuanTheoTruyen($id_truyen) {
        return $this->model->layBinhLuanTheoTruyen($id_truyen);
    }

    // Lấy bình luận theo ID chương
    public function layBinhLuanTheoChuong($id_chuong) {
        return $this->model->layBinhLuanTheoChuong($id_chuong);
    }

    // Thêm bình luận cho truyện
    public function themBinhLuanTruyen($id_truyen, $id_nguoidung, $noi_dung) {
        if (empty($noi_dung)) {
            throw new Exception("Nội dung bình luận không được để trống.");
        }
        return $this->model->themBinhLuanTruyen($id_truyen, $id_nguoidung, $noi_dung);
    }

    // Thêm bình luận cho chương
    public function themBinhLuanChuong($id_chuong, $id_nguoidung, $noi_dung) {
        if (empty($noi_dung)) {
            throw new Exception("Nội dung bình luận không được để trống.");
        }
        return $this->model->themBinhLuanChuong($id_chuong, $id_nguoidung, $noi_dung);
    }
}