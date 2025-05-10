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
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/anhChuong/edit.css">
</head>
<body>
    <!-- Header -->
    <?php include '../shares/header.php'; ?>

    <!-- Nội dung chính -->
    <div class="content">
        <form action="" method="POST" enctype="multipart/form-data">
            <h1>Sửa Ảnh Chương</h1>
            <div class="current-image">
                <p>Ảnh hiện tại:</p>
                <img src="/Wed_Doc_Truyen/<?php echo htmlspecialchars($anh['duong_dan_anh']); ?>" alt="Ảnh hiện tại">
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