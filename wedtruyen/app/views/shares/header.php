<link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/header.css">
<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="menu">
    <a href="/Wed_Doc_Truyen/wedtruyen/index.php" class="logo">Web Đọc Truyện</a>
    <div class="nav-links">
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/theLoai/list.php">Thể loại</a>
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/loaiTruyen/list.php">Loại Truyện</a> <!-- Nút Loại Truyện -->
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/add.php">Thêm Truyện</a>
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/thuvien/list.php">Thư viện của tôi</a>
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['vai_tro'] === 'admin'): ?>
            <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/list.php">Quản Lý Truyện</a>
            <a href="/Wed_Doc_Truyen/wedtruyen/app/views/taiKhoan/list.php">Quản Lý Tài Khoản</a>
        <?php endif; ?>
    </div>
    <div class="user-info">
        <?php if (isset($_SESSION['user'])): ?>
            <span>Xin chào, <?php echo htmlspecialchars($_SESSION['user']['ten_dang_nhap']); ?>!</span>
            <a href="/Wed_Doc_Truyen/wedtruyen/app/views/taiKhoan/logout.php">Đăng xuất</a>
        <?php else: ?>
            <a href="/Wed_Doc_Truyen/wedtruyen/app/views/taiKhoan/login.php">Đăng nhập</a>
        <?php endif; ?>
    </div>
</div>