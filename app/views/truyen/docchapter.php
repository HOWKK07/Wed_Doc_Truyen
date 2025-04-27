<?php
require_once '../../config/connect.php';

$id_chapter = $_GET['id_chapter'];

// Lấy thông tin chapter
$sql = "SELECT * FROM chapter WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_chapter);
$stmt->execute();
$chapter = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $chapter['ten_chapter']; ?></title>
</head>
<body>
    <h1><?php echo $chapter['ten_chapter']; ?></h1>
    <p><?php echo nl2br($chapter['noi_dung']); ?></p>
    <a href="chiTietTruyen.php?id_truyen=<?php echo $chapter['id_truyen']; ?>">Quay lại danh sách chapter</a>
</body>
</html>