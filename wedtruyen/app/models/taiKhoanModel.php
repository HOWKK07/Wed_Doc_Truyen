<?php
class TaiKhoanModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Thêm tài khoản mới
    public function dangKy($ten_dang_nhap, $email, $mat_khau) {
        $sql = "INSERT INTO nguoidung (ten_dang_nhap, email, mat_khau) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $ten_dang_nhap, $email, $mat_khau);
        return $stmt->execute();
    }

    // Lấy thông tin tài khoản theo tên đăng nhập
    public function layTaiKhoanTheoTenDangNhap($ten_dang_nhap) {
        $sql = "SELECT * FROM nguoidung WHERE ten_dang_nhap = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ten_dang_nhap);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function layTaiKhoanTheoEmail($email) {
        $sql = "SELECT * FROM nguoidung WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function capNhatToken($email, $token) {
        $sql = "UPDATE nguoidung SET reset_token = ? WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $token, $email);
        return $stmt->execute();
    }

    public function capNhatMatKhauBangToken($token, $mat_khau_moi) {
        $sql = "UPDATE nguoidung SET mat_khau = ?, reset_token = NULL WHERE reset_token = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $mat_khau_moi, $token);
        return $stmt->execute();
    }
}
?>