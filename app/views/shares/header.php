<style>
    .menu {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        display: flex !important; /* Đảm bảo menu hiển thị theo chiều ngang */
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
        display: flex; /* Đảm bảo các liên kết nằm ngang */
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
        display: flex; /* Đảm bảo thông tin người dùng nằm ngang */
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

<div class="menu">
    <a href="../../index.php" class="logo">Web Đọc Truyện</a>
    <div class="nav-links">
        <a href="../../index.php" id="home-link">Trang chủ</a>
        <a href="../theLoai/list.php">Thể loại</a>
        <a href="../loaiTruyen/list.php">Loại truyện</a> <!-- Thêm liên kết này -->
        <a href="../taiKhoan/list.php">Quản lý tài khoản</a>
        <a href="/Wed_Doc_Truyen/app/views/truyen/add.php">Thêm truyện</a>
    </div>
    <div class="user-info">
        <?php if (isset($_SESSION['user'])): ?>
            <span>Xin chào, <?php echo $_SESSION['user']['ten_dang_nhap']; ?>!</span>
            <a href="../taiKhoan/logout.php">Đăng xuất</a>
        <?php else: ?>
            <a href="../taiKhoan/login.php">Đăng nhập</a>
            <a href="../taiKhoan/register.php">Đăng ký</a>
        <?php endif; ?>
    </div>
</div>

<script>
    // Kiểm tra URL hiện tại
    const homeLink = document.getElementById('home-link');
    const currentUrl = window.location.href;

    // Nếu URL hiện tại là trang chủ, thêm sự kiện để reset trang
    if (currentUrl.includes('index.php')) {
        homeLink.addEventListener('click', function (event) {
            event.preventDefault(); // Ngăn điều hướng mặc định
            window.location.reload(); // Tải lại trang
        });
    }
</script>