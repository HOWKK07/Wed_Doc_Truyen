<link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/header.css">
<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="menu">
    <a href="/Wed_Doc_Truyen/wedtruyen/index.php" class="logo">Web Đọc Truyện</a>
    <!-- Thanh tìm kiếm truyện -->
    <div style="position:relative; display:inline-block; width:400px; margin-left:30px; vertical-align:middle;">
        <input type="text" id="search-box" placeholder="Tìm truyện..." style="width:100%;padding:8px;">
        <div id="search-results" class="search-dropdown"></div>
    </div>
    <div class="nav-links">
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/theLoai/list.php">Thể loại</a>
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/loaiTruyen/list.php">Loại Truyện</a> <!-- Nút Loại Truyện -->
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/add.php">Thêm Truyện</a>
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/thuvien/list.php">Thư viện của tôi</a>
        <a href="#" id="history-menu">Lịch sử đọc</a>
        <div id="history-dropdown" class="search-dropdown" style="right:0;left:auto;"></div>
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

<style>
.menu {
    position: relative;
    z-index: 10;
}
.search-dropdown {
    position: absolute;
    background: #fff;
    width: 100%;
    border: 1px solid #ccc;
    z-index: 1001;
    max-height: 400px;
    overflow-y: auto;
    display: none;
    left: 0;
    top: 100%; /* Hiển thị ngay dưới ô input */
    font-family: 'Segoe UI', Arial, sans-serif;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
}
.search-item {
    display: flex;
    align-items: center;
    padding: 10px 8px;
    cursor: pointer;
    font-size: 15px;
    border-bottom: 1px solid #f2f2f2;
    background: #fff;
    transition: background 0.2s;
}
.search-item:last-child {
    border-bottom: none;
}
.search-item img {
    width: 48px; height: 64px; object-fit: cover; margin-right: 12px; border-radius: 6px; background: #f5f5f5;
    border: 1px solid #eee;
}
.search-item .info {
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.search-item .info b {
    font-size: 16px;
    color: #222;
}
.search-item .info span {
    color: #888;
    font-size: 13px;
    margin-top: 2px;
}
.search-item:hover {
    background: #f0f6ff;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchBox = document.getElementById('search-box');
    const resultsDiv = document.getElementById('search-results');
    const defaultCover = '/Wed_Doc_Truyen/wedtruyen/assets/img/default-cover.png'; // Đặt ảnh mặc định ở đây

    searchBox.addEventListener('input', function() {
        const q = this.value.trim();
        if (q.length === 0) {
            resultsDiv.style.display = 'none';
            resultsDiv.innerHTML = '';
            return;
        }
        fetch('/Wed_Doc_Truyen/wedtruyen/api.php?path=search&q=' + encodeURIComponent(q))
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.data.length > 0) {
                    resultsDiv.innerHTML = data.data.map(item => {
                        let cover = item.anh_bia && item.anh_bia !== 'null' && item.anh_bia !== '' 
                            ? '/Wed_Doc_Truyen/' + item.anh_bia.replace(/^\/+/, '') 
                            : defaultCover;
                        console.log('cover:', cover);
                        return `
                            <div class="search-item" data-id="${item.id_truyen}">
                                <img src="${cover}" alt="${item.ten_truyen}" onerror="this.src='${defaultCover}'">
                                <div class="info">
                                    <b>${item.ten_truyen}</b>
                                    <span>${item.tac_gia ? item.tac_gia : ''}</span>
                                    <span>Chương ${item.so_chuong}</span>
                                </div>
                            </div>
                        `;
                    }).join('');
                    resultsDiv.style.display = 'block';
                } else {
                    resultsDiv.innerHTML = '<div style="padding:8px;">Không tìm thấy truyện</div>';
                    resultsDiv.style.display = 'block';
                }
            });
    });

    document.addEventListener('click', function(e) {
        if (!resultsDiv.contains(e.target) && e.target !== searchBox) {
            resultsDiv.style.display = 'none';
        }
    });

    resultsDiv.addEventListener('click', function(e) {
        let item = e.target.closest('.search-item');
        if (item) {
            window.location.href = '/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=' + item.dataset.id;
        }
    });

    // Lịch sử đọc truyện
    const historyMenu = document.getElementById('history-menu');
    const historyDropdown = document.getElementById('history-dropdown');

    historyMenu.addEventListener('click', function(e) {
        e.preventDefault();
        let history = JSON.parse(localStorage.getItem('reading_history') || '[]');
        if (history.length === 0) {
            historyDropdown.innerHTML = '<div style="padding:8px;">Chưa có lịch sử đọc</div>';
        } else {
            historyDropdown.innerHTML = history.map(item => {
                let cover = item.anh_bia ? 
                    '/Wed_Doc_Truyen/' + item.anh_bia.replace(/^\/+/, '') : 
                    defaultCover;
                return `
                    <div class="search-item" data-id="${item.id_truyen}" data-chuong="${item.id_chuong}">
                        <img src="${cover}" alt="${item.ten_truyen}" onerror="this.src='${defaultCover}'">
                        <div class="info">
                            <b>${item.ten_truyen}</b>
                            <div style="font-size:13px;color:#888;">
                                <span style="color:#28a745;font-weight:bold;">${item.trang_thai || 'Đã đọc'}</span>
                                <span> | ${item.ngay_doc || ''}</span>
                            </div>
                            <span>Chương ${item.so_chuong}: ${item.tieu_de}</span>
                        </div>
                    </div>
                `;
            }).join('');
        }
        historyDropdown.style.display = 'block';
    });

    document.addEventListener('click', function(e) {
        if (!historyDropdown.contains(e.target) && e.target !== historyMenu) {
            historyDropdown.style.display = 'none';
        }
    });

    historyDropdown.addEventListener('click', function(e) {
        let item = e.target.closest('.search-item');
        if (item) {
            window.location.href = '/Wed_Doc_Truyen/wedtruyen/app/views/chapter/docChapter.php?id_chuong=' + item.dataset.chuong;
        }
    });
});
</script>