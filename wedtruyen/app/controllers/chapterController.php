<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/connect.php';
require_once '../../models/chapterModel.php';

class ChapterController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new ChapterModel($conn);
    }

    // Xử lý thêm chapter
    public function themChapter() {
        // Kiểm tra quyền
        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] !== 'admin') {
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

            // Bắt đầu transaction
            $this->conn->begin_transaction();

            try {
                // Kiểm tra chapter đã tồn tại chưa
                $check_sql = "SELECT id_chuong FROM chuong WHERE id_truyen = ? AND so_chuong = ?";
                $check_stmt = $this->conn->prepare($check_sql);
                $check_stmt->bind_param("ii", $id_truyen, $so_chuong);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    throw new Exception('Chương ' . $so_chuong . ' đã tồn tại!');
                }

                // Thêm chapter
                $result = $this->model->themChapter($id_truyen, $so_chuong, $tieu_de, $id_nguoidung);
                if (!$result) {
                    throw new Exception('Không thể thêm chapter');
                }
                
                $id_chuong = $result;

                // Lấy thông tin truyện
                $sql_truyen = "SELECT ten_truyen FROM truyen WHERE id_truyen = ?";
                $stmt_truyen = $this->conn->prepare($sql_truyen);
                $stmt_truyen->bind_param("i", $id_truyen);
                $stmt_truyen->execute();
                $truyen = $stmt_truyen->get_result()->fetch_assoc();

                // Tạo thông báo cho người theo dõi
                $noi_dung = 'Truyện "' . $truyen['ten_truyen'] . '" có chương mới: Chương ' . $so_chuong . ' - ' . $tieu_de;
                
                $sql_notify = "INSERT IGNORE INTO notifications (id_nguoidung, noi_dung, id_chuong, loai_thongbao, ngay_tao)
                              SELECT DISTINCT f.id_nguoidung, ?, ?, 'chapter_moi', NOW()
                              FROM follows f
                              WHERE f.id_truyen = ?
                              AND NOT EXISTS (
                                  SELECT 1 FROM notifications n 
                                  WHERE n.id_nguoidung = f.id_nguoidung 
                                  AND n.id_chuong = ?
                                  AND n.loai_thongbao = 'chapter_moi'
                              )";
                              
                $stmt_notify = $this->conn->prepare($sql_notify);
                $stmt_notify->bind_param("siii", $noi_dung, $id_chuong, $id_truyen, $id_chuong);
                $stmt_notify->execute();

                // Commit transaction
                $this->conn->commit();
                
            } catch (Exception $e) {
                // Rollback nếu có lỗi
                $this->conn->rollback();
                throw $e;
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

    // Xử lý xóa chapter - ĐÃ SỬA LỖI FOREIGN KEY
    public function xoaChapter($id_chuong, $id_truyen) {
        // Bắt đầu transaction
        $this->conn->begin_transaction();
        
        try {
            // 1. Xóa tất cả notifications liên quan đến chương này
            $sql_delete_notifications = "DELETE FROM notifications WHERE id_chuong = ?";
            $stmt = $this->conn->prepare($sql_delete_notifications);
            $stmt->bind_param("i", $id_chuong);
            $stmt->execute();
            
            // 2. Xóa tất cả audio_trang liên quan (nếu có)
            $sql_delete_audio = "DELETE at FROM audio_trang at 
                                INNER JOIN anh_chuong ac ON at.id_anh = ac.id_anh 
                                WHERE ac.id_chuong = ?";
            $stmt = $this->conn->prepare($sql_delete_audio);
            $stmt->bind_param("i", $id_chuong);
            $stmt->execute();
            
            // 3. Xóa tất cả ảnh của chương
            $sql_delete_images = "DELETE FROM anh_chuong WHERE id_chuong = ?";
            $stmt = $this->conn->prepare($sql_delete_images);
            $stmt->bind_param("i", $id_chuong);
            $stmt->execute();
            
            // 4. Xóa tất cả bình luận của chương
            $sql_delete_comments = "DELETE FROM chapter_comments WHERE id_chuong = ?";
            $stmt = $this->conn->prepare($sql_delete_comments);
            $stmt->bind_param("i", $id_chuong);
            $stmt->execute();
            
            // 5. Xóa lịch sử đọc của chương
            $sql_delete_history = "DELETE FROM lich_su_doc WHERE id_chuong = ?";
            $stmt = $this->conn->prepare($sql_delete_history);
            $stmt->bind_param("i", $id_chuong);
            $stmt->execute();
            
            // 6. Cuối cùng mới xóa chương
            $result = $this->model->xoaChapter($id_chuong);
            
            if ($result) {
                // Commit transaction nếu thành công
                $this->conn->commit();
                
                $url = "../truyen/chiTietTruyen.php?id_truyen=$id_truyen";
                $url = trim($url);
                header("Location: $url");
                exit;
            } else {
                throw new Exception("Không thể xóa chapter.");
            }
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $this->conn->rollback();
            throw $e;
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
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_chuong, $id_chuong);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['id_chuong'] ?? null;
    }

    // Lấy chương sau
    public function layChuongSau($id_chuong) {
        $sql = "SELECT id_chuong FROM chuong WHERE id_truyen = (SELECT id_truyen FROM chuong WHERE id_chuong = ?) AND so_chuong > (SELECT so_chuong FROM chuong WHERE id_chuong = ?) ORDER BY so_chuong ASC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_chuong, $id_chuong);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['id_chuong'] ?? null;
    }
}
?>