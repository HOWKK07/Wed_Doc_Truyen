<link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/header.css">
<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="menu">
    <a href="/Wed_Doc_Truyen/wedtruyen/index.php" class="logo">Web Đọc Truyện</a>
    
    <div class="nav-links">
        <!-- Ô tìm kiếm truyện -->
        <div style="position:relative;display:inline-block;">
            <input type="text" id="search-truyen" placeholder="Tìm truyện..." autocomplete="off" style="padding:6px 12px;border-radius:4px;border:1px solid #ccc;width:180px;">
            <div id="search-results" style="display:none;position:absolute;top:36px;left:0;width:100%;background:#fff;border:1px solid #ddd;max-height:300px;overflow-y:auto;z-index:10000;border-radius:0 0 6px 6px;box-shadow:0 4px 16px rgba(0,0,0,0.10);"></div>
        </div>
 
        
        <?php if (isset($_SESSION['user'])): ?>
            <a href="/Wed_Doc_Truyen/wedtruyen/app/views/thuvien/list.php">Thư viện của tôi</a>
            <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/lichSuDoc.php">Lịch sử đọc</a>

            
            <?php if (isset($_SESSION['user']['vai_tro']) && $_SESSION['user']['vai_tro'] === 'admin'): ?>
                <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/list.php">Quản Lý Truyện</a>
                <a href="/Wed_Doc_Truyen/wedtruyen/app/views/taiKhoan/list.php">Quản Lý Tài Khoản</a>
                       <a href="/Wed_Doc_Truyen/wedtruyen/app/views/theLoai/list.php">Thể loại</a>
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/loaiTruyen/list.php">Loại Truyện</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="user-info">
        <?php if (isset($_SESSION['user'])): ?>
        <!-- Nút chuông thông báo -->
        <div class="notification-wrapper" style="display:inline-block;position:relative;">
            <button id="notification-btn" style="background:none;border:none;cursor:pointer;position:relative;">
                <i class="fas fa-bell" style="font-size:22px;color:#ffb100;"></i>
                <span id="notification-count" style="position:absolute;top:-6px;right:-6px;background:#e74c3c;color:#fff;font-size:12px;border-radius:50%;padding:2px 6px;display:none;">0</span>
            </button>
            <div id="notification-dropdown" style="display:none;position:absolute;right:0;top:32px;width:320px;max-height:400px;overflow-y:auto;background:#fff;border:1px solid #ddd;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.12);z-index:999;">
                <div id="notification-list" style="padding:10px 0;"></div>
            </div>
        </div>
            <span>Xin chào, <?php echo htmlspecialchars($_SESSION['user']['ten_dang_nhap']); ?>!</span>
            <a href="/Wed_Doc_Truyen/wedtruyen/app/views/taiKhoan/logout.php" class="logout-btn">Đăng xuất</a>
        <?php else: ?>
            <a href="/Wed_Doc_Truyen/wedtruyen/app/views/taiKhoan/login.php" class="login-btn">Đăng nhập</a>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('notification-btn');
    const dropdown = document.getElementById('notification-dropdown');
    const list = document.getElementById('notification-list');
    const count = document.getElementById('notification-count');

    function fetchNotifications() {
        if (!list) {
            alert('Không tìm thấy phần tử notification-list!');
            return;
        }
        fetch('/Wed_Doc_Truyen/wedtruyen/app/api/notifications.php')
            .then(res => res.json())
            .then(data => {
                console.log('Thông báo:', data); // Thêm dòng này để kiểm tra
                list.innerHTML = '';
                let unread = 0;
                if (data.length === 0) {
                    list.innerHTML = '<div style="padding:12px;text-align:center;color:#888;">Không có thông báo mới.</div>';
                } else {
                    data.forEach(n => {
                        if (!n.da_doc) unread++;
                        // Chỉ render nếu id_chuong hợp lệ (khác null, khác "null", là số)
                        if (n.id_chuong && n.id_chuong !== "null" && !isNaN(Number(n.id_chuong))) {
                            list.innerHTML += `<div style="padding:10px 16px;border-bottom:1px solid #f0f0f0;${n.da_doc ? 'color:#888;' : ''}">
<a href="/Wed_Doc_Truyen/wedtruyen/app/views/chapter/docChapter.php?id_chuong=${n.id_chuong}" style="color:inherit;text-decoration:none;">
    ${n.noi_dung}
    <div style="font-size:12px;color:#aaa;margin-top:2px;">${n.ngay_tao}</div>
</a>
</div>`;
                        }
                    });
                }
                count.textContent = unread;
                count.style.display = unread > 0 ? 'block' : 'none';
            });
    }

    btn.addEventListener('click', function(e) {
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        if (dropdown.style.display === 'block') {
            fetchNotifications();
        }
        e.stopPropagation();
    });

    // Tìm kiếm truyện
    const searchInput = document.getElementById('search-truyen');
    const searchResults = document.getElementById('search-results');
    let searchTimeout = null;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(searchTimeout);
        if (query.length === 0) {
            searchResults.style.display = 'none';
            searchResults.innerHTML = '';
            return;
        }
        searchTimeout = setTimeout(() => {
            fetch(`/Wed_Doc_Truyen/wedtruyen/app/api/searchTruyen.php?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (Array.isArray(data) && data.length > 0) {
                        searchResults.innerHTML = data.map(truyen => {
    // Xử lý đường dẫn ảnh bìa
    let cover = truyen.anh_bia && truyen.anh_bia !== 'null' && truyen.anh_bia !== '' 
        ? `/Wed_Doc_Truyen/wedtruyen/${truyen.anh_bia}` 
        : `/Wed_Doc_Truyen/wedtruyen/assets/img/default_cover.jpg`;
    return `
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=${truyen.id_truyen}" class="search-item" style="display:flex;align-items:center;padding:10px 16px;text-decoration:none;color:#222;border-bottom:1px solid #f0f0f0;">
            <img src="${cover}" alt="cover" style="width:38px;height:52px;object-fit:cover;border-radius:4px;margin-right:12px;">
            <div>
                <div style="font-weight:bold;">${truyen.ten_truyen}</div>
                <div style="font-size:13px;color:#888;">${truyen.tac_gia ? 'Tác giả: ' + truyen.tac_gia : ''}</div>
                <div style="font-size:12px;color:#888;">${truyen.trang_thai ? 'Trạng thái: ' + truyen.trang_thai : ''}</div>
            </div>
        </a>
    `;
}).join('');
                        searchResults.style.display = 'block';
                    } else {
                        searchResults.innerHTML = '<div style="padding:10px;color:#888;">Không tìm thấy truyện.</div>';
                        searchResults.style.display = 'block';
                    }
                });
        }, 250);
    });

    // Ẩn kết quả khi click ngoài
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    searchInput.addEventListener('focus', function() {
        if (searchResults.innerHTML.trim() !== '') searchResults.style.display = 'block';
    });
});
</script>
<style>
/* Đảm bảo dropdown nổi trên cùng và không bị che */
.notification-wrapper {
    position: relative;
    z-index: 1000;
}
#notification-dropdown {
    z-index: 9999 !important;
}
#search-results {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    border-radius: 8px;
    border: 1px solid #ddd;
    background: #fff;
    z-index: 9999;
    max-height: 400px;
    overflow-y: auto;
}
.search-item:hover {
    background: #f6f6f6;
}
</style>