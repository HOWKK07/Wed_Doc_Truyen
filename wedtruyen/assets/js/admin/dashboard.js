
// Admin Dashboard JavaScript

// Navigation functions
function showDashboard() {
    hideAllViews();
    document.getElementById('dashboardView').style.display = 'block';
    setActiveMenu(0);
    loadStats();
    loadActivityLog();
}

function showTaiKhoan() {
    hideAllViews();
    document.getElementById('taiKhoanView').style.display = 'block';
    setActiveMenu(1);
    loadTaiKhoanData();
}

function showTruyen() {
    hideAllViews();
    document.getElementById('truyenView').style.display = 'block';
    setActiveMenu(2);
    loadTruyenData();
}

function showTheLoai() {
    hideAllViews();
    document.getElementById('theLoaiView').style.display = 'block';
    setActiveMenu(3);
    loadTheLoaiData();
}

function showLoaiTruyen() {
    hideAllViews();
    document.getElementById('loaiTruyenView').style.display = 'block';
    setActiveMenu(4);
    loadLoaiTruyenData();
}

function showThongKe() {
    alert('Chức năng thống kê đang được phát triển');
}

function showCaiDat() {
    alert('Chức năng cài đặt đang được phát triển');
}

function hideAllViews() {
    const views = ['dashboardView', 'taiKhoanView', 'truyenView', 'theLoaiView', 'loaiTruyenView'];
    views.forEach(view => {
        const element = document.getElementById(view);
        if (element) element.style.display = 'none';
    });
}

function setActiveMenu(index) {
    const menuItems = document.querySelectorAll('.sidebar-menu a');
    menuItems.forEach(item => item.classList.remove('active'));
    if (menuItems[index]) {
        menuItems[index].classList.add('active');
    }
}

// Load data functions
async function loadStats() {
    try {
        const response = await fetch('admin.php?action=getStats');
        const stats = await response.json();
        
        document.getElementById('statsCards').innerHTML = `
            <div class="stat-card accounts">
                <div class="stat-info">
                    <h3>${stats.accounts}</h3>
                    <p>Tài khoản</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            
            <div class="stat-card stories">
                <div class="stat-info">
                    <h3>${stats.stories}</h3>
                    <p>Truyện</p>
                </div>
                <div class="icon">
                    <i class="fas fa-book"></i>
                </div>
            </div>
            
            <div class="stat-card chapters">
                <div class="stat-info">
                    <h3>${stats.genres}</h3>
                    <p>Thể loại</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
            
            <div class="stat-card lost-stories">
                <div class="stat-info">
                    <h3>${stats.types}</h3>
                    <p>Loại truyện</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadActivityLog() {
    // Simulate activity log - trong thực tế sẽ lấy từ database
    const activities = [
        { time: '10 phút trước', action: 'Thêm truyện mới', detail: 'One Piece Chapter 1100', user: 'Admin' },
        { time: '1 giờ trước', action: 'Cập nhật thể loại', detail: 'Thêm thể loại "Isekai"', user: 'Admin' },
        { time: '2 giờ trước', action: 'Xóa tài khoản', detail: 'Xóa user123', user: 'Admin' }
    ];
    
    const tbody = document.getElementById('activityLog');
    tbody.innerHTML = activities.map(activity => `
        <tr>
            <td>${activity.time}</td>
            <td>${activity.action}</td>
            <td>${activity.detail}</td>
            <td>${activity.user}</td>
        </tr>
    `).join('');
}

// Load Tài khoản data
async function loadTaiKhoanData() {
    try {
        const response = await fetch('admin.php?action=getTaiKhoan');
        const data = await response.json();
        
        const tbody = document.getElementById('taiKhoanTableBody');
        tbody.innerHTML = '';
        
        data.forEach(user => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${user.id_nguoidung}</td>
                <td>${escapeHtml(user.ten_dang_nhap)}</td>
                <td>${escapeHtml(user.email)}</td>
                <td><span class="status-badge ${user.vai_tro === 'admin' ? 'completed' : 'ongoing'}">${user.vai_tro}</span></td>
                <td>${formatDate(user.ngay_tao)}</td>
                <td class="action-buttons">
                    <button class="btn-edit" onclick="editTaiKhoan(${user.id_nguoidung})">
                        <i class="fas fa-edit"></i> Sửa
                    </button>
                    <button class="btn-delete" onclick="deleteTaiKhoan(${user.id_nguoidung})">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        console.error('Error loading tai khoan data:', error);
    }
}

let allTruyenData = []; // Lưu toàn bộ dữ liệu để lọc

async function loadTruyenData() {
    try {
        const response = await fetch('admin.php?action=getTruyen');
        const data = await response.json();
        allTruyenData = data; // Lưu lại để dùng cho tìm kiếm
        renderTruyenTable(data);
    } catch (error) {
        console.error('Error loading truyen data:', error);
    }
}

function renderTruyenTable(data) {
    const tbody = document.getElementById('truyenTableBody');
    tbody.innerHTML = '';
    data.forEach(truyen => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${truyen.id_truyen}</td>
            <td>${escapeHtml(truyen.ten_truyen)}</td>
            <td>${escapeHtml(truyen.tac_gia)}</td>
            <td>${escapeHtml(truyen.ten_loai_truyen || '')}</td>
            <td><span class="status-badge ${truyen.trang_thai === 'Hoàn thành' ? 'completed' : 'ongoing'}">${truyen.trang_thai}</span></td>
            <td>${truyen.nam_phat_hanh}</td>
            <td>${formatDate(truyen.ngay_tao)}</td>
            <td class="action-buttons">
                <button class="btn-edit" onclick="event.stopPropagation(); editTruyen(${truyen.id_truyen})">
                    <i class="fas fa-edit"></i> Sửa
                </button>
                <button class="btn-delete" onclick="event.stopPropagation(); deleteTruyen(${truyen.id_truyen})">
                    <i class="fas fa-trash"></i> Xóa
                </button>
            </td>
        `;
        row.style.cursor = 'pointer';
        row.addEventListener('click', function(e) {
            if (e.target.closest('.action-buttons')) return;
            window.location.href = '../truyen/chiTietTruyen.php?id_truyen=' + truyen.id_truyen;
        });
        tbody.appendChild(row);
    });
}

