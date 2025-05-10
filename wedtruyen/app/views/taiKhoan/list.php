<?php
session_start();
require_once '../../config/connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$sql = "SELECT * FROM nguoidung";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tài Khoản</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/taiKhoan/list.css">
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <h1>Quản Lý Tài Khoản</h1>
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green; text-align: center;">Thao tác thành công!</p>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Đăng Nhập</th>
                    <th>Email</th>
                    <th>Vai Trò</th>
                    <th>Ngày Tạo</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id_nguoidung']; ?></td>
                            <td><?php echo htmlspecialchars($row['ten_dang_nhap']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['vai_tro']); ?></td>
                            <td><?php echo htmlspecialchars($row['ngay_tao']); ?></td>
                            <td class="actions">
                                <a href="edit.php?id=<?php echo $row['id_nguoidung']; ?>" class="edit">Sửa</a>
                                <a href="delete.php?id=<?php echo $row['id_nguoidung']; ?>" class="delete" onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Không có tài khoản nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>