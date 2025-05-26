// Admin Dashboard JavaScript

// Navigation functions
function showDashboard() {
    hideAllViews();
    document.getElementById('dashboardView').style.display = 'block';
    setActiveMenu(0);
    loadStats();
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

async function loadTruyenData() {
    try {
        const response = await fetch('admin.php?action=getTruyen');
        const data = await response.json();
        
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
                    <button class="btn-edit" onclick="editTruyen(${truyen.id_truyen})">
                        <i class="fas fa-edit"></i> Sửa
                    </button>
                    <button class="btn-delete" onclick="deleteTruyen(${truyen.id_truyen})">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        console.error('Error loading truyen data:', error);
    }
}

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

// Modal functions
function openAddTruyenModal() {
    document.getElementById('addTruyenModal').style.display = 'block';
    loadLoaiTruyenOptions();
    loadTheLoaiCheckboxes();
}

function closeAddTruyenModal() {
    document.getElementById('addTruyenModal').style.display = 'none';
    document.getElementById('addTruyenForm').reset();
}

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
            container.appendChild(document.createElement('br'));
        });
    } catch (error) {
        console.error('Error loading the loai checkboxes:', error);
    }
}

// Form submit
document.getElementById('addTruyenForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Thêm truyện thành công!');
            closeAddTruyenModal();
            loadTruyenData();
        } else {
            alert('Lỗi: ' + (result.error || 'Không thể thêm truyện'));
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        alert('Có lỗi xảy ra khi thêm truyện');
    }
});

// Edit/Delete functions
function editTruyen(id) {
    // In real implementation, this would open an edit modal
    window.location.href = `../truyen/edit.php?id=${id}`;
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

function editTheLoai(id) {
    // Similar to editTruyen
    window.location.href = `../theLoai/edit.php?id=${id}`;
}

function deleteTheLoai(id) {
    if (confirm('Bạn có chắc chắn muốn xóa thể loại này?')) {
        // Implement delete functionality
        alert('Chức năng xóa thể loại ID: ' + id);
    }
}

function editLoaiTruyen(id) {
    window.location.href = `../loaiTruyen/edit.php?id=${id}`;
}

function deleteLoaiTruyen(id) {
    if (confirm('Bạn có chắc chắn muốn xóa loại truyện này?')) {
        // Implement delete functionality
        alert('Chức năng xóa loại truyện ID: ' + id);
    }
}

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