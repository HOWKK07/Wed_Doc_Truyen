<?php
session_start();
require_once '../../config/connect.php';
require_once '../../models/anhChuongModel.php';

if (!isset($_GET['id_anh']) || empty($_GET['id_anh'])) {
    die("Lỗi: Không tìm thấy ID ảnh.");
}

$id_anh = $_GET['id_anh'];
$model = new AnhChuongModel($conn);

// Lấy thông tin ảnh
$sql = "SELECT * FROM anh_chuong WHERE id_anh = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_anh);
$stmt->execute();
$anh = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $so_trang = $_POST['so_trang'];

    // Nếu có ảnh mới được tải lên
    if (isset($_FILES['anh_moi']) && $_FILES['anh_moi']['error'] === UPLOAD_ERR_OK) {
        // Xóa ảnh cũ
        $file_path_cu = __DIR__ . "/../../../" . $anh['duong_dan_anh'];
        if (file_exists($file_path_cu)) {
            unlink($file_path_cu); // Xóa file ảnh cũ
        }

        // Tải lên ảnh mới
        $target_dir = __DIR__ . "/../../../uploads/anhchuong/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["anh_moi"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $file_path_moi = $target_dir . $file_name;

        if (!move_uploaded_file($_FILES["anh_moi"]["tmp_name"], $file_path_moi)) {
            die("Không thể tải lên ảnh mới.");
        }

        // Cập nhật đường dẫn ảnh mới vào cơ sở dữ liệu
        $duong_dan_anh_moi = "uploads/anhchuong/" . $file_name;
        $sql = "UPDATE anh_chuong SET duong_dan_anh = ?, so_trang = ? WHERE id_anh = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $duong_dan_anh_moi, $so_trang, $id_anh);
    } else {
        // Nếu không có ảnh mới, chỉ cập nhật số trang
        $sql = "UPDATE anh_chuong SET so_trang = ? WHERE id_anh = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $so_trang, $id_anh);
    }

    if ($stmt->execute()) {
        header("Location: list.php?id_chuong=" . $anh['id_chuong']);
        exit();
    } else {
        echo "Lỗi: Không thể cập nhật thông tin ảnh.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Ảnh</title>
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

        .current-image {
            text-align: center;
            margin-bottom: 20px;
        }

        .current-image img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <form action="" method="POST" enctype="multipart/form-data">
            <h1>Sửa Ảnh</h1>

            <div class="current-image">
                <p>Ảnh hiện tại:</p>
                <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($anh['duong_dan_anh']); ?>?t=<?php echo time(); ?>" alt="Ảnh hiện tại">
            </div>

            <label for="so_trang">Số trang:</label>
            <input type="number" id="so_trang" name="so_trang" value="<?php echo htmlspecialchars($anh['so_trang']); ?>" required>

            <label for="anh_moi">Thay ảnh mới (nếu cần):</label>
            <input type="file" id="anh_moi" name="anh_moi" accept="image/*">

            <button type="submit">Cập Nhật</button>
        </form>
    </div>

    <!-- Footer -->
    <?php include '../shares/footer.php'; ?>
</body>
</html>