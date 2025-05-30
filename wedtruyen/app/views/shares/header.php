<link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/header-optimized.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Lấy danh sách thể loại
$sql_theloai = "SELECT * FROM theloai ORDER BY ten_theloai ASC";
$result_theloai = $conn->query($sql_theloai);

// Lấy danh sách loại truyện
$sql_loaitruyen = "SELECT * FROM loai_truyen ORDER BY ten_loai_truyen ASC";
$result_loaitruyen = $conn->query($sql_loaitruyen);
?>

<div class="menu">
    <a href="/Wed_Doc_Truyen/wedtruyen/index.php" class="logo">
        <i class="fas fa-book-reader"></i>
        Web Đọc
    </a>
    
    <div class="nav-links">
        <!-- Search Box -->
        <div class="search-container">
            <input type="text" id="search-truyen" placeholder="Tìm kiếm truyện..." autocomplete="off">
            <i class="fas fa-search search-icon"></i>
            <div id="search-results"></div>
        </div>
        
        <a href="/Wed_Doc_Truyen/wedtruyen/index.php">
            <i class="fas fa-home"></i>
            Trang chủ
        </a>
        
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/thuvien/list.php">
            <i class="fas fa-bookmark"></i>
            Thư viện
        </a>
        
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/lichSuDoc.php">
            <i class="fas fa-history"></i>
            Lịch sử
        </a>
        
        <!-- Dropdown Thể loại -->
        <div class="genre-dropdown-wrapper">
            <button class="genre-dropdown-btn">
                <i class="fas fa-tags"></i>
                Thể loại 
                <span class="dropdown-arrow">▼</span>
            </button>
            <div class="genre-dropdown-menu">
                <div class="dropdown-header">
                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/theLoai/list.php">
                        <i class="fas fa-list"></i> Xem tất cả thể loại
                    </a>
                </div>
                <div class="genre-grid">
                    <?php 
                    mysqli_data_seek($result_theloai, 0);
                    if ($result_theloai && $result_theloai->num_rows > 0): 
                    ?>
                        <?php while($theloai = $result_theloai->fetch_assoc()): ?>
                            <a href="/Wed_Doc_Truyen/wedtruyen/index.php?theloai=<?php echo $theloai['id_theloai']; ?>">
                                <?php echo htmlspecialchars($theloai['ten_theloai']); ?>
                            </a>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Dropdown Loại truyện -->
        <div class="type-dropdown-wrapper">
            <button class="type-dropdown-btn">
                <i class="fas fa-layer-group"></i>
                Loại truyện 
                <span class="dropdown-arrow">▼</span>
            </button>
            <div class="type-dropdown-menu">
                <div class="dropdown-header">
                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/loaiTruyen/list.php">
                        <i class="fas fa-list"></i> Xem tất cả loại truyện
                    </a>
                </div>
                <div class="type-grid">
                    <?php 
                    mysqli_data_seek($result_loaitruyen, 0);
                    if ($result_loaitruyen && $result_loaitruyen->num_rows > 0): 
                    ?>
                        <?php while($loaitruyen = $result_loaitruyen->fetch_assoc()): ?>
                            <a href="/Wed_Doc_Truyen/wedtruyen/index.php?loaitruyen=<?php echo $loaitruyen['id_loai_truyen']; ?>">
                                <?php echo htmlspecialchars($loaitruyen['ten_loai_truyen']); ?>
                            </a>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if (isset($_SESSION['user']['vai_tro']) && $_SESSION['user']['vai_tro'] === 'admin'): ?>
            <a href="/Wed_Doc_Truyen/wedtruyen/app/views/admin/admin.php">
                <i class="fas fa-cog"></i>
                Admin
            </a>
        <?php endif; ?>
    </div>
    
    <div class="user-info">
        <!-- Notification Bell -->
        <div class="notification-wrapper">
            <button id="notification-btn">
                <i class="fas fa-bell"></i>
                <span id="notification-count" style="display:none;">0</span>
            </button>
            <div id="notification-dropdown">
                <div id="notification-list"></div>
            </div>
        </div>
        
        <?php if (isset($_SESSION['user'])): ?>
            <!-- Account Dropdown -->
            <div class="account-dropdown-wrapper">
                <button id="account-btn">
                    <i class="fas fa-user-circle"></i>
                    <span class="dropdown-arrow">▼</span>
                </button>
                <div id="account-dropdown">
                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/taiKhoan/edit.php?id=<?php echo $_SESSION['user']['id_nguoidung']; ?>">
                        <i class="fas fa-user"></i> Thông tin tài khoản
                    </a>
                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/lichSuDoc.php">
                        <i class="fas fa-history"></i> Lịch sử đọc
                    </a>
                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/taiKhoan/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </a>
                </div>
            </div>
            <span>Xin chào, <?php echo htmlspecialchars($_SESSION['user']['ten_dang_nhap']); ?>!</span>
        <?php else: ?>
            <a href="/Wed_Doc_Truyen/wedtruyen/app/views/taiKhoan/login.php" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                Đăng nhập
            </a>
        <?php endif; ?>
    </div>
</div>

<script src="/Wed_Doc_Truyen/wedtruyen/assets/js/header-optimized.js"></script>