// Xử lý tìm kiếm
document.addEventListener('DOMContentLoaded', function() {
    // ...existing code...
    document.getElementById('searchTruyenInput').addEventListener('input', function() {
        const keyword = this.value.trim().toLowerCase();
        const filtered = allTruyenData.filter(truyen =>
            Object.values(truyen).some(val =>
                (val + '').toLowerCase().includes(keyword)
            )
        );
        renderTruyenTable(filtered);
    });
});

async function loadTheLoaiData() {
    try {
        const response = await fetch('admin.php?action=getTheLoai');
        const data = await response.json();
        
        const tbody = document.getElementById('theLoaiTableBody');
        tbody.innerHTML = '';
        
        data.forEach(theloai => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${theloai.id_theloai}</td>
                <td>${escapeHtml(theloai.ten_theloai)}</td>
                <td>${formatDate(theloai.ngay_tao)}</td>
                <td class="action-buttons">
                    <button class="btn-edit" onclick="editTheLoai(${theloai.id_theloai})">
                        <i class="fas fa-edit"></i> Sửa
                    </button>
                    <button class="btn-delete" onclick="deleteTheLoai(${theloai.id_theloai})">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        console.error('Error loading the loai data:', error);
    }
}

async function loadLoaiTruyenData() {
    try {
        const response = await fetch('admin.php?action=getLoaiTruyen');
        const data = await response.json();
        
        const tbody = document.getElementById('loaiTruyenTableBody');
        tbody.innerHTML = '';
        
        data.forEach(loaitruyen => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${loaitruyen.id_loai_truyen}</td>
                <td>${escapeHtml(loaitruyen.ten_loai_truyen)}</td>
                <td>${formatDate(loaitruyen.ngay_tao)}</td>
                <td class="action-buttons">
                    <button class="btn-edit" onclick="editLoaiTruyen(${loaitruyen.id_loai_truyen})">
                        <i class="fas fa-edit"></i> Sửa
                    </button>
                    <button class="btn-delete" onclick="deleteLoaiTruyen(${loaitruyen.id_loai_truyen})">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        console.error('Error loading loai truyen data:', error);
    }
}

// Modal functions for Truyện
function openAddTruyenModal() {
    document.getElementById('truyenModalTitle').textContent = 'Thêm truyện mới';
    document.getElementById('truyenAction').value = 'addTruyen';
    document.getElementById('truyenForm').reset();
    document.getElementById('id_truyen').value = '';
    document.getElementById('currentImage').innerHTML = '';
    document.getElementById('truyenModal').style.display = 'block';
    loadLoaiTruyenOptions();
    loadTheLoaiCheckboxes();
}

function closeTruyenModal() {
    document.getElementById('truyenModal').style.display = 'none';
}

async function editTruyen(id) {
    try {
        const response = await fetch(`admin.php?action=getTruyenDetail&id=${id}`);
        const truyen = await response.json();
        
        document.getElementById('truyenModalTitle').textContent = 'Sửa truyện';
        document.getElementById('truyenAction').value = 'updateTruyen';
        document.getElementById('id_truyen').value = truyen.id_truyen;
        document.getElementById('ten_truyen').value = truyen.ten_truyen;
        document.getElementById('tac_gia').value = truyen.tac_gia;
        document.getElementById('trang_thai').value = truyen.trang_thai;
        document.getElementById('nam_phat_hanh').value = truyen.nam_phat_hanh;
        document.getElementById('mo_ta').value = truyen.mo_ta;
        
        // Hiển thị ảnh hiện tại
        if (truyen.anh_bia) {
            document.getElementById('currentImage').innerHTML = `
                <img src="/Wed_Doc_Truyen/${truyen.anh_bia}" style="max-width: 200px; max-height: 200px;">
                <p>Ảnh hiện tại</p>
            `;
        }
        
        await loadLoaiTruyenOptions();
        document.getElementById('id_loai_truyen').value = truyen.id_loai_truyen;
        
        await loadTheLoaiCheckboxes();
        // Check các thể loại đã chọn
        if (truyen.theloai_selected) {
            truyen.theloai_selected.forEach(id => {
                const checkbox = document.querySelector(`input[name="the_loai[]"][value="${id}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }
        
        document.getElementById('truyenModal').style.display = 'block';
    } catch (error) {
        console.error('Error loading truyen detail:', error);
        alert('Có lỗi xảy ra khi tải thông tin truyện');
    }
}

async function deleteTruyen(id) {
    if (confirm('Bạn có chắc chắn muốn xóa truyện này?')) {
        try {
            const response = await fetch(`admin.php?action=deleteTruyen&id=${id}`);
            const result = await response.json();
            
            if (result.success) {
                alert('Đã xóa truyện thành công!');
                loadTruyenData();
            } else {
                alert('Lỗi: ' + (result.error || 'Không thể xóa truyện'));
            }
        } catch (error) {
            console.error('Error deleting truyen:', error);
            alert('Có lỗi xảy ra khi xóa truyện');
        }
    }
}

// Modal functions for Thể loại
function openAddTheLoaiModal() {
    document.getElementById('theLoaiModalTitle').textContent = 'Thêm thể loại mới';
    document.getElementById('theLoaiAction').value = 'addTheLoai';
    document.getElementById('theLoaiForm').reset();
    document.getElementById('id_theloai').value = '';
    document.getElementById('theLoaiModal').style.display = 'block';
}

function closeTheLoaiModal() {
    document.getElementById('theLoaiModal').style.display = 'none';
}

async function editTheLoai(id) {
    const response = await fetch('admin.php?action=getTheLoai');
    const data = await response.json();
    const theloai = data.find(tl => tl.id_theloai == id);

    if (theloai) {
        document.getElementById('theLoaiModalTitle').textContent = 'Sửa thể loại';
        document.getElementById('theLoaiAction').value = 'updateTheLoai'; // Đảm bảo dòng này có!
        document.getElementById('id_theloai').value = theloai.id_theloai;
        document.getElementById('ten_theloai').value = theloai.ten_theloai;
        document.getElementById('theLoaiModal').style.display = 'block';
    }
}

async function deleteTheLoai(id) {
    if (confirm('Bạn có chắc chắn muốn xóa thể loại này?')) {
        try {
            const response = await fetch(`admin.php?action=deleteTheLoai&id=${id}`);
            const result = await response.json();
            
            if (result.success) {
                alert('Đã xóa thể loại thành công!');
                loadTheLoaiData();
            } else {
                alert('Lỗi: ' + (result.error || 'Không thể xóa thể loại'));
            }
        } catch (error) {
            console.error('Error deleting the loai:', error);
            alert('Có lỗi xảy ra khi xóa thể loại');
        }
    }
}

// Modal functions for Loại truyện
function openAddLoaiTruyenModal() {
    document.getElementById('loaiTruyenModalTitle').textContent = 'Thêm loại truyện mới';
    document.getElementById('loaiTruyenAction').value = 'addLoaiTruyen';
    document.getElementById('loaiTruyenForm').reset();
    document.getElementById('id_loai_truyen_modal').value = '';
    document.getElementById('loaiTruyenModal').style.display = 'block';
}

function closeLoaiTruyenModal() {
    document.getElementById('loaiTruyenModal').style.display = 'none';
}

async function editLoaiTruyen(id) {
    const response = await fetch('admin.php?action=getLoaiTruyen');
    const data = await response.json();
    const loaitruyen = data.find(lt => lt.id_loai_truyen == id);

    if (loaitruyen) {
        document.getElementById('loaiTruyenModalTitle').textContent = 'Sửa loại truyện';
        document.getElementById('loaiTruyenAction').value = 'updateLoaiTruyen'; // Đảm bảo dòng này có!
        document.getElementById('id_loai_truyen_modal').value = loaitruyen.id_loai_truyen;
        document.getElementById('ten_loai_truyen').value = loaitruyen.ten_loai_truyen;
        document.getElementById('loaiTruyenModal').style.display = 'block';
    }
}

async function deleteLoaiTruyen(id) {
    if (confirm('Bạn có chắc chắn muốn xóa loại truyện này?')) {
        try {
            const response = await fetch(`admin.php?action=deleteLoaiTruyen&id=${id}`);
            const result = await response.json();
            
            if (result.success) {
                alert('Đã xóa loại truyện thành công!');
                loadLoaiTruyenData();
            } else {
                alert('Lỗi: ' + (result.error || 'Không thể xóa loại truyện'));
            }
        } catch (error) {
            console.error('Error deleting loai truyen:', error);
            alert('Có lỗi xảy ra khi xóa loại truyện');
        }
    }
}

// Modal functions for Tài khoản
function closeTaiKhoanModal() {
    document.getElementById('taiKhoanModal').style.display = 'none';
}

async function editTaiKhoan(id) {
    try {
        const response = await fetch(`admin.php?action=getTaiKhoanDetail&id=${id}`);
        const user = await response.json();
        
        document.getElementById('id_nguoidung').value = user.id_nguoidung;
        document.getElementById('ten_dang_nhap_modal').value = user.ten_dang_nhap;
        document.getElementById('email_modal').value = user.email;
        document.getElementById('vai_tro_modal').value = user.vai_tro;
        document.getElementById('taiKhoanModal').style.display = 'block';
    } catch (error) {
        console.error('Error loading user detail:', error);
        alert('Có lỗi xảy ra khi tải thông tin tài khoản');
    }
}

async function deleteTaiKhoan(id) {
    if (confirm('Bạn có chắc chắn muốn xóa tài khoản này?')) {
        try {
            const response = await fetch(`admin.php?action=deleteTaiKhoan&id=${id}`);
            const result = await response.json();
            
            if (result.success) {
                alert('Đã xóa tài khoản thành công!');
                loadTaiKhoanData();
            } else {
                alert('Lỗi: ' + (result.error || 'Không thể xóa tài khoản'));
            }
        } catch (error) {
            console.error('Error deleting tai khoan:', error);
            alert('Có lỗi xảy ra khi xóa tài khoản');
        }
    }
}

// Load options
async function loadLoaiTruyenOptions() {
    try {
        const response = await fetch('admin.php?action=getLoaiTruyen');
        const data = await response.json();
        
        const select = document.querySelector('select[name="id_loai_truyen"]');
        select.innerHTML = '<option value="">Chọn loại truyện</option>';
        
        data.forEach(loai => {
            const option = document.createElement('option');
            option.value = loai.id_loai_truyen;
            option.textContent = loai.ten_loai_truyen;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading loai truyen options:', error);
    }
}

async function loadTheLoaiCheckboxes() {
    try {
        const response = await fetch('admin.php?action=getTheLoai');
        const data = await response.json();
        
        const container = document.getElementById('theLoaiList');
        container.innerHTML = '';
        
        data.forEach(theloai => {
            const label = document.createElement('label');
            label.innerHTML = `
                <input type="checkbox" name="the_loai[]" value="${theloai.id_theloai}">
                ${escapeHtml(theloai.ten_theloai)}
            `;
            container.appendChild(label);
        });
    } catch (error) {
        console.error('Error loading the loai checkboxes:', error);
    }
}

// Form submit handlers
document.getElementById('truyenForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(formData.get('action') === 'addTruyen' ? 'Thêm truyện thành công!' : 'Cập nhật truyện thành công!');
            closeTruyenModal();
            loadTruyenData();
        } else {
            alert('Lỗi: ' + (result.error || 'Không thể thực hiện thao tác'));
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        alert('Có lỗi xảy ra khi gửi form');
    }
});

document.getElementById('theLoaiForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(formData.get('action') === 'addTheLoai' ? 'Thêm thể loại thành công!' : 'Cập nhật thể loại thành công!');
            closeTheLoaiModal();
            loadTheLoaiData();
        } else {
            alert('Lỗi: ' + (result.error || 'Không thể thực hiện thao tác'));
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        alert('Có lỗi xảy ra khi gửi form');
    }
});

document.getElementById('loaiTruyenForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(formData.get('action') === 'addLoaiTruyen' ? 'Thêm loại truyện thành công!' : 'Cập nhật loại truyện thành công!');
            closeLoaiTruyenModal();
            loadLoaiTruyenData();
        } else {
            alert('Lỗi: ' + (result.error || 'Không thể thực hiện thao tác'));
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        alert('Có lỗi xảy ra khi gửi form');
    }
});

document.getElementById('taiKhoanForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Cập nhật tài khoản thành công!');
            closeTaiKhoanModal();
            loadTaiKhoanData();
        } else {
            alert('Lỗi: ' + (result.error || 'Không thể cập nhật tài khoản'));
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        alert('Có lỗi xảy ra khi gửi form');
    }
});

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN');
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.className === 'modal') {
        event.target.style.display = 'none';
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set default view
    showTruyen();
});
