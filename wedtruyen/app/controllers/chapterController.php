<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/connect.php';
require_once '../../models/chapterModel.php';

class ChapterController {
    private $model;
    private $conn; // Khai báo thuộc tính $conn

    public function __construct($conn) {
        $this->conn = $conn; // Gán kết nối cơ sở dữ liệu vào $conn
        $this->model = new ChapterModel($conn);
    }

    // Xử lý thêm chapter
    public function themChapter() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_truyen = $_POST['id_truyen'];
            if (isset($_SESSION['user']['id_nguoidung'])) {
                $id_nguoidung = $_SESSION['user']['id_nguoidung'];
            } else {
                throw new Exception("Bạn cần đăng nhập để thêm chương.");
            }
            $so_chuong = $_POST['so_chuong'];
            $tieu_de = $_POST['tieu_de'];

            if (empty($id_truyen) || empty($so_chuong) || empty($tieu_de)) {
                throw new Exception("Vui lòng điền đầy đủ thông tin.");
            }

            $sql = "INSERT INTO chuong (id_truyen, id_nguoidung, so_chuong, tieu_de) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iiis", $id_truyen, $id_nguoidung, $so_chuong, $tieu_de);
            if (!$stmt->execute()) {
                throw new Exception("Không thể thêm chapter.");
            }
            $id_chuong = $this->conn->insert_id;

            // Gửi thông báo cho các user đã theo dõi truyện này
            $stmt = $this->conn->prepare("SELECT id_nguoidung FROM follows WHERE id_truyen = ?");
            $stmt->bind_param("i", $id_truyen);
            $stmt->execute();
            $resultFollows = $stmt->get_result();

            // Lấy tên truyện
            $stmtTenTruyen = $this->conn->prepare("SELECT ten_truyen FROM truyen WHERE id_truyen = ?");
            $stmtTenTruyen->bind_param("i", $id_truyen);
            $stmtTenTruyen->execute();
            $resultTenTruyen = $stmtTenTruyen->get_result();
            $rowTenTruyen = $resultTenTruyen->fetch_assoc();
            $ten_truyen = $rowTenTruyen ? $rowTenTruyen['ten_truyen'] : '';

            // Nội dung thông báo có tên truyện
            $noi_dung = "Truyện <b>$ten_truyen</b> có chương mới: <b>$tieu_de</b>!";
            while ($row = $resultFollows->fetch_assoc()) {
                $id_nguoidung_follow = $row['id_nguoidung'];
                $stmt2 = $this->conn->prepare("INSERT INTO notifications (id_nguoidung, id_chuong, noi_dung) VALUES (?, ?, ?)");
                $stmt2->bind_param("iis", $id_nguoidung_follow, $id_chuong, $noi_dung);
                $stmt2->execute();
            }

            // Sau khi thêm chương thành công:
            $url = "../truyen/chiTietTruyen.php?id_truyen=" . $id_truyen;
            $url = trim($url); // loại bỏ khoảng trắng và ký tự xuống dòng
            header("Location: $url");
            exit;
        }
    }

    // Xử lý sửa chapter
    public function suaChapter() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_chuong = $_POST['id_chuong'];
            $so_chuong = $_POST['so_chuong'];
            $tieu_de = $_POST['tieu_de'];

            if (empty($id_chuong) || empty($so_chuong) || empty($tieu_de)) {
                throw new Exception("Vui lòng điền đầy đủ thông tin.");
            }

            $result = $this->model->suaChapter($id_chuong, $so_chuong, $tieu_de);

            if ($result) {
                $id_truyen = isset($_POST['id_truyen']) ? preg_replace('/[\r\n]+/', '', $_POST['id_truyen']) : '';
                if (!$id_truyen) {
                    throw new Exception("Không tìm thấy ID truyện.");
                }
                $url = "../truyen/chiTietTruyen.php?id_truyen=" . $id_truyen;
                header("Location: $url");
                exit;
            } else {
                throw new Exception("Không thể sửa chapter.");
            }
        }
    }

    // Xử lý xóa chapter
    public function xoaChapter($id_chuong, $id_truyen) {
        $result = $this->model->xoaChapter($id_chuong);

        if ($result) {
            $url = "../truyen/chiTietTruyen.php?id_truyen=$id_truyen";
            $url = trim($url); // loại bỏ khoảng trắng và ký tự xuống dòng
            header("Location: $url");
            exit;
        } else {
            throw new Exception("Không thể xóa chapter.");
        }
    }

    // Lấy danh sách chapter theo ID truyện
    public function layDanhSachChapter($id_truyen) {
        return $this->model->layDanhSachChapter($id_truyen);
    }

    // Lấy thông tin chi tiết của một chapter
    public function layThongTinChapter($id_chuong) {
        $sql = "SELECT c.*, t.ten_truyen 
                FROM chuong c 
                JOIN truyen t ON c.id_truyen = t.id_truyen 
                WHERE c.id_chuong = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_chuong);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Lấy danh sách ảnh theo ID chương
    public function layDanhSachAnh($id_chuong) {
        $model = new AnhChuongModel($this->conn);
        return $model->layDanhSachAnh($id_chuong);
    }

    // Lấy chương trước
    public function layChuongTruoc($id_chuong) {
        $sql = "SELECT id_chuong FROM chuong WHERE id_truyen = (SELECT id_truyen FROM chuong WHERE id_chuong = ?) AND so_chuong < (SELECT so_chuong FROM chuong WHERE id_chuong = ?) ORDER BY so_chuong DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql); // Sử dụng $this->conn
        $stmt->bind_param("ii", $id_chuong, $id_chuong);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['id_chuong'] ?? null;
    }

    // Lấy chương sau
    public function layChuongSau($id_chuong) {
        $sql = "SELECT id_chuong FROM chuong WHERE id_truyen = (SELECT id_truyen FROM chuong WHERE id_chuong = ?) AND so_chuong > (SELECT so_chuong FROM chuong WHERE id_chuong = ?) ORDER BY so_chuong ASC LIMIT 1";
        $stmt = $this->conn->prepare($sql); // Sử dụng $this->conn
        $stmt->bind_param("ii", $id_chuong, $id_chuong);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['id_chuong'] ?? null;
    }
}
?>
