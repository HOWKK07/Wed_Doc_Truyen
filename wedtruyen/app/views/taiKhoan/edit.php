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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .content {
            padding: 20px;
        }

        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        form h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        form input, form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        form button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #0056b3;
        }
    </style>
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