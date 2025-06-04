<?php
require_once '../../config/connect.php';

$id = $_GET['id'];

// Lấy thông tin tài khoản
$sql = "SELECT * FROM nguoidung WHERE id_nguoidung = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_dang_nhap = $_POST['ten_dang_nhap'];
    $email = $_POST['email'];
    $vai_tro = $_POST['vai_tro'];

    // Nếu đang là admin và muốn chuyển thành người dùng, kiểm tra số lượng admin
    if ($user['vai_tro'] === 'admin' && $vai_tro !== 'admin') {
        $sql = "SELECT COUNT(*) as total FROM nguoidung WHERE vai_tro = 'admin'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        if ($row['total'] <= 1) {
            echo "Không thể thay đổi vai trò vì đây là tài khoản admin duy nhất.";
            exit();
        }
    }

    // Cập nhật thông tin tài khoản
    $sql = "UPDATE nguoidung SET ten_dang_nhap = ?, email = ?, vai_tro = ? WHERE id_nguoidung = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $ten_dang_nhap, $email, $vai_tro, $id);

    if ($stmt->execute()) {
        header("Location: list.php?success=1");
        exit();
    } else {
        echo "Lỗi: Không thể cập nhật tài khoản.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Tài Khoản</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/taiKhoan/edit.css">
</head>
<body>
    <div class="account-edit-container">
        <form class="account-edit-form" action="" method="POST" autocomplete="off">
            <h2>Thông tin tài khoản</h2>
            <div class="form-group">
                <label for="ten_dang_nhap">Tên đăng nhập</label>
                <input type="text" id="ten_dang_nhap" name="ten_dang_nhap" value="<?php echo htmlspecialchars($user['ten_dang_nhap']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="vai_tro">Vai trò</label>
                <input type="text" id="vai_tro" name="vai_tro" value="<?php echo $user['vai_tro'] === 'admin' ? 'Admin' : 'Người dùng'; ?>" readonly style="background:#f3f3f3; color:#555; cursor:not-allowed;">
                <!-- Nếu muốn gửi giá trị vai_tro về server, dùng input hidden -->
                <input type="hidden" name="vai_tro" value="<?php echo $user['vai_tro']; ?>">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <button type="button" class="btn btn-cancel" onclick="window.history.back();">Hủy</button>
            </div>
        </form>
    </div>
</body>
</html>