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
        // Nếu có kiểm tra quyền:
        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] !== 'admin') {
            // Nếu là AJAX (có header JSON), trả về JSON lỗi thay vì redirect
            if (
                isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
            ) {
                echo json_encode(['success' => false, 'error' => 'Bạn không có quyền thực hiện thao tác này!']);
                exit;
            } else {
                header('Location: /path/to/login.php');
                exit;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_truyen = isset($_GET['id_truyen']) ? (int)$_GET['id_truyen'] : 0;
            $so_chuong = isset($_POST['so_chuong']) ? (int)$_POST['so_chuong'] : 0;
            $tieu_de = isset($_POST['tieu_de']) ? trim($_POST['tieu_de']) : '';
            $id_nguoidung = $_SESSION['user']['id_nguoidung'] ?? 0;

            if ($id_truyen <= 0 || $so_chuong <= 0 || $tieu_de === '' || $id_nguoidung <= 0) {
                throw new Exception('Dữ liệu không hợp lệ');
            }

            $result = $this->model->themChapter($id_truyen, $so_chuong, $tieu_de, $id_nguoidung);
            if (!$result) {
                throw new Exception('Không thể thêm chapter');
            }
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
                // Nếu là AJAX thì trả về JSON, nếu không thì redirect
                if (
                    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
                ) {
                    echo json_encode(['success' => true]);
                    exit;
                } else {
                    $id_truyen = isset($_POST['id_truyen']) ? preg_replace('/[\r\n]+/', '', $_POST['id_truyen']) : '';
                    if (!$id_truyen) {
                        throw new Exception("Không tìm thấy ID truyện.");
                    }
                    $url = "../truyen/chiTietTruyen.php?id_truyen=" . $id_truyen;
                    header("Location: $url");
                    exit;
                }
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
