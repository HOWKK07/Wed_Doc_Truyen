<?php
require_once '../../config/connect.php';
require_once '../../models/taiKhoanModel.php';

class TaiKhoanController {
    private $model;

    public function __construct($conn) {
        $this->model = new TaiKhoanModel($conn);
    }

    // Xử lý đăng ký
    public function dangKy() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten_dang_nhap = $_POST['ten_dang_nhap'];
            $email = $_POST['email'];
            $mat_khau = password_hash($_POST['mat_khau'], PASSWORD_BCRYPT); // Mã hóa mật khẩu

            $result = $this->model->dangKy($ten_dang_nhap, $email, $mat_khau);

            if ($result) {
                header("Location: login.php?success=1");
                exit();
            } else {
                echo "Lỗi: Không thể đăng ký tài khoản.";
            }
        }
    }

    // Xử lý đăng nhập
    public function dangNhap() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ten_dang_nhap = $_POST['ten_dang_nhap'];
            $mat_khau = $_POST['mat_khau'];

            $user = $this->model->layTaiKhoanTheoTenDangNhap($ten_dang_nhap);

            if ($user && password_verify($mat_khau, $user['mat_khau'])) {
                session_start();
                $_SESSION['user'] = $user;
                header("Location: ../../index.php");
                exit();
            } else {
                echo "Tên đăng nhập hoặc mật khẩu không đúng.";
            }
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