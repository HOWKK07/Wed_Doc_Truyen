<?php
class TaiKhoanModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Thêm tài khoản mới
    public function dangKy($ten_dang_nhap, $email, $mat_khau) {
        $sql = "INSERT INTO tai_khoan (ten_dang_nhap, email, mat_khau) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $ten_dang_nhap, $email, $mat_khau);
        return $stmt->execute();
    }

    // Lấy thông tin tài khoản theo tên đăng nhập
    public function layTaiKhoanTheoTenDangNhap($ten_dang_nhap) {
        $sql = "SELECT * FROM tai_khoan WHERE ten_dang_nhap = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ten_dang_nhap);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>