<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/binhLuanController.php';

if (!isset($_SESSION['user'])) {
    die("Bạn cần đăng nhập để bình luận.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_truyen = $_POST['id_truyen'];
    $id_nguoidung = $_SESSION['user']['id_nguoidung'];
    $noi_dung = $_POST['noi_dung'];

    $binhLuanController = new BinhLuanController($conn);

    try {
        $binhLuanController->themBinhLuanTruyen($id_truyen, $id_nguoidung, $noi_dung);
        header("Location: ../truyen/chiTietTruyen.php?id_truyen=$id_truyen");
        exit();
    } catch (Exception $e) {
        die("Lỗi: " . $e->getMessage());
    }
}
?>

<?php if (isset($_SESSION['user'])): ?>
    <form action="../binhLuan/addComment.php" method="POST" class="comment-form">
        <input type="hidden" name="id_truyen" value="<?php echo $id_truyen; ?>">
        <textarea name="noi_dung" rows="3" placeholder="Viết bình luận..." required></textarea>
        <button type="submit">Gửi bình luận</button>
    </form>
<?php else: ?>
    <p><a href="../taiKhoan/login.php">Đăng nhập</a> để bình luận.</p>
<?php endif; ?>