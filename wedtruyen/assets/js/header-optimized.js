// Header Optimized JavaScript - Final Version
document.addEventListener('DOMContentLoaded', function() {
    // Smooth dropdown animations
    const dropdowns = document.querySelectorAll('.genre-dropdown-wrapper, .type-dropdown-wrapper');
    
    dropdowns.forEach(dropdown => {
        const menu = dropdown.querySelector('.genre-dropdown-menu, .type-dropdown-menu');
        if (!menu) return;
        
        dropdown.addEventListener('mouseenter', () => {
            menu.style.display = 'block';
            setTimeout(() => menu.style.opacity = '1', 10);
        });
        
        dropdown.addEventListener('mouseleave', () => {
            menu.style.opacity = '0';
            setTimeout(() => menu.style.display = 'none', 300);
        });
    });
    
    // Search functionality với debouncing
    const searchInput = document.getElementById('search-truyen');
    const searchResults = document.getElementById('search-results');
    let searchTimeout = null;
    
    if (searchInput && searchResults) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(searchTimeout);
            
            if (query.length === 0) {
                searchResults.style.display = 'none';
                searchResults.innerHTML = '';
                return;
            }
            
            // Show loading state
            searchResults.innerHTML = '<div style="padding:15px;text-align:center;"><i class="fas fa-spinner fa-spin"></i> Đang tìm kiếm...</div>';
            searchResults.style.display = 'block';
            
            searchTimeout = setTimeout(() => {
                // Fetch search results
                fetch(`/Wed_Doc_Truyen/wedtruyen/app/api/searchTruyen.php?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (Array.isArray(data) && data.length > 0) {
                            searchResults.innerHTML = data.map(truyen => {
                                let cover = truyen.anh_bia && truyen.anh_bia !== 'null' && truyen.anh_bia !== ''
                                    ? `/Wed_Doc_Truyen/${truyen.anh_bia.replace(/^\/+/, '')}`
                                    : `/Wed_Doc_Truyen/wedtruyen/assets/img/default_cover.jpg`;
                                return `
                                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=${truyen.id_truyen}" class="search-item">
                                        <img src="${cover}" alt="cover" onerror="this.src='/Wed_Doc_Truyen/wedtruyen/assets/img/default_cover.jpg'">
                                        <div>
                                            <div style="font-weight:600;color:#333;">${escapeHtml(truyen.ten_truyen)}</div>
                                            <div style="font-size:13px;color:#666;">${truyen.tac_gia ? 'Tác giả: ' + escapeHtml(truyen.tac_gia) : ''}</div>
                                            <div style="font-size:12px;color:#888;">${truyen.trang_thai ? escapeHtml(truyen.trang_thai) : ''}</div>
                                        </div>
                                    </a>
                                `;
                            }).join('');
                            searchResults.style.display = 'block';
                        } else {
                            searchResults.innerHTML = '<div style="padding:15px;text-align:center;color:#666;">Không tìm thấy truyện nào</div>';
                            searchResults.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        searchResults.innerHTML = '<div style="padding:15px;text-align:center;color:#d32f2f;">Có lỗi xảy ra khi tìm kiếm</div>';
                    });
            }, 300);
        });
        
        // Focus effect
        searchInput.addEventListener('focus', function() {
            if (this.value.trim() && searchResults.innerHTML) {
                searchResults.style.display = 'block';
            }
        });
    }
    
    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (searchInput && searchResults && !searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    
    // Account dropdown
    const accountBtn = document.getElementById('account-btn');
    const accountDropdown = document.getElementById('account-dropdown');
    
    if (accountBtn && accountDropdown) {
        accountBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = accountDropdown.style.display === 'block';
            accountDropdown.style.display = isOpen ? 'none' : 'block';
            
            // Animate
            if (!isOpen) {
                accountDropdown.style.opacity = '0';
                accountDropdown.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    accountDropdown.style.opacity = '1';
                    accountDropdown.style.transform = 'translateY(0)';
                }, 10);
            }
        });
    }
    
    // Notification functionality
    const notificationBtn = document.getElementById('notification-btn');
    const notificationDropdown = document.getElementById('notification-dropdown');
    const notificationCount = document.getElementById('notification-count');
    const notificationList = document.getElementById('notification-list');
    
    // Load notifications
    function loadNotifications() {
        if (!notificationList) return;
        
        // Fetch notifications from API
        fetch('/Wed_Doc_Truyen/wedtruyen/app/api/notifications.php?action=list')
            .then(res => res.json())
            .then(data => {
                if (data.notifications && data.notifications.length > 0) {
                    // Add header if there are unread notifications
                    let html = '';
                    if (data.unread_count > 0) {
                        html += `
                            <div style="padding:12px 20px;border-bottom:1px solid #e0e0e0;display:flex;justify-content:space-between;align-items:center;background:#f8f9fa;">
                                <span style="font-weight:600;color:#333;">Thông báo (${data.unread_count} chưa đọc)</span>
                                <button onclick="markAllAsRead()" style="font-size:13px;color:#1976d2;background:none;border:none;cursor:pointer;padding:4px 8px;border-radius:4px;transition:background 0.2s;">
                                    Đánh dấu tất cả đã đọc
                                </button>
                            </div>
                        `;
                    }
                    
                    html += data.notifications.map(n => `
                        <div class="notification-item" onclick="handleNotificationClick(${n.id_notification}, ${n.id_chuong || 'null'})" style="${!n.da_doc ? 'background:#e3f2fd;' : ''}">
                            <div style="font-weight:${n.da_doc ? 'normal' : '600'};color:#333;margin-bottom:5px;">
                                ${escapeHtml(n.noi_dung)}
                            </div>
                            <div style="font-size:12px;color:#666;display:flex;justify-content:space-between;">
                                <span>${formatDateTime(n.ngay_tao)}</span>
                                ${!n.da_doc ? '<span style="color:#1976d2;font-weight:500;">● Mới</span>' : ''}
                            </div>
                        </div>
                    `).join('');
                    
                    notificationList.innerHTML = html;
                    
                    // Update count
                    if (data.unread_count > 0) {
                        notificationCount.textContent = data.unread_count;
                        notificationCount.style.display = 'block';
                    } else {
                        notificationCount.style.display = 'none';
                    }
                } else {
                    notificationList.innerHTML = '<div style="padding:40px 20px;text-align:center;color:#666;">Không có thông báo nào</div>';
                    notificationCount.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                notificationList.innerHTML = '<div style="padding:20px;text-align:center;color:#d32f2f;">Lỗi khi tải thông báo</div>';
            });
    }
    
    // Handle notification click
    window.handleNotificationClick = function(id, chapterId) {
        // Mark as read
        fetch('/Wed_Doc_Truyen/wedtruyen/app/api/notifications.php?action=mark_read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_notification=' + id
        }).then(() => {
            // Reload notifications
            loadNotifications();
            
            // Navigate to chapter if available
            if (chapterId) {
                window.location.href = `/Wed_Doc_Truyen/wedtruyen/app/views/chapter/docChapter.php?id_chuong=${chapterId}`;
            }
        });
    };
    
    // Mark all as read
    window.markAllAsRead = function() {
        fetch('/Wed_Doc_Truyen/wedtruyen/app/api/notifications.php?action=mark_all_read', {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
                // Add animation
                notificationBtn.classList.add('notification-pulse');
                setTimeout(() => notificationBtn.classList.remove('notification-pulse'), 500);
            }
        });
    };
    
    // Format date time
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
    
    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Toggle notification dropdown
    if (notificationBtn && notificationDropdown) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = notificationDropdown.style.display === 'block';
            notificationDropdown.style.display = isOpen ? 'none' : 'block';
            
            if (!isOpen) {
                loadNotifications();
                // Animate
                notificationDropdown.style.opacity = '0';
                notificationDropdown.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    notificationDropdown.style.opacity = '1';
                    notificationDropdown.style.transform = 'translateY(0)';
                }, 10);
            }
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        if (notificationDropdown) notificationDropdown.style.display = 'none';
        if (accountDropdown) accountDropdown.style.display = 'none';
    });
    
    // Prevent dropdown close when clicking inside
    if (notificationDropdown) {
        notificationDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    if (accountDropdown) {
        accountDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Mobile menu toggle
    if (window.innerWidth <= 768) {
        const logo = document.querySelector('.logo');
        const navLinks = document.querySelector('.nav-links');
        
        if (logo && navLinks) {
            logo.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
                }
            });
        }
    }
    
    // Check for new notifications periodically
    let lastCheckTime = new Date().toISOString();
    
    function checkNewNotifications() {
        fetch(`/Wed_Doc_Truyen/wedtruyen/app/api/notifications.php?action=check_new&last_check=${lastCheckTime}`)
            .then(res => res.json())
            .then(data => {
                if (data.new_count > 0) {
                    // Reload notifications
                    loadNotifications();
                    
                    // Show animation
                    if (notificationBtn) {
                        notificationBtn.classList.add('notification-pulse');
                        setTimeout(() => notificationBtn.classList.remove('notification-pulse'), 1000);
                    }
                    
                    // Play sound (optional)
                    // const audio = new Audio('/Wed_Doc_Truyen/wedtruyen/assets/sounds/notification.mp3');
                    // audio.play().catch(() => {}); // Catch để tránh lỗi autoplay policy
                }
                lastCheckTime = new Date().toISOString();
            })
            .catch(error => console.error('Error checking notifications:', error));
    }
    
    // Initial load
    loadNotifications();
    
    // Auto refresh notifications every 30 seconds
    setInterval(checkNewNotifications, 30000);
    
    // Active menu item highlight
    const currentPath = window.location.pathname;
    const menuItems = document.querySelectorAll('.nav-links > a');
    
    menuItems.forEach(item => {
        if (item.getAttribute('href') === currentPath) {
            item.style.background = 'rgba(255, 255, 255, 0.15)';
            item.style.borderBottom = '3px solid #ffb100';
        }
    });
    
    // Smooth scroll to top when clicking logo
    const logoLink = document.querySelector('.logo');
    if (logoLink) {
        logoLink.addEventListener('click', function(e) {
            if (window.location.pathname === '/Wed_Doc_Truyen/wedtruyen/index.php') {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        });
    }
    
    // Add loading state to dropdowns
    const dropdownBtns = document.querySelectorAll('.genre-dropdown-btn, .type-dropdown-btn');
    dropdownBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                const dropdown = this.nextElementSibling;
                if (dropdown) {
                    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                }
            }
        });
    });
});