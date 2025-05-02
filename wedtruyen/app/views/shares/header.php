<style>
    .menu {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .menu .logo {
        font-size: 24px;
        font-weight: bold;
        color: white;
        text-decoration: none;
    }

    .menu .nav-links {
        display: flex;
        gap: 15px;
    }

    .menu .nav-links a {
        text-decoration: none;
        color: white;
        padding: 8px 15px;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    .menu .nav-links a:hover {
        background-color: #0056b3;
    }

    .menu .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .menu .user-info span {
        font-size: 14px;
        color: white;
    }

    .menu .user-info a {
        text-decoration: none;
        color: white;
        padding: 8px 15px;
        border-radius: 5px;
        background-color: #28a745;
        transition: background-color 0.3s;
    }

    .menu .user-info a:hover {
        background-color: #218838;
    }
</style>

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