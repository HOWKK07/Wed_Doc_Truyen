<?php
// filepath: app/models/truyenModel.php
class TruyenModel {
    protected $conn; // Thay đổi từ private thành protected

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Thêm truyện mới
     * @param string $ten_truyen
     * @param string $tac_gia
     * @param int $id_loai_truyen
     * @param string|null $anh_bia
     * @param string $mo_ta
     * @param string $trang_thai
     * @param int $nam_phat_hanh
     * @return int ID của truyện vừa thêm
     */
    public function themTruyen($ten_truyen, $tac_gia, $id_loai_truyen, $anh_bia, $mo_ta, $trang_thai, $nam_phat_hanh) {
        $sql = "INSERT INTO truyen (ten_truyen, tac_gia, id_loai_truyen, anh_bia, mo_ta, trang_thai, nam_phat_hanh) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Lỗi chuẩn bị truy vấn: " . $this->conn->error);
        }

        $stmt->bind_param("ssisssi", $ten_truyen, $tac_gia, $id_loai_truyen, $anh_bia, $mo_ta, $trang_thai, $nam_phat_hanh);

        // Kiểm tra lỗi khi thực thi truy vấn
        if (!$stmt->execute()) {
            throw new Exception("Không thể thêm truyện: " . $stmt->error);
        }

        return $this->conn->insert_id; // Trả về ID của truyện vừa thêm
    }

    /**
     * Cập nhật thông tin truyện
     * @param int $id_truyen
     * @param string $ten_truyen
     * @param string $tac_gia
     * @param int $id_loai_truyen
     * @param string|null $anh_bia
     * @param string $mo_ta
     * @param string $trang_thai
     * @param int $nam_phat_hanh
     */
    public function capNhatTruyen($id_truyen, $ten_truyen, $tac_gia, $id_loai_truyen, $anh_bia, $mo_ta, $trang_thai, $nam_phat_hanh) {
        $sql = "UPDATE truyen SET ten_truyen = ?, tac_gia = ?, id_loai_truyen = ?, anh_bia = ?, mo_ta = ?, trang_thai = ?, nam_phat_hanh = ? WHERE id_truyen = ?";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Lỗi chuẩn bị truy vấn: " . $this->conn->error);
        }

        $stmt->bind_param("ssisssii", $ten_truyen, $tac_gia, $id_loai_truyen, $anh_bia, $mo_ta, $trang_thai, $nam_phat_hanh, $id_truyen);

        if (!$stmt->execute()) {
            throw new Exception("Không thể cập nhật truyện: " . $stmt->error);
        }
    }

    /**
     * Xóa thể loại của truyện
     * @param int $id_truyen
     */
    public function xoaTheLoaiTruyen($id_truyen) {
        $sql = "DELETE FROM truyen_theloai WHERE id_truyen = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_truyen);
        $stmt->execute();
    }

    /**
     * Thêm thể loại cho truyện
     * @param int $id_truyen
     * @param int $id_theloai
     */
    public function themTheLoaiChoTruyen($id_truyen, $id_theloai) {
        $sql = "INSERT INTO truyen_theloai (id_truyen, id_theloai) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_truyen, $id_theloai);

        if (!$stmt->execute()) {
            throw new Exception("Không thể thêm thể loại cho truyện.");
        }
    }

    /**
     * Lấy danh sách loại truyện
     * @return array
     */
    public function layDanhSachLoaiTruyen() {
        $sql = "SELECT * FROM loai_truyen";
        $result = $this->getConnection()->query($sql); // Sử dụng getter
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy danh sách thể loại
     * @return array
     */
    public function layDanhSachTheLoai() {
        $sql = "SELECT * FROM theloai";
        $result = $this->conn->query($sql);

        if (!$result) {
            throw new Exception("Không thể lấy danh sách thể loại.");
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy thông tin truyện
     * @param int $id_truyen
     * @return array|null
     */
    public function layThongTinTruyen($id_truyen) {
        $sql = "SELECT * FROM truyen WHERE id_truyen = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_truyen);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Lấy thể loại của truyện
     * @param int $id_truyen
     * @return array
     */
    public function layTheLoaiCuaTruyen($id_truyen) {
        $sql = "SELECT theloai.id_theloai, theloai.ten_theloai 
                FROM theloai 
                INNER JOIN truyen_theloai ON theloai.id_theloai = truyen_theloai.id_theloai 
                WHERE truyen_theloai.id_truyen = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_truyen);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy tất cả truyện
     * @return array
     */
    public function layTatCaTruyen() {
        $sql = "SELECT * FROM truyen ORDER BY ngay_tao DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Xóa truyện theo ID
     * @param int $id_truyen
     * @return bool
     */
    public function xoaTruyen($id_truyen) {
        // Xóa các bản ghi liên quan trong bảng truyen_theloai
        $this->xoaTheLoaiTruyen($id_truyen);
        
        // Xóa truyện
        $sql = "DELETE FROM truyen WHERE id_truyen = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_truyen);
        
        if (!$stmt->execute()) {
            throw new Exception("Không thể xóa truyện: " . $stmt->error);
        }
        
        return true;
    }

    /**
     * Lấy kết nối cơ sở dữ liệu
     * @return mysqli
     */
    public function getConnection() {
        return $this->conn;
    }

    public function timKiemTruyen($keyword) {
        $sql = "SELECT t.id_truyen, t.ten_truyen, t.tac_gia, t.anh_bia, 
                       (SELECT COUNT(*) FROM chuong WHERE chuong.id_truyen = t.id_truyen) as so_chuong
                FROM truyen t
                WHERE t.ten_truyen LIKE CONCAT('%', ?, '%')
                LIMIT 10";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $keyword);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

$model = new TruyenModel($conn);
?>