<?php
session_start();
require_once '../../config/connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] !== 'admin') {
    die("Bạn không có quyền truy cập trang này.");
}

// Lấy danh sách truyện
$sql = "SELECT truyen.*, loai_truyen.ten_loai_truyen 
        FROM truyen 
        LEFT JOIN loai_truyen ON truyen.id_loai_truyen = loai_truyen.id_loai_truyen 
        ORDER BY ngay_tao DESC";
$result = $conn->query($sql);

$truyenList = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $truyenList[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Truyện</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .actions a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
        }

        .actions .edit-btn {
            background-color: #ffc107;
            color: black;
        }

        .actions .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .actions a:hover {
            opacity: 0.8;
        }

        .add-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .add-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <div class="container">
        <h1>Quản Lý Truyện</h1>
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <p style="color: green; text-align: center;">Cập nhật truyện thành công!</p>
        <?php endif; ?>
        <a href="add.php" class="add-btn">Thêm Truyện</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Truyện</th>
                    <th>Ngày Tạo</th>
                    <th>Trạng Thái</th>
                    <th>Loại Truyện</th>
                    <th>Năm Phát Hành</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($truyenList) && is_array($truyenList)): ?>
                    <?php foreach ($truyenList as $truyen): ?>
                        <tr>
                            <td><?php echo $truyen['id_truyen']; ?></td>
                            <td><?php echo htmlspecialchars($truyen['ten_truyen']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($truyen['ngay_tao'])); ?></td>
                            <td><?php echo htmlspecialchars($truyen['trang_thai']); ?></td>
                            <td><?php echo htmlspecialchars($truyen['ten_loai_truyen']); ?></td>
                            <td><?php echo htmlspecialchars($truyen['nam_phat_hanh']); ?></td>
                            <td class="actions">
                                <a href="edit.php?id=<?php echo $truyen['id_truyen']; ?>" class="edit-btn">Sửa</a>
                                <a href="delete.php?id_truyen=<?php echo $truyen['id_truyen']; ?>" class="delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa truyện này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Không có truyện nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>