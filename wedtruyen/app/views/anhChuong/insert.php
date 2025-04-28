<?php
session_start();
require_once '../../config/connect.php';
require_once '../../models/anhChuongModel.php';

if (!isset($_GET['id_chuong']) || !isset($_GET['so_trang'])) {
    die("Lỗi: Không tìm thấy ID chương hoặc số trang.");
}

$id_chuong = $_GET['id_chuong'];
$so_trang_hien_tai = $_GET['so_trang'];
$model = new AnhChuongModel($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra file ảnh
    if (!isset($_FILES['anh']) || $_FILES['anh']['error'] !== UPLOAD_ERR_OK) {
        die("Lỗi tải lên ảnh.");
    }

    // Tải lên ảnh mới
    $target_dir = __DIR__ . "/../../../uploads/anhchuong/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = pathinfo($_FILES["anh"]["name"], PATHINFO_EXTENSION);
    $file_name = uniqid() . '.' . $file_extension;
    $file_path = $target_dir . $file_name;

    if (!move_uploaded_file($_FILES["anh"]["tmp_name"], $file_path)) {
        die("Không thể tải lên ảnh.");
    }

    $duong_dan_anh = "uploads/anhchuong/" . $file_name;

    // Tăng số trang của các ảnh sau ảnh hiện tại
    $so_trang_moi = $so_trang_hien_tai + 1;
    $sql = "UPDATE anh_chuong SET so_trang = so_trang + 1 WHERE id_chuong = ? AND so_trang > ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_chuong, $so_trang_hien_tai);
    $stmt->execute();

    // Chèn ảnh mới vào vị trí sau ảnh hiện tại
    $sql = "INSERT INTO anh_chuong (id_chuong, duong_dan_anh, so_trang) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $id_chuong, $duong_dan_anh, $so_trang_moi);
    if ($stmt->execute()) {
        header("Location: list.php?id_chuong=$id_chuong");
        exit();
    } else {
        echo "Lỗi: Không thể chèn ảnh.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chèn Ảnh</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
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
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
        <form action="" method="POST" enctype="multipart/form-data">
            <h1>Chèn Ảnh</h1>
            <label for="anh">Chọn ảnh:</label>
            <input type="file" id="anh" name="anh" accept="image/*" required>
            <button type="submit">Chèn Ảnh</button>
        </form>
        <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($anh['duong_dan_anh']); ?>?t=<?php echo time(); ?>" alt="Ảnh mới">
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>