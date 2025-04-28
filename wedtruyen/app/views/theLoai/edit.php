<?php
require_once '../../config/connect.php';

$id = $_GET['id'];

// Lấy thông tin thể loại
$sql = "SELECT * FROM theloai WHERE id_theloai = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$theloai = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_theloai = $_POST['ten_theloai'];

    // Cập nhật thông tin thể loại
    $sql = "UPDATE theloai SET ten_theloai = ? WHERE id_theloai = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $ten_theloai, $id);

    if ($stmt->execute()) {
        header("Location: list.php?success=1");
        exit();
    } else {
        echo "Lỗi: Không thể cập nhật thể loại.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Thể Loại</title>
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

        form input {
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
            <h1>Sửa Thể Loại</h1>
            <label for="ten_theloai">Tên Thể Loại:</label>
            <input type="text" id="ten_theloai" name="ten_theloai" value="<?php echo $theloai['ten_theloai']; ?>" required>
            <button type="submit">Cập Nhật</button>
        </form>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>