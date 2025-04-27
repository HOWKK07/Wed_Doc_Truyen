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
}
?>