<?php
require_once '../../config/connect.php';
require_once '../../models/truyenModel.php';
require_once '../../helpers/utils.php'; // Đường dẫn đến file chứa hàm safeOutput()

class TruyenController {
    private $model;

    public function __construct($conn) {
        $this->model = new TruyenModel($conn);
    }

    public function themTruyen() {
        checkAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $ten_truyen = safeOutput($_POST['ten_truyen']);
                $tac_gia = safeOutput($_POST['tac_gia']);
                $id_loai_truyen = $_POST['id_loai_truyen'];
                $mo_ta = safeOutput($_POST['mo_ta']);
                $trang_thai = $_POST['trang_thai'];
                $nam_phat_hanh = $_POST['nam_phat_hanh']; // Lấy năm phát hành
                $the_loai = $_POST['the_loai'] ?? [];
                $anh_bia = null;

                // Xử lý tải lên ảnh bìa
                if (isset($_FILES['anh_bia']) && $_FILES['anh_bia']['error'] === UPLOAD_ERR_OK) {
                    $file_extension = pathinfo($_FILES['anh_bia']['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid() . '.' . $file_extension;
                    $file_path = __DIR__ . "/../../../uploads/anhbia/" . $file_name;

                    if (move_uploaded_file($_FILES['anh_bia']['tmp_name'], $file_path)) {
                        $anh_bia = "uploads/anhbia/" . $file_name;
                    } else {
                        throw new Exception("Không thể tải lên ảnh bìa.");
                    }
                }

                // Gọi model để thêm truyện
                $id_truyen = $this->model->themTruyen($ten_truyen, $tac_gia, $id_loai_truyen, $anh_bia, $mo_ta, $trang_thai, $nam_phat_hanh);

                // Gọi model để thêm thể loại
                foreach ($the_loai as $id_theloai) {
                    $this->model->themTheLoaiChoTruyen($id_truyen, $id_theloai);
                }

                // Chuyển hướng về trang chủ
                header("Location: ../../index.php?success=1");
                exit();
            } catch (Exception $e) {
                echo "<p style='color: red;'>Lỗi: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }

    /**
     * Lấy danh sách loại truyện
     * @return array
     */
    public function layDanhSachLoaiTruyen() {
        $sql = "SELECT * FROM loai_truyen";
        $result = $this->model->getConnection()->query($sql); // Sử dụng getter
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy danh sách thể loại
     * @return array
     */
    public function layDanhSachTheLoai() {
        $sql = "SELECT * FROM theloai";
        $result = $this->model->getConnection()->query($sql); // Sử dụng getter
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy thông tin truyện
     * @param int $id_truyen
     * @return array
     */
    public function layThongTinTruyen($id_truyen) {
        return $this->model->layThongTinTruyen($id_truyen);
    }

    /**
     * Lấy thể loại đã chọn
     * @param int $id_truyen
     * @return array
     */
    public function layTheLoaiDaChon($id_truyen) {
        $sql = "SELECT id_theloai FROM truyen_theloai WHERE id_truyen = ?";
        $stmt = $this->model->getConnection()->prepare($sql); // Sử dụng getter
        $stmt->bind_param("i", $id_truyen);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Lấy thể loại của truyện
     * @param int $id_truyen
     * @return array
     */
    public function layTheLoaiCuaTruyen($id_truyen) {
        return $this->model->layTheLoaiCuaTruyen($id_truyen);
    }

    /**
     * Cập nhật truyện
     * @param int $id_truyen
     * @param string $ten_truyen
     * @param string $tac_gia
     * @param int $id_loai_truyen
     * @param string $anh_bia
     * @param string $mo_ta
     * @param int $trang_thai
     * @param array $the_loai
     * @param int $nam_phat_hanh
     */
    public function capNhatTruyen($ten_truyen, $tac_gia, $id_loai_truyen, $anh_bia, $mo_ta, $trang_thai, $the_loai, $nam_phat_hanh) {
        $this->model->capNhatTruyen($ten_truyen, $tac_gia, $id_loai_truyen, $anh_bia, $mo_ta, $trang_thai, $the_loai, $nam_phat_hanh);

        // Cập nhật thể loại
        $this->model->xoaTheLoaiTruyen($id_truyen);
        foreach ($the_loai as $id_theloai) {
            $this->model->themTheLoaiChoTruyen($id_truyen, $id_theloai);
        }

        // Chuyển hướng về trang quản lý truyện
        header("Location: list.php?success=1");
        exit();
    }
}
?>