<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/../models/binhLuanModel.php';
require_once __DIR__ . '/../helpers/utils.php';

class BinhLuanController {
    private $conn;
    private $binhLuanModel;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->binhLuanModel = new BinhLuanModel($this->conn);
    }

    // API Methods
    public function getAllComments() {
        try {
            $comments = $this->binhLuanModel->layTatCaBinhLuan();
            return json_encode(['status' => 'success', 'data' => $comments]);
        } catch (Exception $e) {
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getCommentById($id) {
        try {
            $comment = $this->binhLuanModel->layBinhLuanTheoId($id);
            if (!$comment) {
                return json_encode(['status' => 'error', 'message' => 'Không tìm thấy bình luận']);
            }
            return json_encode(['status' => 'success', 'data' => $comment]);
        } catch (Exception $e) {
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function createComment($data) {
        try {
            if (empty($data['noi_dung'])) {
                throw new Exception("Nội dung bình luận không được để trống");
            }
            if (empty($data['id_nguoidung'])) {
                throw new Exception("ID người dùng không được để trống");
            }

            $id = null;
            if (!empty($data['id_truyen'])) {
                $id = $this->binhLuanModel->themBinhLuanTruyen(
                    $data['id_truyen'],
                    $data['id_nguoidung'],
                    $data['noi_dung']
                );
            } elseif (!empty($data['id_chuong'])) {
                $id = $this->binhLuanModel->themBinhLuanChuong(
                    $data['id_chuong'],
                    $data['id_nguoidung'],
                    $data['noi_dung']
                );
            } else {
                throw new Exception("Cần cung cấp ID truyện hoặc ID chương");
            }

            return json_encode(['status' => 'success', 'message' => 'Thêm bình luận thành công', 'id' => $id]);
        } catch (Exception $e) {
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function updateComment($id, $data) {
        try {
            if (empty($data['noi_dung'])) {
                throw new Exception("Nội dung bình luận không được để trống");
            }
            
            $this->binhLuanModel->capNhatBinhLuan($id, $data['noi_dung']);
            return json_encode(['status' => 'success', 'message' => 'Cập nhật bình luận thành công']);
        } catch (Exception $e) {
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deleteComment($id) {
        try {
            $this->binhLuanModel->xoaBinhLuan($id);
            return json_encode(['status' => 'success', 'message' => 'Xóa bình luận thành công']);
        } catch (Exception $e) {
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // Lấy bình luận theo ID truyện
    public function layBinhLuanTheoTruyen($id_truyen) {
        return $this->binhLuanModel->layBinhLuanTheoTruyen($id_truyen);
    }

    // Lấy bình luận theo ID chương
    public function layBinhLuanTheoChuong($id_chuong) {
        return $this->binhLuanModel->layBinhLuanTheoChuong($id_chuong);
    }

    // Thêm bình luận cho truyện
    public function themBinhLuanTruyen($id_truyen, $id_nguoidung, $noi_dung) {
        if (empty($noi_dung)) {
            throw new Exception("Nội dung bình luận không được để trống.");
        }
        return $this->binhLuanModel->themBinhLuanTruyen($id_truyen, $id_nguoidung, $noi_dung);
    }

    // Thêm bình luận cho chương
    public function themBinhLuanChuong($id_chuong, $id_nguoidung, $noi_dung) {
        if (empty($noi_dung)) {
            throw new Exception("Nội dung bình luận không được để trống.");
        }
        return $this->binhLuanModel->themBinhLuanChuong($id_chuong, $id_nguoidung, $noi_dung);
    }
}