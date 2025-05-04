<?php
require_once '../../models/danhGiaModel.php';

class DanhGiaController {
    private $model;

    public function __construct($conn) {
        $this->model = new DanhGiaModel($conn);
    }

    // Lấy thông tin đánh giá
    public function getRating($id_truyen) {
        return $this->model->getRatingByStoryId($id_truyen);
    }

    // Lưu đánh giá
    public function saveRating($id_truyen, $id_nguoidung, $so_sao) {
        if ($so_sao < 1 || $so_sao > 5) {
            throw new Exception("Giá trị đánh giá không hợp lệ. Vui lòng chọn từ 1 đến 5 sao.");
        }
        return $this->model->saveRating($id_truyen, $id_nguoidung, $so_sao);
    }
}