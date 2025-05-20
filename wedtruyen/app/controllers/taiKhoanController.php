<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/../models/taiKhoanModel.php';
require_once __DIR__ . '/../helpers/utils.php';

class TaiKhoanController {
    private $model;

    public function __construct($conn) {
        $this->model = new TaiKhoanModel($conn);
    }

    // API Methods
    public function getAllUsers() {
        $users = $this->model->layTatCaNguoiDung();
        echo json_encode($users);
    }

    public function getUserById($id) {
        $user = $this->model->layThongTinNguoiDung($id);
        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Người dùng không tồn tại']);
        }
    }

    public function createUser() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $ten_dang_nhap = $data['ten_dang_nhap'] ?? null;
        $mat_khau = $data['mat_khau'] ?? null;
        $email = $data['email'] ?? null;
        $vai_tro = $data['vai_tro'] ?? 'nguoidung';

        // Mã hóa mật khẩu
        $mat_khau_hash = password_hash($mat_khau, PASSWORD_DEFAULT);

        try {
            $id_nguoidung = $this->model->themNguoiDung($ten_dang_nhap, $mat_khau_hash, $email, $vai_tro);
            echo json_encode(['success' => true, 'id_nguoidung' => $id_nguoidung]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function updateUser($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $email = $data['email'] ?? null;
        $vai_tro = $data['vai_tro'] ?? null;
        $mat_khau = $data['mat_khau'] ?? null;

        try {
            if ($mat_khau) {
                $mat_khau_hash = password_hash($mat_khau, PASSWORD_DEFAULT);
                $this->model->capNhatNguoiDung($id, $email, $vai_tro, $mat_khau_hash);
            } else {
                $this->model->capNhatNguoiDung($id, $email, $vai_tro);
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function deleteUser($id) {
        try {
            $this->model->xoaNguoiDung($id);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Xử lý đăng ký
    public function dangKy() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten_dang_nhap = trim($_POST['ten_dang_nhap']);
            $email = trim($_POST['email']);
            $mat_khau = password_hash($_POST['mat_khau'], PASSWORD_BCRYPT); // Mã hóa mật khẩu

            if (empty($ten_dang_nhap) || empty($email) || empty($mat_khau)) {
                throw new Exception("Vui lòng điền đầy đủ thông tin.");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email không hợp lệ.");
            }

            $result = $this->model->dangKy($ten_dang_nhap, $email, $mat_khau);

            if (!$result) {
                throw new Exception("Tên đăng nhập hoặc email đã tồn tại.");
            }

            header("Location: login.php?success=1");
            exit();
        }
    }

    // Xử lý đăng nhập
    public function dangNhap() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten_dang_nhap = trim($_POST['ten_dang_nhap']);
            $mat_khau = $_POST['mat_khau'];

            if (empty($ten_dang_nhap) || empty($mat_khau)) {
                throw new Exception("Vui lòng điền đầy đủ thông tin.");
            }

            $user = $this->model->layTaiKhoanTheoTenDangNhap($ten_dang_nhap);

            if (!$user || !password_verify($mat_khau, $user['mat_khau'])) {
                throw new Exception("Tên đăng nhập hoặc mật khẩu không đúng.");
            }

            session_start();
            $_SESSION['user'] = $user;
            header("Location: ../../index.php");
            exit();
        }
    }

    public function quenMatKhau() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];

            // Kiểm tra email
            $user = $this->model->layTaiKhoanTheoEmail($email);

            if ($user) {
                // Tạo token và lưu vào cơ sở dữ liệu
                $token = bin2hex(random_bytes(16));
                $this->model->capNhatToken($email, $token);

                // Gửi email khôi phục mật khẩu (giả lập)
                $resetLink = "http://localhost/Wed_Doc_Truyen/app/views/taiKhoan/resetPassword.php?token=$token";
                echo "Một email khôi phục mật khẩu đã được gửi đến $email. <br>";
                echo "Nhấn vào liên kết sau để đặt lại mật khẩu: <a href='$resetLink'>$resetLink</a>";
            } else {
                echo "Email không tồn tại trong hệ thống.";
            }
        }
    }

    public function datLaiMatKhau() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'];
            $mat_khau_moi = password_hash($_POST['mat_khau_moi'], PASSWORD_BCRYPT);

            // Gọi model để kiểm tra token và cập nhật mật khẩu
            $result = $this->model->capNhatMatKhauBangToken($token, $mat_khau_moi);

            if ($result) {
                echo "Mật khẩu đã được đặt lại thành công. <a href='login.php'>Đăng nhập</a>";
            } else {
                echo "Token không hợp lệ hoặc đã hết hạn.";
            }
        }
    }
}
?>