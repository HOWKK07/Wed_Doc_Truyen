<?php
class DanhGiaModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Lấy thông tin đánh giá trung bình và tổng số lượt đánh giá
    public function getRatingByStoryId($id_truyen) {
        $sql = "SELECT AVG(so_sao) AS avg_rating, COUNT(*) AS total_ratings FROM ratings WHERE id_truyen = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_truyen);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Kiểm tra xem người dùng đã đánh giá chưa
    public function checkUserRating($id_truyen, $id_nguoidung) {
        $sql = "SELECT * FROM ratings WHERE id_truyen = ? AND id_nguoidung = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_truyen, $id_nguoidung);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Thêm hoặc cập nhật đánh giá
    public function saveRating($id_truyen, $id_nguoidung, $so_sao) {
        // Kiểm tra nếu người dùng đã đánh giá
        $existingRating = $this->checkUserRating($id_truyen, $id_nguoidung);
        if ($existingRating) {
            // Cập nhật đánh giá
            $sql = "UPDATE ratings SET so_sao = ? WHERE id_truyen = ? AND id_nguoidung = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iii", $so_sao, $id_truyen, $id_nguoidung);
        } else {
            // Thêm đánh giá mới
            $sql = "INSERT INTO ratings (id_truyen, id_nguoidung, so_sao) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iii", $id_truyen, $id_nguoidung, $so_sao);
        }
        return $stmt->execute();
    }
}