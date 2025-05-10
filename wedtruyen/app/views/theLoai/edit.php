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
    <link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/theLoai/edit.css">
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