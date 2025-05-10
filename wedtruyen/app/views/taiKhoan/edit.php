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
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <form action="" method="POST">
            <h1>Sửa Tài Khoản</h1>
            <label for="ten_dang_nhap">Tên Đăng Nhập:</label>
            <input type="text" id="ten_dang_nhap" name="ten_dang_nhap" value="<?php echo $user['ten_dang_nhap']; ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>

            <label for="vai_tro">Vai Trò:</label>
            <select id="vai_tro" name="vai_tro" required>
                <option value="admin" <?php echo $user['vai_tro'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="nguoidung" <?php echo $user['vai_tro'] === 'nguoidung' ? 'selected' : ''; ?>>Người Dùng</option>
            </select>

            <button type="submit">Cập Nhật</button>
        </form>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>