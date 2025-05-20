<?php
class BinhLuanModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Lấy tất cả bình luận
     * @return array
     */
    public function layTatCaBinhLuan() {
        $sql = "SELECT b.*, n.ten_dang_nhap 
                FROM binhluantruyen b 
                LEFT JOIN nguoidung n ON b.id_nguoidung = n.id_nguoidung
                UNION ALL
                SELECT bc.*, n.ten_dang_nhap 
                FROM binhluanchuong bc 
                LEFT JOIN nguoidung n ON bc.id_nguoidung = n.id_nguoidung
                ORDER BY ngay_binh_luan DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy bình luận theo ID
     * @param int $id
     * @return array|null
     */
    public function layBinhLuanTheoId($id) {
        // Tìm trong bảng bình luận truyện
        $sql = "SELECT b.*, n.ten_dang_nhap 
                FROM binhluantruyen b 
                LEFT JOIN nguoidung n ON b.id_nguoidung = n.id_nguoidung
                WHERE b.id_binh_luan = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            return $result;
        }

        // Nếu không tìm thấy, tìm trong bảng bình luận chương
        $sql = "SELECT bc.*, n.ten_dang_nhap 
                FROM binhluanchuong bc 
                LEFT JOIN nguoidung n ON bc.id_nguoidung = n.id_nguoidung
                WHERE bc.id_binh_luan = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Lấy bình luận theo ID truyện
     * @param int $id_truyen
     * @return array
     */
    public function layBinhLuanTheoTruyen($id_truyen) {
        $sql = "SELECT b.*, n.ten_dang_nhap 
                FROM binhluantruyen b 
                LEFT JOIN nguoidung n ON b.id_nguoidung = n.id_nguoidung
                WHERE b.id_truyen = ?
                ORDER BY b.ngay_binh_luan DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_truyen);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy bình luận theo ID chương
     * @param int $id_chuong
     * @return array
     */
    public function layBinhLuanTheoChuong($id_chuong) {
        $sql = "SELECT bc.*, n.ten_dang_nhap 
                FROM binhluanchuong bc 
                LEFT JOIN nguoidung n ON bc.id_nguoidung = n.id_nguoidung
                WHERE bc.id_chuong = ?
                ORDER BY bc.ngay_binh_luan DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_chuong);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Thêm bình luận cho truyện
     * @param int $id_truyen
     * @param int $id_nguoidung
     * @param string $noi_dung
     * @return int ID của bình luận vừa thêm
     */
    public function themBinhLuanTruyen($id_truyen, $id_nguoidung, $noi_dung) {
        $sql = "INSERT INTO binhluantruyen (id_truyen, id_nguoidung, noi_dung) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $id_truyen, $id_nguoidung, $noi_dung);
        
        if (!$stmt->execute()) {
            throw new Exception("Không thể thêm bình luận: " . $stmt->error);
        }
        
        return $this->conn->insert_id;
    }

    /**
     * Thêm bình luận cho chương
     * @param int $id_chuong
     * @param int $id_nguoidung
     * @param string $noi_dung
     * @return int ID của bình luận vừa thêm
     */
    public function themBinhLuanChuong($id_chuong, $id_nguoidung, $noi_dung) {
        $sql = "INSERT INTO binhluanchuong (id_chuong, id_nguoidung, noi_dung) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $id_chuong, $id_nguoidung, $noi_dung);
        
        if (!$stmt->execute()) {
            throw new Exception("Không thể thêm bình luận: " . $stmt->error);
        }
        
        return $this->conn->insert_id;
    }

    /**
     * Cập nhật bình luận
     * @param int $id
     * @param string $noi_dung
     */
    public function capNhatBinhLuan($id, $noi_dung) {
        // Thử cập nhật trong bảng bình luận truyện
        $sql = "UPDATE binhluantruyen SET noi_dung = ? WHERE id_binh_luan = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $noi_dung, $id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            return;
        }

        // Nếu không tìm thấy, cập nhật trong bảng bình luận chương
        $sql = "UPDATE binhluanchuong SET noi_dung = ? WHERE id_binh_luan = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $noi_dung, $id);
        $stmt->execute();
        
        if ($stmt->affected_rows == 0) {
            throw new Exception("Không tìm thấy bình luận để cập nhật");
        }
    }

    /**
     * Xóa bình luận
     * @param int $id
     */
    public function xoaBinhLuan($id) {
        // Thử xóa trong bảng bình luận truyện
        $sql = "DELETE FROM binhluantruyen WHERE id_binh_luan = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            return;
        }

        // Nếu không tìm thấy, xóa trong bảng bình luận chương
        $sql = "DELETE FROM binhluanchuong WHERE id_binh_luan = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        if ($stmt->affected_rows == 0) {
            throw new Exception("Không tìm thấy bình luận để xóa");
        }
    }
}