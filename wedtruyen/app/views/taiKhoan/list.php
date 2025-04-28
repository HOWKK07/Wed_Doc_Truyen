<?php
session_start();
require_once '../../config/connect.php';

// Kiểm tra xem người dùng đã đăng nhập và có vai trò admin chưa
if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Lấy danh sách tài khoản từ cơ sở dữ liệu
$sql = "SELECT * FROM nguoidung";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Tài Khoản</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .content {
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .actions a {
            margin-right: 10px;
            text-decoration: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .actions .edit {
            background-color: #007bff;
        }

        .actions .delete {
            background-color: #dc3545;
        }

        .actions a:hover {
            opacity: 0.8;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <h1>Quản Lý Tài Khoản</h1>
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
                            <td><?php echo $row['ten_dang_nhap']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['vai_tro']; ?></td>
                            <td><?php echo $row['ngay_tao']; ?></td>
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