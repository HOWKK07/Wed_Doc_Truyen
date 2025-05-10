<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/theLoaiController.php';

$controller = new TheLoaiController($conn);
$theloais = $controller->layDanhSachTheLoai();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Thể Loại</title>
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/theLoai/list.css">
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <h1>Quản Lý Thể Loại</h1>
        <a href="add.php" class="add-button">Thêm Thể Loại</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Thể Loại</th>
                    <th>Ngày Tạo</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($theloais->num_rows > 0): ?>
                    <?php while ($row = $theloais->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id_theloai']; ?></td>
                            <td><?php echo htmlspecialchars($row['ten_theloai']); ?></td>
                            <td><?php echo $row['ngay_tao']; ?></td>
                            <td class="actions">
                                <a href="edit.php?id=<?php echo $row['id_theloai']; ?>" class="edit">Sửa</a>
                                <a href="delete.php?id=<?php echo $row['id_theloai']; ?>" class="delete" onclick="return confirm('Bạn có chắc chắn muốn xóa thể loại này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Không có thể loại nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>