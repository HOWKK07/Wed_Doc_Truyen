<?php
require_once '../../config/connect.php';
require_once '../../models/chapterModel.php';

$id_truyen = $_GET['id_truyen'];

// Lấy thông tin truyện
$sql = "SELECT * FROM truyen WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_truyen);
$stmt->execute();
$truyen = $stmt->get_result()->fetch_assoc();

// Lấy danh sách chapter
$sql_chapter = "SELECT * FROM chapter WHERE id_truyen = ? ORDER BY ngay_tao ASC";
$stmt_chapter = $conn->prepare($sql_chapter);
$stmt_chapter->bind_param("i", $id_truyen);
$stmt_chapter->execute();
$chapters = $stmt_chapter->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $truyen['ten_truyen']; ?></title>
</head>
<body>
    <h1><?php echo $truyen['ten_truyen']; ?></h1>
    <p><strong>Tác giả:</strong> <?php echo $truyen['tac_gia']; ?></p>
    <p><strong>Mô tả:</strong> <?php echo $truyen['mo_ta']; ?></p>

    <h2>Danh sách chapter</h2>
    <ul>
        <?php while ($chapter = $chapters->fetch_assoc()): ?>
            <li>
                <a href="docChapter.php?id_chapter=<?php echo $chapter['id']; ?>">
                    <?php echo $chapter['ten_chapter']; ?>
                </a>
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>