<?php
require_once '../../config/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Kiểm tra email có tồn tại trong cơ sở dữ liệu không
    $sql = "SELECT * FROM tai_khoan WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Nếu email tồn tại, gửi email khôi phục mật khẩu
        $token = bin2hex(random_bytes(16)); // Tạo token ngẫu nhiên
        $resetLink = "http://localhost/Wed_Doc_Truyen/app/views/taiKhoan/resetPassword.php?token=$token";

        // Lưu token vào cơ sở dữ liệu (giả sử bạn có cột `reset_token` trong bảng `tai_khoan`)
        $sqlUpdate = "UPDATE tai_khoan SET reset_token = ? WHERE email = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ss", $token, $email);
        $stmtUpdate->execute();

        // Gửi email (giả lập)
        echo "Một email khôi phục mật khẩu đã được gửi đến $email. <br>";
        echo "Nhấn vào liên kết sau để đặt lại mật khẩu: <a href='$resetLink'>$resetLink</a>";
    } else {
        echo "Email không tồn tại trong hệ thống.";
    }
}
?>