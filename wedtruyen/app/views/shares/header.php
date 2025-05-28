<link rel="stylesheet" href="/Wed_Doc_Truyen/wedtruyen/assets/css/header.css">
<?php
$current_page = basename($_SERVER['PHP_SELF']);
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
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/theLoai/list.php">Thể loại</a>
        <a href="/Wed_Doc_Truyen/wedtruyen/app/views/loaiTruyen/list.php">Loại truyện</a>
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
</style>