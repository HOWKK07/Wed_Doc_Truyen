<?php
class TaiKhoanModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Lấy tất cả người dùng
     * @return array
     */
    public function layTatCaNguoiDung() {
        $sql = "SELECT id_nguoidung, ten_dang_nhap, email, vai_tro, ngay_tao FROM nguoidung";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy thông tin người dùng theo ID
     * @param int $id_nguoidung
     * @return array|null
     */
    public function layThongTinNguoiDung($id_nguoidung) {
        $sql = "SELECT id_nguoidung, ten_dang_nhap, email, vai_tro, ngay_tao FROM nguoidung WHERE id_nguoidung = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_nguoidung);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Thêm người dùng mới
     * @param string $ten_dang_nhap
     * @param string $mat_khau
     * @param string $email
     * @param string $vai_tro
     * @return int ID của người dùng vừa thêm
     */
    public function themNguoiDung($ten_dang_nhap, $mat_khau, $email, $vai_tro) {
        // Kiểm tra tên đăng nhập đã tồn tại chưa
        if ($this->layTaiKhoanTheoTenDangNhap($ten_dang_nhap)) {
            throw new Exception("Tên đăng nhập đã tồn tại");
        }

        // Kiểm tra email đã tồn tại chưa
        if ($this->layTaiKhoanTheoEmail($email)) {
            throw new Exception("Email đã tồn tại");
        }

        $sql = "INSERT INTO nguoidung (ten_dang_nhap, mat_khau, email, vai_tro) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $ten_dang_nhap, $mat_khau, $email, $vai_tro);

        if (!$stmt->execute()) {
            throw new Exception("Không thể thêm người dùng: " . $stmt->error);
        }

        return $this->conn->insert_id;
    }

    /**
     * Cập nhật thông tin người dùng
     * @param int $id_nguoidung
     * @param string $email
     * @param string $vai_tro
     * @param string|null $mat_khau
     */
    public function capNhatNguoiDung($id_nguoidung, $email, $vai_tro, $mat_khau = null) {
        if ($mat_khau) {
            $sql = "UPDATE nguoidung SET email = ?, vai_tro = ?, mat_khau = ? WHERE id_nguoidung = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sssi", $email, $vai_tro, $mat_khau, $id_nguoidung);
        } else {
            $sql = "UPDATE nguoidung SET email = ?, vai_tro = ? WHERE id_nguoidung = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssi", $email, $vai_tro, $id_nguoidung);
        }

        if (!$stmt->execute()) {
            throw new Exception("Không thể cập nhật người dùng: " . $stmt->error);
        }
    }

    /**
     * Xóa người dùng
     * @param int $id_nguoidung
     */
    public function xoaNguoiDung($id_nguoidung) {
        $sql = "DELETE FROM nguoidung WHERE id_nguoidung = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_nguoidung);

        if (!$stmt->execute()) {
            throw new Exception("Không thể xóa người dùng: " . $stmt->error);
        }
    }

    /**
     * Lấy tài khoản theo tên đăng nhập
     * @param string $ten_dang_nhap
     * @return array|null
     */
    public function layTaiKhoanTheoTenDangNhap($ten_dang_nhap) {
        $sql = "SELECT * FROM nguoidung WHERE ten_dang_nhap = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ten_dang_nhap);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Lấy tài khoản theo email
     * @param string $email
     * @return array|null
     */
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