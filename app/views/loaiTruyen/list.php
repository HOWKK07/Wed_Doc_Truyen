<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/loaiTruyenController.php';

$controller = new LoaiTruyenController($conn);
$loaitruyens = $controller->layDanhSachLoaiTruyen();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Loại Truyện</title>
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

        .add-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .add-button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <h1>Quản Lý Loại Truyện</h1>
        <a href="add.php" class="add-button">Thêm Loại Truyện</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Loại Truyện</th>
                    <th>Ngày Tạo</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $loaitruyens->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id_loai_truyen']; ?></td>
                        <td><?php echo $row['ten_loai_truyen']; ?></td>
                        <td><?php echo $row['ngay_tao']; ?></td>
                        <td class="actions">
                            <a href="edit.php?id=<?php echo $row['id_loai_truyen']; ?>" class="edit">Sửa</a>
                            <a href="delete.php?id=<?php echo $row['id_loai_truyen']; ?>" class="delete" onclick="return confirm('Bạn có chắc chắn muốn xóa loại truyện này?');">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>