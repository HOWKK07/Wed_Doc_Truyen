<?php
class AnhChuongModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Thêm ảnh vào chương
    public function themAnh($id_chuong, $duong_dan_anh, $so_trang) {
        $sql = "INSERT INTO anh_chuong (id_chuong, duong_dan_anh, so_trang) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isi", $id_chuong, $duong_dan_anh, $so_trang);

        return $stmt->execute();
    }

    // Lấy danh sách ảnh theo ID chương
    public function layDanhSachAnh($id_chuong) {
        $sql = "SELECT * FROM anh_chuong WHERE id_chuong = ? ORDER BY so_trang ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_chuong);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function layIdTruyenTheoIdChuong($id_chuong) {
        $sql = "SELECT id_truyen FROM chuong WHERE id_chuong = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_chuong);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

public function suaAnh($id_anh, $so_trang, $duong_dan_anh = null) {
        if ($duong_dan_anh) {
            $sql = "UPDATE anh_chuong SET duong_dan_anh = ?, so_trang = ? WHERE id_anh = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sii", $duong_dan_anh, $so_trang, $id_anh);
        } else {
            $sql = "UPDATE anh_chuong SET so_trang = ? WHERE id_anh = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $so_trang, $id_anh);
        }

        return $stmt->execute();
    }

    public function xoaAnh($id_anh) {
        $sql = "DELETE FROM anh_chuong WHERE id_anh = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_anh);

        return $stmt->execute();
    }

    public function layThongTinAnh($id_anh) {
        $sql = "SELECT * FROM anh_chuong WHERE id_anh = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_anh);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // Lấy số trang lớn nhất của chương
    public function laySoTrangLonNhat($id_chuong) {
        $sql = "SELECT MAX(so_trang) AS so_trang_lon_nhat FROM anh_chuong WHERE id_chuong = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_chuong);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['so_trang_lon_nhat'] ?? 0; // Trả về 0 nếu không có trang nào
    }

    public function example($required, $optional = null) {
        // ...
    }

    public function someMethod($so_trang, $duong_dan_anh = null) {
        // ...
    }

    public function suaAnhAjax() {
        $id_anh = (int)$_POST['id_anh'];
        $so_trang = (int)$_POST['so_trang'];
        $model = $this->model;
        $anh = $model->layThongTinAnh($id_anh);
        if (!$anh) throw new Exception('Không tìm thấy ảnh');

        // Nếu có ảnh mới
        if (isset($_FILES['duong_dan_anh']) && $_FILES['duong_dan_anh']['error'] === UPLOAD_ERR_OK) {
            $file_path_cu = __DIR__ . "/../../../" . $anh['duong_dan_anh'];
            if (file_exists($file_path_cu)) unlink($file_path_cu);

            $target_dir = __DIR__ . "/../../../uploads/anhchuong/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $file_extension = pathinfo($_FILES["duong_dan_anh"]["name"], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path_moi = $target_dir . $file_name;

            if (!move_uploaded_file($_FILES["duong_dan_anh"]["tmp_name"], $file_path_moi)) {
                throw new Exception('Không thể tải lên ảnh mới.');
            }
            $duong_dan_anh_moi = "uploads/anhchuong/" . $file_name;
            $model->suaAnh($id_anh, $so_trang, $duong_dan_anh_moi);
        } else {
            $model->suaAnh($id_anh, $so_trang);
        }
    }
}
?>