<link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/header.css">
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
    <a href="/Wed_Doc_Truyen/wedtruyen/index.php" class="logo">Web Đọc</a>
    <div class="nav-links">
        <!-- Ô tìm kiếm truyện -->
        <div style="position:relative;display:inline-block;">
            <input type="text" id="search-truyen" placeholder="Tìm truyện..." autocomplete="off" style="padding:6px 12px;border-radius:4px;border:1px solid #ccc;width:240px;">
            <div id="search-results" style="display:none;position:absolute;top:36px;left:0;width:100%;background:#fff;border:1px solid #ddd;max-height:300px;overflow-y:auto;z-index:10000;border-radius:0 0 6px 6px;box-shadow:0 4px 16px rgba(0,0,0,0.10);"></div>
        </div>
        <a href="/Wed_Doc_Truyen/wedtruyen/index.php">Trang chủ</a>
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/thuvien/list.php">Thư viện của tôi</a>
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/lichSuDoc.php">Lịch sử đọc</a>
        
        <!-- Dropdown Thể loại -->
        <div class="genre-dropdown-wrapper" style="display:inline-block;position:relative;">
            <button class="genre-dropdown-btn" style="background:none;border:none;color:white;padding:8px 15px;cursor:pointer;">
                Thể loại <span style="font-size:10px;">▼</span>
            </button>
            <div class="genre-dropdown-menu" style="display:none;position:absolute;top:100%;left:0;width:400px;background:#fff;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.15);z-index:1000;margin-top:5px;">
                <div style="padding:15px;border-bottom:1px solid #eee;">
                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/theLoai/list.php" style="color:#007bff;text-decoration:none;font-weight:500;">
                        <i class="fas fa-list"></i> Xem tất cả thể loại
                    </a>
                </div>
                <div class="genre-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:0;max-height:400px;overflow-y:auto;padding:10px;">
                    <?php 
                    mysqli_data_seek($result_theloai, 0); // Reset pointer
                    if ($result_theloai && $result_theloai->num_rows > 0): 
                    ?>
                        <?php while($theloai = $result_theloai->fetch_assoc()): ?>
                            <a href="/Wed_Doc_Truyen/wedtruyen/index.php?theloai=<?php echo $theloai['id_theloai']; ?>" 
                               style="display:block;padding:10px 15px;color:#333;text-decoration:none;transition:all 0.2s;border-radius:4px;">
                                <?php echo htmlspecialchars($theloai['ten_theloai']); ?>
                            </a>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Dropdown Loại truyện -->
        <div class="type-dropdown-wrapper" style="display:inline-block;position:relative;">
            <button class="type-dropdown-btn" style="background:none;border:none;color:white;padding:8px 15px;cursor:pointer;">
                Loại truyện <span style="font-size:10px;">▼</span>
            </button>
            <div class="type-dropdown-menu" style="display:none;position:absolute;top:100%;left:0;width:400px;background:#fff;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.15);z-index:1000;margin-top:5px;">
                <div style="padding:15px;border-bottom:1px solid #eee;">
                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/loaiTruyen/list.php" style="color:#007bff;text-decoration:none;font-weight:500;">
                        <i class="fas fa-list"></i> Xem tất cả loại truyện
                    </a>
                </div>
                <div class="type-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:0;max-height:400px;overflow-y:auto;padding:10px;">
                    <?php 
                    mysqli_data_seek($result_loaitruyen, 0); // Reset pointer
                    if ($result_loaitruyen && $result_loaitruyen->num_rows > 0): 
                    ?>
                        <?php while($loaitruyen = $result_loaitruyen->fetch_assoc()): ?>
                            <a href="/Wed_Doc_Truyen/wedtruyen/index.php?loaitruyen=<?php echo $loaitruyen['id_loai_truyen']; ?>" 
                               style="display:block;padding:10px 15px;color:#333;text-decoration:none;transition:all 0.2s;border-radius:4px;">
                                <?php echo htmlspecialchars($loaitruyen['ten_loai_truyen']); ?>
                            </a>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if (isset($_SESSION['user']['vai_tro']) && $_SESSION['user']['vai_tro'] === 'admin'): ?>
            <a href="/Wed_Doc_Truyen/wedtruyen/app/views/admin/admin.php">Admin Panel</a>
        <?php endif; ?>
    </div>
    <div class="user-info">
        <!-- Nút chuông thông báo luôn hiển thị -->
        <div class="notification-wrapper" style="display:inline-block;position:relative;">
            <button id="notification-btn" style="background:none;border:none;cursor:pointer;position:relative;">
                <i class="fas fa-bell" style="font-size:22px;color:#ffb100;"></i>
                <span id="notification-count" style="position:absolute;top:-6px;right:-6px;background:#e74c3c;color:#fff;font-size:12px;border-radius:50%;padding:2px 6px;display:none;">0</span>
            </button>
            <div id="notification-dropdown" style="display:none;position:absolute;right:0;top:32px;width:320px;max-height:400px;overflow-y:auto;background:#fff;border:1px solid #ddd;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.12);z-index:999;">
                <div id="notification-list" style="padding:10px 0;"></div>
            </div>
        </div>
        <?php if (isset($_SESSION['user'])): ?>
            <!-- Dropdown tài khoản -->
            <div class="account-dropdown-wrapper" style="display:inline-block;position:relative;">
                <button id="account-btn" style="background:none;border:none;cursor:pointer;position:relative;margin-left:10px;">
                    <i class="fas fa-user-circle" style="font-size:26px;color:#fff;"></i>
                    <span style="vertical-align:middle;">&#9660;</span>
                </button>
                <div id="account-dropdown" style="display:none;position:absolute;right:0;top:36px;width:180px;background:#23272f;color:#fff;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.18);z-index:1000;">
                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/taiKhoan/edit.php?id=<?php echo $_SESSION['user']['id_nguoidung']; ?>" style="display:flex;align-items:center;padding:12px 16px;text-decoration:none;color:#fff;">
                        <i class="fas fa-user" style="margin-right:10px;"></i> Thông tin tài khoản
                    </a>
                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/lichSuDoc.php" style="display:flex;align-items:center;padding:12px 16px;text-decoration:none;color:#fff;">
                        <i class="fas fa-history" style="margin-right:10px;"></i> Lịch sử
                    </a>
                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/taiKhoan/logout.php" style="display:flex;align-items:center;padding:12px 16px;text-decoration:none;color:#fff;">
                        <i class="fas fa-power-off" style="margin-right:10px;"></i> Đăng xuất
                    </a>
                </div>
            </div>
            <span>Xin chào, <?php echo htmlspecialchars($_SESSION['user']['ten_dang_nhap']); ?>!</span>
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
    let lastCheckTime = new Date().toISOString();

    function formatDateTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return 'Vừa xong';
        if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' phút trước';
        if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' giờ trước';
        if (diffInSeconds < 604800) return Math.floor(diffInSeconds / 86400) + ' ngày trước';
        
        return date.toLocaleDateString('vi-VN');
    }

    function markAsRead(id_notification) {
        fetch('/Wed_Doc_Truyen/wedtruyen/app/api/notifications.php?action=mark_read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_notification=' + id_notification
        });
    }

    function fetchNotifications() {
        if (!list) return;
        
        fetch('/Wed_Doc_Truyen/wedtruyen/app/api/notifications.php?action=list')
            .then(res => res.json())
            .then(data => {
                list.innerHTML = '';
                
                if (!data.notifications || data.notifications.length === 0) {
                    list.innerHTML = '<div style="padding:12px;text-align:center;color:#888;">Không có thông báo nào.</div>';
                } else {
                    // Header với nút đánh dấu tất cả đã đọc
                    if (data.unread_count > 0) {
                        list.innerHTML = `
                            <div style="padding:8px 16px;border-bottom:1px solid #e0e0e0;display:flex;justify-content:space-between;align-items:center;">
                                <span style="font-weight:500;font-size:14px;">Thông báo (${data.unread_count} chưa đọc)</span>
                                <button onclick="markAllAsRead()" style="font-size:12px;color:#1976d2;background:none;border:none;cursor:pointer;">Đánh dấu tất cả đã đọc</button>
                            </div>
                        `;
                    }
                    
                    data.notifications.forEach(n => {
                        const isUnread = !n.da_doc;
                        const bgColor = isUnread ? '#e3f2fd' : '#fff';
                        const fontWeight = isUnread ? '500' : 'normal';
                        
                        list.innerHTML += `
                            <div class="notification-item" 
                                 style="padding:12px 16px;border-bottom:1px solid #f0f0f0;background:${bgColor};cursor:pointer;transition:background 0.2s;"
                                 onmouseover="this.style.background='#f5f5f5'"
                                 onmouseout="this.style.background='${bgColor}'"
                                 onclick="notificationClick(${n.id_notification}, ${n.id_chuong || 'null'})">
                                <div style="font-weight:${fontWeight};color:#333;margin-bottom:4px;">
                                    ${n.noi_dung}
                                </div>
                                <div style="font-size:12px;color:#666;display:flex;justify-content:space-between;">
                                    <span>${formatDateTime(n.ngay_tao)}</span>
                                    ${isUnread ? '<span style="color:#1976d2;">● Mới</span>' : ''}
                                </div>
                            </div>
                        `;
                    });
                }
                
                // Cập nhật số thông báo chưa đọc
                count.textContent = data.unread_count || 0;
                count.style.display = data.unread_count > 0 ? 'block' : 'none';
            })
            .catch(error => {
                console.error('Lỗi khi tải thông báo:', error);
            });
    }

    // Xử lý click vào thông báo
    window.notificationClick = function(id_notification, id_chuong) {
        // Đánh dấu đã đọc
        markAsRead(id_notification);
        
        // Chuyển đến trang chapter nếu có
        if (id_chuong) {
            window.location.href = `/Wed_Doc_Truyen/wedtruyen/app/views/chapter/docChapter.php?id_chuong=${id_chuong}`;
        }
    };

    // Đánh dấu tất cả đã đọc
    window.markAllAsRead = function() {
        fetch('/Wed_Doc_Truyen/wedtruyen/app/api/notifications.php?action=mark_all_read', {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                fetchNotifications();
            }
        });
    };

    // Kiểm tra thông báo mới mỗi 30 giây
    function checkNewNotifications() {
        fetch(`/Wed_Doc_Truyen/wedtruyen/app/api/notifications.php?action=check_new&last_check=${lastCheckTime}`)
            .then(res => res.json())
            .then(data => {
                if (data.new_count > 0) {
                    // Có thông báo mới, làm mới danh sách
                    fetchNotifications();
                    
                    // Hiển thị animation cho chuông
                    btn.classList.add('notification-pulse');
                    setTimeout(() => btn.classList.remove('notification-pulse'), 1000);
                }
                lastCheckTime = new Date().toISOString();
            });
    }

    // Polling mỗi 30 giây
    setInterval(checkNewNotifications, 30000);

    // Tải thông báo ngay khi load trang
    fetchNotifications();

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
    let cover = truyen.anh_bia && truyen.anh_bia !== 'null' && truyen.anh_bia !== ''
        ? `/Wed_Doc_Truyen/${truyen.anh_bia.replace(/^\/+/, '')}`
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
        if (!dropdown.contains(e.target) && e.target !== btn) {
            dropdown.style.display = 'none';
        }
    });
    searchInput.addEventListener('focus', function() {
        if (searchResults.innerHTML.trim() !== '') searchResults.style.display = 'block';
    });

    // Dropdown tài khoản
    const accountBtn = document.getElementById('account-btn');
    const accountDropdown = document.getElementById('account-dropdown');
    if (accountBtn && accountDropdown) {
        accountBtn.addEventListener('click', function(e) {
            accountDropdown.style.display = accountDropdown.style.display === 'block' ? 'none' : 'block';
            e.stopPropagation();
        });
        document.addEventListener('click', function(e) {
            if (!accountDropdown.contains(e.target) && e.target !== accountBtn) {
                accountDropdown.style.display = 'none';
            }
        });
    }

    // Dropdown thể loại
    const genreBtn = document.querySelector('.genre-dropdown-btn');
    const genreDropdown = document.querySelector('.genre-dropdown-menu');
    if (genreBtn && genreDropdown) {
        genreBtn.addEventListener('click', function(e) {
            genreDropdown.style.display = genreDropdown.style.display === 'block' ? 'none' : 'block';
            // Đóng dropdown loại truyện nếu đang mở
            const typeDropdown = document.querySelector('.type-dropdown-menu');
            if (typeDropdown) typeDropdown.style.display = 'none';
            e.stopPropagation();
        });
    }

    // Dropdown loại truyện
    const typeBtn = document.querySelector('.type-dropdown-btn');
    const typeDropdown = document.querySelector('.type-dropdown-menu');
    if (typeBtn && typeDropdown) {
        typeBtn.addEventListener('click', function(e) {
            typeDropdown.style.display = typeDropdown.style.display === 'block' ? 'none' : 'block';
            // Đóng dropdown thể loại nếu đang mở
            const genreDropdown = document.querySelector('.genre-dropdown-menu');
            if (genreDropdown) genreDropdown.style.display = 'none';
            e.stopPropagation();
        });
    }

    // Đóng tất cả dropdown khi click ngoài
    document.addEventListener('click', function(e) {
        const genreDropdown = document.querySelector('.genre-dropdown-menu');
        const typeDropdown = document.querySelector('.type-dropdown-menu');
        const genreWrapper = document.querySelector('.genre-dropdown-wrapper');
        const typeWrapper = document.querySelector('.type-dropdown-wrapper');
        
        if (genreDropdown && !genreWrapper.contains(e.target)) {
            genreDropdown.style.display = 'none';
        }
        if (typeDropdown && !typeWrapper.contains(e.target)) {
            typeDropdown.style.display = 'none';
        }
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

/* Animation cho notification */
.notification-pulse {
    animation: pulse 0.5s ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* Style cho notification items */
.notification-item:hover {
    background: #f5f5f5;
}

/* Style scrollbar cho notification */
#notification-dropdown::-webkit-scrollbar {
    width: 6px;
}

#notification-dropdown::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#notification-dropdown::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

#notification-dropdown::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Dropdown menu styles */
.dropdown-wrapper {
    position: relative;
    display: inline-block;
}

.dropdown-toggle {
    color: white;
    text-decoration: none;
    padding: 8px 15px;
    border-radius: 4px;
    transition: background-color 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.dropdown-toggle:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.dropdown-toggle span {
    font-size: 10px;
    transition: transform 0.3s;
}

.dropdown-wrapper:hover .dropdown-toggle span {
    transform: rotate(180deg);
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 200px;
    background-color: #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    border-radius: 8px;
    z-index: 1000;
    margin-top: 5px;
    max-height: 400px;
    overflow-y: auto;
}

.dropdown-wrapper:hover .dropdown-menu {
    display: block;
}

.dropdown-menu a {
    display: block;
    padding: 10px 15px;
    color: #333;
    text-decoration: none;
    transition: background-color 0.2s;
}

.dropdown-menu a:hover {
    background-color: #f0f0f0;
    color: #007bff;
}

.dropdown-divider {
    height: 1px;
    background-color: #e0e0e0;
    margin: 5px 0;
}

/* New dropdown styles for genre and type */
.genre-dropdown-btn, .type-dropdown-btn {
    transition: background-color 0.3s;
    border-radius: 4px;
}

.genre-dropdown-btn:hover, .type-dropdown-btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.genre-dropdown-menu a:hover, .type-dropdown-menu a:hover {
    background-color: #f0f0f0;
    color: #007bff;
}

/* Grid scrollbar */
.genre-grid::-webkit-scrollbar, .type-grid::-webkit-scrollbar {
    width: 6px;
}

.genre-grid::-webkit-scrollbar-track, .type-grid::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.genre-grid::-webkit-scrollbar-thumb, .type-grid::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

/* Responsive dropdown */
@media (max-width: 768px) {
    .dropdown-menu {
        position: static;
        width: 100%;
        box-shadow: none;
        margin-top: 0;
    }
    
    .genre-dropdown-menu, .type-dropdown-menu {
        width: 100% !important;
        position: fixed !important;
        left: 0 !important;
        right: 0 !important;
        top: auto !important;
        bottom: 0 !important;
        border-radius: 16px 16px 0 0 !important;
        max-height: 70vh !important;
    }
    
    .genre-grid, .type-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>